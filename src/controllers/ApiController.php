<?php
/**
 * API Controller
 * 
 * Handles API endpoints for AJAX requests.
 */

class ApiController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Health check endpoint
     */
    public function health() {
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'timestamp' => date('c'),
            'environment' => APP_ENV,
        ]);
    }
    
    /**
     * Database health check
     */
    public function healthDb() {
        header('Content-Type: application/json');
        
        try {
            $this->db->query('SELECT 1');
            echo json_encode([
                'ok' => true,
                'timestamp' => date('c'),
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c'),
            ]);
        }
    }
    
    /**
     * Bootstrap endpoint - returns all data for frontend
     */
    public function bootstrap() {
        header('Content-Type: application/json');
        
        try {
            $currencies = $this->db->query('SELECT * FROM currencies');
            $rooms = $this->db->query('SELECT * FROM rooms');
            // Attach pricing tiers and currency-specific prices to each room
            foreach ($rooms as &$room) {
                $room['price_tiers'] = $this->db->query(
                    'SELECT guest_count, price_per_night, currency FROM room_price_tiers WHERE room_id = ? ORDER BY guest_count',
                    [$room['id']]
                );
                // Attach currency prices
                $currencyPrices = $this->db->query(
                    'SELECT currency, price_per_adult, price_additional_adult, price_per_child FROM room_currency_prices WHERE room_id = ?',
                    [$room['id']]
                );
                $room['currency_prices'] = [];
                foreach ($currencyPrices as $cp) {
                    $room['currency_prices'][$cp['currency']] = [
                        'adult' => floatval($cp['price_per_adult']),
                        'additional_adult' => floatval($cp['price_additional_adult']),
                        'child' => floatval($cp['price_per_child'])
                    ];
                }
                $roomImages = [];
                try {
                    $roomImages = $this->db->query(
                        'SELECT id, image_path FROM room_images WHERE room_id = ? ORDER BY id ASC',
                        [$room['id']]
                    );
                } catch (Exception $ignore) {
                    $roomImages = [];
                }
                $room['images'] = array_values(array_filter(array_map(function ($img) {
                    return $img['image_path'] ?? null;
                }, $roomImages)));
                // Keep legacy single-image field for older clients
                if (empty($room['image_path']) && !empty($room['images'][0])) {
                    $room['image_path'] = $room['images'][0];
                }
            }
            unset($room);
            $spaServices = [];
            $meetingVenues = [];
            try {
                $spaServices = $this->db->query('SELECT * FROM spa_services WHERE is_active = 1 ORDER BY id');
                foreach ($spaServices as &$svc) {
                    $prices = $this->db->query(
                        'SELECT currency, price FROM spa_service_prices WHERE service_id = ?',
                        [$svc['id']]
                    );
                    $svc['currency_prices'] = [];
                    foreach ($prices as $p) {
                        $svc['currency_prices'][$p['currency']] = floatval($p['price']);
                    }
                }
                unset($svc);

                $meetingVenues = $this->db->query('SELECT * FROM meeting_venues WHERE is_active = 1 ORDER BY id');
                foreach ($meetingVenues as &$venue) {
                    $prices = $this->db->query(
                        'SELECT currency, price FROM meeting_venue_prices WHERE venue_id = ?',
                        [$venue['id']]
                    );
                    $venue['currency_prices'] = [];
                    foreach ($prices as $p) {
                        $venue['currency_prices'][$p['currency']] = floatval($p['price']);
                    }
                }
                unset($venue);
            } catch (Exception $ignore) {
                $spaServices = [];
                $meetingVenues = [];
            }

            $bookings = $this->db->query('SELECT * FROM booking_rooms');
            $spaBookings = $this->db->query('SELECT * FROM spa_bookings');
            $meetingBookings = $this->db->query('SELECT * FROM meeting_bookings');
            $galleryImages = $this->db->query('SELECT * FROM gallery_images');
            $galleryCategories = $this->db->query('SELECT * FROM gallery_categories');
            $siteImages = $this->db->query('SELECT * FROM site_images');
            $virtualTour = $this->db->queryOne('SELECT * FROM virtual_tours WHERE id = 1');
            $hotspots = $this->db->query('SELECT * FROM hotspots WHERE tour_id = 1');
            
            // Format site images as key-value pairs
            $siteImagesMap = [];
            foreach ($siteImages as $image) {
                $siteImagesMap[$image['key']] = $image['src'];
            }
            
            // Format gallery images
            $galleryImagesFormatted = [];
            foreach ($galleryImages as $image) {
                $galleryImagesFormatted[] = [
                    'id' => $image['id'],
                    'src' => $image['image_path'] ?? '',
                    'category' => $image['category'] ?? '',
                ];
            }
            
            echo json_encode([
                'currencies' => $currencies,
                'rooms' => $rooms,
                'spaServices' => $spaServices,
                'meetingVenues' => $meetingVenues,
                'bookings' => $bookings,
                'spaBookings' => $spaBookings,
                'meetingBookings' => $meetingBookings,
                'galleryImages' => $galleryImagesFormatted,
                'galleryCategories' => $galleryCategories,
                'siteImages' => $siteImagesMap,
                'virtualTourImage' => $virtualTour['image_url'] ?? '',
                'virtualTourHotspots' => $hotspots,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Chatbot endpoint
     */
    public function chatbot() {
        header('Content-Type: application/json');
        
        // Rate limiting
        $rateLimiter = new RateLimiter();
        if (!$rateLimiter->checkLimit('chatbot', CHATBOT_RATE_LIMIT, CHATBOT_RATE_WINDOW)) {
            http_response_code(429);
            echo json_encode([
                'error' => 'Rate limit exceeded. Please try again later.',
            ]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $message = $input['message'] ?? '';
        
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Message is required']);
            return;
        }
        
        // Forward to Gemini proxy
        require_once __DIR__ . '/../services/GeminiProxy.php';
        $proxy = new GeminiProxy();
        
        try {
            $response = $proxy->chat($message);
            echo json_encode([
                'response' => $response,
                'text' => $response, // For backward compatibility
            ]);
        } catch (GeminiRateLimitException $e) {
            error_log('Chatbot rate limit: ' . $e->getMessage());
            http_response_code(503);
            echo json_encode([
                'error' => 'Our AI assistant is temporarily busy. Please try again in a minute.',
            ]);
        } catch (Exception $e) {
            error_log('Chatbot error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to get response from chatbot. Please try again later.',
            ]);
        }
    }
}

