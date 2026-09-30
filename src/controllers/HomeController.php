<?php
/**
 * Home Controller
 * 
 * Handles public-facing pages.
 */

class HomeController {
    private $db;
    private $legacyCategoryMap = null;
    
    public function __construct() {
        try {
            if (DB_HOST === '' || DB_NAME === '') {
                $this->db = null;
                return;
            }
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            error_log('HomeController: database unavailable — ' . $e->getMessage());
            $this->db = null;
        }
    }
    
    /**
     * Homepage
     */
    public function index() {
        $rooms = $this->getRoomsWithImages();
        $meetingVenues = $this->getMeetingVenuesPublic();
        $data = [
            'title' => 'Azzeman Hotel | 4-Star Hotel in Bole, Addis Ababa',
            'hero_image' => ViewHelper::siteImage('hero_background', 'https://images.unsplash.com/photo-1582719508461-905c673771fd?q=80&w=2070&auto=format&fit=crop'),
            'about_image' => ViewHelper::siteImage('about_image', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop'),
            'rooms_image' => ViewHelper::siteImage('rooms_image', 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1200&auto=format&fit=crop'),
            'meetings_image' => ViewHelper::siteImage('meetings_image', 'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=2070&auto=format&fit=crop'),
            'spa_images' => [
                ViewHelper::siteImage('spa_image_1', 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?q=80&w=800&auto=format&fit=crop'),
                ViewHelper::siteImage('spa_image_2', 'https://images.unsplash.com/photo-1519824145371-296894a0d72b?q=80&w=800&auto=format&fit=crop'),
                ViewHelper::siteImage('spa_image_3', 'https://images.unsplash.com/photo-1597015552392-4916a6953258?q=80&w=800&auto=format&fit=crop'),
                ViewHelper::siteImage('spa_image_4', 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=800&auto=format&fit=crop'),
            ],
            'rooms' => $rooms,
            'meeting_venues' => $meetingVenues,
            'gallery_categories' => $this->getGalleryCategories(),
            'gallery_images' => $this->getGalleryImages(),
        ];
        
        echo ViewHelper::render('home', $data);
    }
    
    /**
     * Gallery page
     */
    public function gallery() {
        $data = [
            'title' => 'Gallery - Azzeman Hotel',
            'categories' => $this->getGalleryCategories(),
            'images' => $this->getGalleryImages(),
        ];
        
        echo ViewHelper::render('gallery', $data);
    }
    
    /**
     * Gallery category page
     */
    public function galleryCategory($categorySlug) {
        $categories = $this->getGalleryCategories();
        $category = null;
        foreach ($categories as $cat) {
            if ($cat['id'] === $categorySlug) {
                $category = $cat;
                break;
            }
        }

        if (!$category) {
            http_response_code(404);
            echo 'Category not found';
            return;
        }

        $images = $this->db->query(
            'SELECT gi.*, ib.title, ib.content 
             FROM gallery_images gi 
             LEFT JOIN image_blogs ib ON ib.image_id = gi.id 
             WHERE LOWER(gi.category) = LOWER(?) OR LOWER(gi.category) = LOWER(?)
             ORDER BY gi.created_at DESC',
            [$category['id'], $category['name']]
        );
        $images = array_map([$this, 'transformGalleryRow'], $images);

        $data = [
            'title' => $category['name'] . ' - Gallery',
            'category' => $category,
            'images' => $images,
            'categories' => $categories,
            'fallback_image' => ViewHelper::siteImage('hero_background', 'https://images.unsplash.com/photo-1582719508461-905c673771fd?q=80&w=2070&auto=format&fit=crop'),
        ];

        echo ViewHelper::render('gallery-category', $data);
    }
    
    /**
     * Get rooms
     */
    private function getRooms() {
        if (!$this->db) {
            return [];
        }
        return $this->db->query('SELECT * FROM rooms ORDER BY id');
    }

    /**
     * Rooms with first gallery/site image for the landing elevator.
     */
    private function getRoomsWithImages() {
        $rooms = $this->getRooms();
        $fallback = ViewHelper::siteImage('rooms_image', '');
        foreach ($rooms as &$room) {
            if (!$this->db) {
                $room['display_image'] = $fallback;
                $room['chips'] = ['Wi-Fi', 'Air conditioning', 'Flat-screen TV'];
                continue;
            }
            $img = $room['image_path'] ?? '';
            try {
                $row = $this->db->queryOne(
                    'SELECT image_path FROM room_images WHERE room_id = ? ORDER BY id ASC LIMIT 1',
                    [$room['id']]
                );
                if (!empty($row['image_path'])) {
                    $img = $row['image_path'];
                }
            } catch (Exception $e) {
                // table may not exist yet
            }
            if ($img && preg_match('#^https?://#', $img) !== 1) {
                $img = ViewHelper::asset(ltrim($img, '/'));
            }
            if (!$img) {
                $img = $fallback;
            }
            $room['display_image'] = $img;
            $facilities = $room['facilities'] ?? '';
            $chips = array_values(array_filter(array_map('trim', preg_split('/[,;|]/', (string) $facilities) ?: [])));
            $room['chips'] = array_slice($chips ?: ['Wi-Fi', 'Air conditioning', 'Flat-screen TV'], 0, 4);
        }
        unset($room);
        return $rooms;
    }

    private function getMeetingVenuesPublic() {
        if (!$this->db) {
            return [];
        }
        try {
            return $this->db->query('SELECT id, name, capacity_note FROM meeting_venues WHERE is_active = 1 ORDER BY id');
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get gallery categories
     */
    private function getGalleryCategories() {
        if (!$this->db) {
            return [];
        }
        try {
            $rows = $this->db->query('SELECT id, name FROM gallery_categories ORDER BY name');
        } catch (Exception $e) {
            error_log('Gallery categories table missing or inaccessible: ' . $e->getMessage());
            $rows = [];
        }

        if (empty($rows)) {
            $rows = $this->db->query(
                'SELECT DISTINCT category AS fallback_name FROM gallery_images WHERE category IS NOT NULL AND category <> "" ORDER BY category'
            );
            return array_map(function ($row) {
                $name = $row['fallback_name'] ?? '';
                return [
                    'id' => $this->slugify($name),
                    'name' => $name,
                ];
            }, array_filter($rows, function ($row) {
                return !empty($row['fallback_name']);
            }));
        }

        return array_map(function ($row) {
            $id = $row['id'] ?? '';
            $name = $row['name'] ?? '';
            return [
                'id' => $id !== '' ? $id : $this->slugify($name),
                'name' => $name,
            ];
        }, $rows);
    }
    
    /**
     * Get gallery images
     */
    private function getGalleryImages() {
        if (!$this->db) {
            return [];
        }
        $images = $this->db->query(
            'SELECT gi.*, ib.title, ib.content,
                    gc.id AS category_id, gc.name AS category_name
             FROM gallery_images gi 
             LEFT JOIN image_blogs ib ON ib.image_id = gi.id 
             LEFT JOIN gallery_categories gc ON (
                 LOWER(gc.id) = LOWER(gi.category) OR LOWER(gc.name) = LOWER(gi.category)
             )
             ORDER BY gi.created_at DESC'
        );
        return array_map([$this, 'transformGalleryRow'], $images);
    }

    private function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $converted = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text ?: 'category';
    }

    private function transformGalleryRow(array $row): array {
         if (empty($row['image_path']) && !empty($row['src'])) {
             $row['image_path'] = $row['src'];
         }
 
        $categorySlug = $row['category_id'] ?? '';
        $categoryLabel = $row['category_name'] ?? $row['category'] ?? '';

        if ($categoryLabel === '' && $categorySlug !== '') {
            $categoryLabel = $this->getLegacyCategoryName($categorySlug) ?? $categorySlug;
        }

        if ($categorySlug === '' && $categoryLabel !== '') {
            $categorySlug = $this->slugify($categoryLabel);
        }

        $row['category'] = $categoryLabel;
        $row['category_slug'] = $categorySlug !== '' ? $categorySlug : 'all';

        return $row;
    }

    private function getLegacyCategoryName($id): ?string {
        if ($this->legacyCategoryMap === null) {
            $this->legacyCategoryMap = [];
            try {
                $rows = $this->db->query('SELECT id, name FROM gallery_categories');
                foreach ($rows as $row) {
                    $this->legacyCategoryMap[$row['id']] = $row['name'];
                }
            } catch (Exception $e) {
                $this->legacyCategoryMap = [];
            }
        }
        return $this->legacyCategoryMap[$id] ?? null;
    }

    private function getLegacyCategories(): array {
        try {
            return $this->db->query('SELECT name FROM gallery_categories ORDER BY name');
        } catch (Exception $e) {
            return [];
        }
    }
}

