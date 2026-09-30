<?php
/**
 * Booking Controller
 * 
 * Handles booking submissions (rooms, spa, meetings).
 * Rebuilt to be simple and reliable.
 */

class BookingController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Book a room
     */
    public function bookRoom() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            return;
        }
        
        $guestName = trim($input['guestName'] ?? '');
        $email = trim($input['email'] ?? '');
        $phoneNumber = trim($input['phoneNumber'] ?? '');
        $checkInDate = $input['checkInDate'] ?? '';
        $checkOutDate = $input['checkOutDate'] ?? '';
        $roomType = trim($input['roomType'] ?? '');
        $numberOfGuests = intval($input['numberOfGuests'] ?? 1);
        
        // Validation
        if (empty($guestName) || empty($email) || empty($phoneNumber) || 
            empty($checkInDate) || empty($checkOutDate) || empty($roomType)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address']);
            return;
        }
        
        $today = date('Y-m-d');
        if ($checkInDate < $today) {
            http_response_code(400);
            echo json_encode(['error' => 'Check-in date cannot be in the past']);
            return;
        }
        
        if (strtotime($checkOutDate) <= strtotime($checkInDate)) {
            http_response_code(400);
            echo json_encode(['error' => 'Check-out date must be after check-in date']);
            return;
        }
        
        // Check room availability
        $room = $this->db->queryOne(
            'SELECT * FROM rooms WHERE type = ?',
            [$roomType]
        );
        
        if (!$room) {
            http_response_code(400);
            echo json_encode(['error' => 'Selected room type not found']);
            return;
        }
        
        if ($room['booked'] >= $room['quantity']) {
            http_response_code(400);
            echo json_encode(['error' => "Sorry, all {$roomType} rooms are currently booked"]);
            return;
        }
        
        // Calculate stay duration in nights
        $checkInTimestamp = strtotime($checkInDate);
        $checkOutTimestamp = strtotime($checkOutDate);
        $nights = ceil(($checkOutTimestamp - $checkInTimestamp) / (24 * 3600));
        if ($nights <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Check-out date must be after check-in date']);
            return;
        }

        // Determine price: use per-room adult/child rates
        $roomsInput = $input['rooms'] ?? [];
        if (!is_array($roomsInput) || empty($roomsInput)) {
            // Fallback for backwards compatibility
            $roomsInput = [
                ['adults' => $numberOfGuests, 'children' => 0]
            ];
        }

        $chosenCurrency = trim($input['currency'] ?? 'USD');
        $currencyRow = $this->db->queryOne('SELECT * FROM currencies WHERE code = ?', [$chosenCurrency]);
        if (!$currencyRow) {
            $chosenCurrency = 'USD'; // fallback
            $currencyRow = $this->db->queryOne('SELECT * FROM currencies WHERE code = ?', ['USD']);
        }
        $currencySymbol = $currencyRow['symbol'] ?? '$';

        $ratesRow = $this->db->queryOne(
            'SELECT price_per_adult, price_additional_adult, price_per_child FROM room_currency_prices WHERE room_id = ? AND currency = ?',
            [$room['id'], $chosenCurrency]
        );

        if ($ratesRow) {
            $pricePerAdult = floatval($ratesRow['price_per_adult']);
            $priceAdditionalAdult = floatval($ratesRow['price_additional_adult']);
            $pricePerChild = floatval($ratesRow['price_per_child']);
        } else {
            $pricePerAdult = floatval($room['price_per_adult'] ?? $room['price_per_night'] ?? 100.00);
            $priceAdditionalAdult = floatval($room['price_additional_adult'] ?? round($pricePerAdult * 0.5, 2));
            $pricePerChild = floatval($room['price_per_child'] ?? 40.00);
        }

        $totalPricePerNight = 0;
        $totalAdults = 0;
        $totalChildren = 0;
        $roomsCount = count($roomsInput);
        $maxGuests = intval($room['max_guests'] ?? 2);
        
        $details = [];
        foreach ($roomsInput as $index => $rm) {
            $adults = max(0, intval($rm['adults'] ?? 0));
            $children = max(0, intval($rm['children'] ?? 0));
            
            // Validate occupancy per room
            $totalInRoom = $adults + $children;
            if ($totalInRoom > $maxGuests) {
                http_response_code(400);
                echo json_encode(['error' => "Room " . ($index + 1) . " exceeds the maximum occupancy of {$maxGuests} guests."]);
                return;
            }
            if ($totalInRoom < 1) {
                http_response_code(400);
                echo json_encode(['error' => "Each room must have at least 1 guest."]);
                return;
            }
            
            if ($adults > 0) {
                $roomCost = $pricePerAdult + ($adults - 1) * $priceAdditionalAdult + ($children * $pricePerChild);
            } else {
                $roomCost = $children * $pricePerChild;
            }
            $totalPricePerNight += $roomCost;
            
            $totalAdults += $adults;
            $totalChildren += $children;
            $details[] = "Room " . ($index + 1) . ": {$adults} Adult" . ($adults !== 1 ? 's' : '') . ($children > 0 ? ", {$children} Child" . ($children !== 1 ? 'ren' : '') : '');
        }

        $totalPrice = $totalPricePerNight * $nights;
        $roomDetailsStr = implode('; ', $details);
        $totalNumberOfGuests = $totalAdults + $totalChildren;

        // Create booking
        try {
            $id = 'AZZ-' . bin2hex(random_bytes(4));
        } catch (Exception $e) {
            $id = 'AZZ-' . uniqid();
        }
        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO booking_rooms (id, guest_name, email, phone_number, check_in_date, check_out_date, room_type, number_of_guests, status, price_per_night, price_additional_adult, price_per_child, currency, total_price, rooms_count, adults_count, children_count, room_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$id, $guestName, $email, $phoneNumber, $checkInDate, $checkOutDate, $roomType, $totalNumberOfGuests, 'pending', $pricePerAdult, $priceAdditionalAdult, $pricePerChild, $chosenCurrency, $totalPrice, $roomsCount, $totalAdults, $totalChildren, $roomDetailsStr]
            );
            
            // Update room booked count
            $this->db->execute(
                'UPDATE rooms SET booked = booked + ? WHERE type = ?',
                [$roomsCount, $roomType]
            );
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Room booking error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create booking. Please try again.']);
            return;
        }

        $emailPayload = [
            'id' => $id,
            'guestName' => $guestName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'checkInDate' => $checkInDate,
            'checkOutDate' => $checkOutDate,
            'roomType' => $roomType,
            'numberOfGuests' => $totalNumberOfGuests,
            'pricePerNight' => $pricePerAdult,
            'priceAdditionalAdult' => $priceAdditionalAdult,
            'pricePerChild' => $pricePerChild,
            'currency' => $chosenCurrency,
            'currencySymbol' => $currencySymbol,
            'totalPrice' => $totalPrice,
            'nights' => $nights,
            'roomsCount' => $roomsCount,
            'adultsCount' => $totalAdults,
            'childrenCount' => $totalChildren,
            'roomDetails' => $roomDetailsStr,
        ];

        $this->respondJsonFast([
            'success' => true,
            'message' => 'Booking request sent successfully!',
            'id' => $id,
            'booking_type' => 'room',
            'total_price' => floatval($totalPrice),
            'currency' => $chosenCurrency,
            'payment_status' => 'unpaid',
        ], function () use ($emailPayload) {
            $this->queueBookingEmail('room', $emailPayload);
        });
    }

    
    /**
     * Book a spa treatment
     */
    public function bookSpa() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            return;
        }
        
        $guestName = trim($input['guestName'] ?? '');
        $email = trim($input['email'] ?? '');
        $phoneNumber = trim($input['phoneNumber'] ?? '');
        $service = trim($input['service'] ?? '');
        $date = $input['date'] ?? '';
        $time = trim($input['time'] ?? '');
        $chosenCurrency = strtoupper(trim($input['currency'] ?? 'USD'));
        
        // Validation
        if (empty($guestName) || empty($email) || empty($phoneNumber) || 
            empty($service) || empty($date) || empty($time)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address']);
            return;
        }
        
        $today = date('Y-m-d');
        if ($date < $today) {
            http_response_code(400);
            echo json_encode(['error' => 'Booking date cannot be in the past']);
            return;
        }

        $serviceRow = $this->db->queryOne('SELECT * FROM spa_services WHERE name = ? AND is_active = 1', [$service]);
        if (!$serviceRow) {
            http_response_code(400);
            echo json_encode(['error' => 'Selected spa service not found']);
            return;
        }
        $priceRow = $this->db->queryOne(
            'SELECT price FROM spa_service_prices WHERE service_id = ? AND currency = ?',
            [$serviceRow['id'], $chosenCurrency]
        );
        if (!$priceRow) {
            $priceRow = $this->db->queryOne(
                'SELECT price, currency FROM spa_service_prices WHERE service_id = ? ORDER BY id ASC LIMIT 1',
                [$serviceRow['id']]
            );
            if ($priceRow) {
                $chosenCurrency = $priceRow['currency'];
            }
        }
        $totalPrice = floatval($priceRow['price'] ?? 0);
        
        // Create booking
        try {
            $id = 'SPA-' . bin2hex(random_bytes(4));
        } catch (Exception $e) {
            $id = 'SPA-' . uniqid();
        }
        try {
            $this->db->execute(
                'INSERT INTO spa_bookings (id, guest_name, email, phone_number, service, date, time, status, total_price, currency, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$id, $guestName, $email, $phoneNumber, $service, $date, $time, 'pending', $totalPrice, $chosenCurrency, 'unpaid']
            );
        } catch (Exception $e) {
            error_log('Spa booking error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create booking. Please try again.']);
            return;
        }

        $emailPayload = [
            'id' => $id,
            'guestName' => $guestName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'service' => $service,
            'date' => $date,
            'time' => $time,
            'totalPrice' => $totalPrice,
            'currency' => $chosenCurrency,
        ];

        $this->respondJsonFast([
            'success' => true,
            'message' => 'Spa booking request sent successfully!',
            'id' => $id,
            'booking_type' => 'spa',
            'total_price' => $totalPrice,
            'currency' => $chosenCurrency,
            'payment_status' => 'unpaid',
        ], function () use ($emailPayload) {
            $this->queueBookingEmail('spa', $emailPayload);
        });
    }
    
    /**
     * Book a meeting/event
     */
    public function bookMeeting() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            return;
        }
        
        $contactName = trim($input['contactName'] ?? '');
        $email = trim($input['email'] ?? '');
        $phoneNumber = trim($input['phoneNumber'] ?? '');
        $companyName = trim($input['companyName'] ?? '');
        $venueName = trim($input['venueName'] ?? '');
        $date = $input['date'] ?? '';
        $chosenCurrency = strtoupper(trim($input['currency'] ?? 'USD'));
        
        // Validation
        if (empty($contactName) || empty($email) || empty($phoneNumber) || 
            empty($venueName) || empty($date)) {
            http_response_code(400);
            echo json_encode(['error' => 'All required fields must be filled']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address']);
            return;
        }
        
        $today = date('Y-m-d');
        if ($date < $today) {
            http_response_code(400);
            echo json_encode(['error' => 'Event date cannot be in the past']);
            return;
        }

        $venueRow = $this->db->queryOne('SELECT * FROM meeting_venues WHERE name = ? AND is_active = 1', [$venueName]);
        if (!$venueRow) {
            http_response_code(400);
            echo json_encode(['error' => 'Selected venue not found']);
            return;
        }
        $priceRow = $this->db->queryOne(
            'SELECT price FROM meeting_venue_prices WHERE venue_id = ? AND currency = ?',
            [$venueRow['id'], $chosenCurrency]
        );
        if (!$priceRow) {
            $priceRow = $this->db->queryOne(
                'SELECT price, currency FROM meeting_venue_prices WHERE venue_id = ? ORDER BY id ASC LIMIT 1',
                [$venueRow['id']]
            );
            if ($priceRow) {
                $chosenCurrency = $priceRow['currency'];
            }
        }
        $totalPrice = floatval($priceRow['price'] ?? 0);
        
        // Create booking
        try {
            $id = 'MEET-' . bin2hex(random_bytes(4));
        } catch (Exception $e) {
            $id = 'MEET-' . uniqid();
        }
        try {
            $this->db->execute(
                'INSERT INTO meeting_bookings (id, contact_name, email, phone_number, company_name, venue_name, date, status, total_price, currency, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$id, $contactName, $email, $phoneNumber, $companyName ?: null, $venueName, $date, 'pending', $totalPrice, $chosenCurrency, 'unpaid']
            );
        } catch (Exception $e) {
            error_log('Meeting booking error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create booking. Please try again.']);
            return;
        }

        $emailPayload = [
            'id' => $id,
            'contactName' => $contactName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'companyName' => $companyName,
            'venueName' => $venueName,
            'date' => $date,
            'totalPrice' => $totalPrice,
            'currency' => $chosenCurrency,
        ];

        $this->respondJsonFast([
            'success' => true,
            'message' => 'Event booking request sent successfully!',
            'id' => $id,
            'booking_type' => 'meeting',
            'total_price' => $totalPrice,
            'currency' => $chosenCurrency,
            'payment_status' => 'unpaid',
        ], function () use ($emailPayload) {
            $this->queueBookingEmail('meeting', $emailPayload);
        });
    }

    /**
     * Return JSON to the browser immediately, then run a tiny deferred task.
     */
    private function respondJsonFast(array $payload, callable $afterResponse = null): void {
        $body = json_encode($payload);
        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('Connection: close');
            header('Content-Length: ' . strlen($body));
        }

        echo $body;

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        @flush();

        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }

        if ($afterResponse === null) {
            return;
        }

        ignore_user_abort(true);
        try {
            $afterResponse();
        } catch (Exception $e) {
            error_log('Deferred booking task error: ' . $e->getMessage());
        }
    }

    /**
     * Queue booking emails in a background PHP process so SMTP never blocks booking/pay.
     */
    private function queueBookingEmail(string $kind, array $payload): void {
        $dir = defined('LOGS_PATH') ? LOGS_PATH : sys_get_temp_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $file = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'email_queue_' . uniqid('', true) . '.json';
        $written = @file_put_contents($file, json_encode([
            'kind' => $kind,
            'payload' => $payload,
        ]));
        if ($written === false) {
            error_log('Failed to queue booking email payload');
            return;
        }

        $php = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';
        $script = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2)) . DIRECTORY_SEPARATOR . 'cli_send_booking_email.php';

        if (!is_file($script)) {
            error_log('Missing cli_send_booking_email.php');
            return;
        }

        if (stripos(PHP_OS, 'WIN') === 0) {
            // Detach on Windows so the web request can end immediately.
            $cmd = 'start /B "" ' . escapeshellarg($php) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($file);
            pclose(@popen($cmd, 'r'));
            return;
        }

        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($file) . ' > /dev/null 2>&1 &';
        @exec($cmd);
    }
}
