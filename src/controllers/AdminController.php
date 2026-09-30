<?php
/**
 * Admin Controller
 * 
 * Handles admin dashboard and CMS operations.
 */

class AdminController {
    private $db;
    private $categoryTableEnsured = false;
    private $roomImagesTableEnsured = false;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->autoMigrateDatabase();
    }
    
    /**
     * Login form
     */
    public function loginForm() {
        // If already logged in, redirect to dashboard
        if (AuthHelper::isAuthenticated()) {
            $this->redirectToAdmin();
            exit;
        }
        
        $data = [
            'title' => 'Admin Login - Azzeman Hotel',
            'csrf_token' => AuthHelper::generateCsrfToken(),
        ];
        
        echo ViewHelper::render('admin/login', $data);
    }
    
    /**
     * Login handler
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin();
            exit;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            $this->redirectToLogin();
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username and password are required.';
            $this->redirectToLogin();
            exit;
        }
        
        // Find user
        $user = $this->db->queryOne(
            'SELECT * FROM admin_users WHERE username = ?',
            [$username]
        );
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['error'] = 'Invalid username or password.';
            $this->redirectToLogin();
            exit;
        }
        
        // Login successful
        AuthHelper::login($user['id'], $user['username']);
        $this->redirectToAdmin();
        exit;
    }
    
    /**
     * Get base path for redirects
     */
    private function getBasePath() {
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = rtrim($scriptPath, '/');

        if ($basePath === '.' || $basePath === '/') {
            $basePath = '';
        }

        if ($basePath === '' && defined('APP_URL') && APP_URL !== 'https://yourdomain.com') {
            $parts = parse_url(APP_URL);
            $configuredPath = isset($parts['path']) ? rtrim($parts['path'], '/') : '';
            $basePath = $configuredPath;
        }

        return $basePath;
    }
    
    /**
     * Redirect to login page
     */
    private function redirectToLogin() {
        $basePath = $this->getBasePath();
        header('Location: ' . $basePath . '/admin-login');
    }
    
    /**
     * Redirect to admin dashboard
     */
    private function redirectToAdmin() {
        $basePath = $this->getBasePath();
        header('Location: ' . $basePath . '/admin');
    }
    
    /**
     * Logout handler
     */
    public function logout() {
        AuthHelper::logout();
        $this->redirectToLogin();
        exit;
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard() {
        AuthHelper::requireAuth();
        $tab = $_GET['tab'] ?? 'rooms';
        
        // Get current user for profile tab
        $user = $this->db->queryOne(
            'SELECT * FROM admin_users WHERE id = ?',
            [AuthHelper::getAdminId()]
        );
        
        $data = [
            'title' => 'Admin Dashboard - Azzeman Hotel',
            'tab' => $tab,
            'room_bookings' => $this->getRoomBookings(),
            'spa_bookings' => $this->getSpaBookings(),
            'meeting_bookings' => $this->getMeetingBookings(),
            'gallery_categories' => $this->getGalleryCategories(),
            'gallery_items' => $this->getGalleryItems(),
            'site_images' => $this->getSiteImages(),
            'virtual_tour' => $this->getVirtualTour(),
            'rooms' => (function() {
                $rooms = $this->db->query('SELECT * FROM rooms ORDER BY id');
                foreach ($rooms as &$r) {
                    $r['price_tiers'] = $this->db->query(
                        'SELECT guest_count, price_per_night, currency FROM room_price_tiers WHERE room_id = ? ORDER BY guest_count',
                        [$r['id']]
                    );
                    $currencyPrices = $this->db->query(
                        'SELECT currency, price_per_adult, price_additional_adult, price_per_child FROM room_currency_prices WHERE room_id = ?',
                        [$r['id']]
                    );
                    $r['currency_prices'] = [];
                    foreach ($currencyPrices as $cp) {
                        $r['currency_prices'][$cp['currency']] = [
                            'adult' => floatval($cp['price_per_adult']),
                            'additional_adult' => floatval($cp['price_additional_adult']),
                            'child' => floatval($cp['price_per_child'])
                        ];
                    }
                }
                return $rooms;
            })(),
            'room_images' => $this->getRoomImages(),
            'spa_services' => $this->getSpaServices(),
            'meeting_venues' => $this->getMeetingVenues(),
            'currencies' => $this->db->query('SELECT * FROM currencies ORDER BY is_default DESC, code ASC'),
            'user' => $user,
            'csrf_token' => AuthHelper::generateCsrfToken(),
            'basePath' => $this->getBasePath(),
        ];
        
        echo ViewHelper::render('admin/dashboard', $data);
    }
    
    /**
     * Bookings page
     */
    public function bookings() {
        AuthHelper::requireAuth();
        $type = $_GET['type'] ?? 'rooms';
        
        $data = [
            'title' => 'Bookings - Admin Dashboard',
            'type' => $type,
            'bookings' => $type === 'rooms' ? $this->getRoomBookings() : 
                        ($type === 'spa' ? $this->getSpaBookings() : $this->getMeetingBookings()),
        ];
        
        header('Content-Type: application/json');
        echo json_encode($data['bookings']);
    }
    
    /**
     * Update booking status
     */
    public function updateBooking() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $id = $_POST['id'] ?? '';
        $type = $_POST['type'] ?? 'room';
        $status = $_POST['status'] ?? '';
        
        if (empty($id) || empty($status)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        $table = $type === 'room' ? 'booking_rooms' : 
                ($type === 'spa' ? 'spa_bookings' : 'meeting_bookings');
        
        $this->db->execute(
            "UPDATE {$table} SET status = ? WHERE id = ?",
            [$status, $id]
        );
        
        // If cancelling a room booking, free up the room
        if ($type === 'room' && $status === 'cancelled') {
            $booking = $this->db->queryOne(
                'SELECT room_type FROM booking_rooms WHERE id = ?',
                [$id]
            );
            if ($booking) {
                $this->db->execute(
                    'UPDATE rooms SET booked = GREATEST(0, booked - 1) WHERE type = ?',
                    [$booking['room_type']]
                );
            }
        }
        
        // Send Brevo email notification to customer on confirm/cancel
        if (in_array($status, ['confirmed', 'cancelled'])) {
            try {
                $bookingData = $this->db->queryOne(
                    "SELECT * FROM {$table} WHERE id = ?",
                    [$id]
                );
                
                if ($bookingData) {
                    require_once BASE_PATH . '/src/services/BrevoService.php';
                    $brevo = new BrevoService();
                    
                    if ($status === 'confirmed') {
                        $brevo->sendBookingApproved($type, $bookingData);
                    } else {
                        $brevo->sendBookingRejected($type, $bookingData);
                    }
                }
            } catch (Exception $e) {
                // Log but don't block the admin response
                error_log('Brevo email error in updateBooking: ' . $e->getMessage());
            }
        }
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Delete booking
     */
    public function deleteBooking() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $id = $_POST['id'] ?? '';
        $type = $_POST['type'] ?? 'room';
        
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing booking ID']);
            return;
        }
        
        $table = $type === 'room' ? 'booking_rooms' : 
                ($type === 'spa' ? 'spa_bookings' : 'meeting_bookings');
        
        // If deleting a room booking, free up the room
        if ($type === 'room') {
            $booking = $this->db->queryOne(
                'SELECT room_type, status FROM booking_rooms WHERE id = ?',
                [$id]
            );
            if ($booking && in_array($booking['status'], ['pending', 'confirmed'])) {
                $this->db->execute(
                    'UPDATE rooms SET booked = GREATEST(0, booked - 1) WHERE type = ?',
                    [$booking['room_type']]
                );
            }
        }
        
        $this->db->execute("DELETE FROM {$table} WHERE id = ?", [$id]);
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Gallery management
     */
    public function gallery() {
        AuthHelper::requireAuth();
        $images = $this->getGalleryItems();
        $data = [
            'title' => 'Gallery Management - Admin Dashboard',
            'gallery_items' => $images,
            'categories' => $this->getGalleryCategories(),
            'rooms' => $this->db->query('SELECT id, type FROM rooms ORDER BY id'),
            'room_images' => $this->getRoomImages(),
            'csrf_token' => AuthHelper::generateCsrfToken(),
            'basePath' => $this->getBasePath(),
        ];
        
        echo ViewHelper::render('admin/gallery', $data);
    }
    
    /**
     * Upload gallery image
     */
    public function uploadGalleryImage() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $category = trim($_POST['category'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $file = $_FILES['image'] ?? null;
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Please select an image to upload.']);
            return;
        }

        try {
            $relativePath = $this->processGalleryUpload($file);

            $this->db->beginTransaction();
            $this->db->execute(
                'INSERT INTO gallery_images (image_path, category) VALUES (?, ?)',
                [$relativePath, $category !== '' ? $category : null]
            );
            $imageId = $this->db->lastInsertId();

            $this->db->execute(
                'INSERT INTO image_blogs (image_id, title, content) VALUES (?, ?, ?)',
                [$imageId, $title !== '' ? $title : null, $content !== '' ? $content : null]
            );

            $this->db->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $imageId]);
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->rollback();
            }
            error_log('Gallery upload failed: ' . $e->getMessage());
            http_response_code(500);
            $message = 'Failed to upload image. Please try again.';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $message .= ' ' . $e->getMessage();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        }
    }

    /**
     * Handle gallery image upload
     */
    private function processGalleryUpload(array $file): string {
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            throw new Exception('File exceeds maximum upload size.');
        }

        $extensionMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        $mimeType = null;
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
        }
        if (!$mimeType && function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        }

        $originalExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!$mimeType && $originalExtension && isset($extensionMap[$originalExtension])) {
            $mimeType = $extensionMap[$originalExtension];
        }

        if (!$mimeType || !in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
            throw new Exception('Unsupported image type.');
        }

        $extension = array_search($mimeType, $extensionMap, true) ?: $originalExtension ?: 'jpg';

        if (!is_dir(GALLERY_UPLOAD_DIR)) {
            if (!mkdir(GALLERY_UPLOAD_DIR, 0755, true) && !is_dir(GALLERY_UPLOAD_DIR)) {
                throw new Exception('Failed to create uploads directory.');
            }
        }

        $filename = uniqid('gallery_', true) . '.' . $extension;
        $destination = GALLERY_UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to save uploaded file.');
        }

        return GALLERY_UPLOAD_URL . $filename;
    }
    
    /**
     * Update gallery image
     */
    public function updateGalleryImage() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id === null || $id === false) {
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        }
        $category = trim($_POST['category'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $file = $_FILES['image'] ?? null;
        
        if (empty($id)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing image ID']);
            return;
        }

        $image = $this->db->queryOne(
            'SELECT * FROM gallery_images WHERE id = ?',
            [$id]
        );
        if (!$image) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Image not found']);
            return;
        }

        $relativePath = $image['image_path'];
        $uploadedNewFile = false;
        $oldFileBasename = basename($image['image_path']);

        try {
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $relativePath = $this->processGalleryUpload($file);
                $uploadedNewFile = true;
            }

            $updates = [];
            $params = [];

            if ($uploadedNewFile) {
                $updates[] = 'image_path = ?';
                $params[] = $relativePath;
            }

            if ($category !== '') {
                $updates[] = 'category = ?';
                $params[] = $category;
            } else {
                $updates[] = 'category = NULL';
            }

            if (!empty($updates)) {
                $params[] = $id;
                $sql = 'UPDATE gallery_images SET ' . implode(', ', $updates) . ' WHERE id = ?';
                $this->db->execute($sql, $params);
            }

            $existingBlog = $this->db->queryOne(
                'SELECT id FROM image_blogs WHERE image_id = ?',
                [$id]
            );

            if ($existingBlog) {
                $this->db->execute(
                    'UPDATE image_blogs SET title = ?, content = ? WHERE image_id = ?',
                    [$title !== '' ? $title : null, $content !== '' ? $content : null, $id]
                );
            } else {
                $this->db->execute(
                    'INSERT INTO image_blogs (image_id, title, content) VALUES (?, ?, ?)',
                    [$id, $title !== '' ? $title : null, $content !== '' ? $content : null]
                );
            }

            if ($uploadedNewFile && $oldFileBasename) {
                $oldPath = GALLERY_UPLOAD_DIR . $oldFileBasename;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Gallery update failed: ' . $e->getMessage());
            if ($uploadedNewFile) {
                $newPath = GALLERY_UPLOAD_DIR . basename($relativePath);
                if (is_file($newPath)) {
                    @unlink($newPath);
                }
            }
            http_response_code(500);
            $message = 'Failed to update image. Please try again.';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $message .= ' ' . $e->getMessage();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        }
    }

    /**
     * Upload a booking image for a room type (many images allowed per room).
     */
    public function updateRoomImage() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }

        $this->ensureRoomImagesTable();

        $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        if (empty($roomId)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Please select a room type.']);
            return;
        }

        $room = $this->db->queryOne('SELECT id, type FROM rooms WHERE id = ?', [$roomId]);
        if (!$room) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Room type not found']);
            return;
        }

        $file = $_FILES['image'] ?? null;
        $uploadedNewFile = false;
        $relativePath = null;

        try {
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Please choose an image to upload.']);
                return;
            }

            $relativePath = $this->processRoomUpload($file);
            $uploadedNewFile = true;
            $this->db->execute(
                'INSERT INTO room_images (room_id, image_path) VALUES (?, ?)',
                [$roomId, $relativePath]
            );
            $imageId = (int) $this->db->lastInsertId();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'id' => $imageId,
                'room_id' => (int) $roomId,
                'room_type' => $room['type'],
                'image_path' => $relativePath,
                'image_url' => ViewHelper::asset(ltrim($relativePath, '/')),
            ]);
        } catch (Exception $e) {
            error_log('Room image upload failed: ' . $e->getMessage());
            if ($uploadedNewFile && $relativePath) {
                $this->deleteRoomUploadFile(basename($relativePath));
            }
            http_response_code(500);
            $message = 'Failed to upload room image. Please try again.';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $message .= ' ' . $e->getMessage();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        }
    }

    /**
     * Delete one room booking image by id.
     */
    public function deleteRoomImage() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }

        $this->ensureRoomImagesTable();

        $imageId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (empty($imageId)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing image ID']);
            return;
        }

        $image = $this->db->queryOne('SELECT id, image_path FROM room_images WHERE id = ?', [$imageId]);
        if (!$image) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Image not found']);
            return;
        }

        try {
            $this->db->execute('DELETE FROM room_images WHERE id = ?', [$imageId]);
            $this->deleteRoomUploadFile(basename($image['image_path'] ?? ''));
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Room image delete failed: ' . $e->getMessage());
            http_response_code(500);
            $message = 'Failed to delete room image. Please try again.';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $message .= ' ' . $e->getMessage();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        }
    }

    private function processRoomUpload(array $file): string {
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            throw new Exception('File exceeds maximum upload size.');
        }

        $extensionMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        $mimeType = null;
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
        }
        if (!$mimeType && function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        }

        $originalExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!$mimeType && $originalExtension && isset($extensionMap[$originalExtension])) {
            $mimeType = $extensionMap[$originalExtension];
        }

        if (!$mimeType || !in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
            throw new Exception('Unsupported image type.');
        }

        $extension = array_search($mimeType, $extensionMap, true) ?: $originalExtension ?: 'jpg';

        if (!is_dir(ROOM_UPLOAD_DIR)) {
            if (!mkdir(ROOM_UPLOAD_DIR, 0755, true) && !is_dir(ROOM_UPLOAD_DIR)) {
                throw new Exception('Failed to create room uploads directory.');
            }
        }

        $filename = uniqid('room_', true) . '.' . $extension;
        $destination = ROOM_UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to save uploaded file.');
        }

        return ROOM_UPLOAD_URL . $filename;
    }

    private function deleteRoomUploadFile(?string $basename): void {
        if (!$basename) {
            return;
        }
        // Only delete files from the rooms upload directory (not static assets)
        $filePath = ROOM_UPLOAD_DIR . $basename;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }
    
    /**
     * Delete gallery image
     */
    public function deleteGalleryImage() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (empty($id)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing image ID']);
            return;
        }

        $image = $this->db->queryOne(
            'SELECT image_path FROM gallery_images WHERE id = ?',
            [$id]
        );
        if (!$image) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Image not found']);
            return;
        }

        try {
            $this->db->execute('DELETE FROM gallery_images WHERE id = ?', [$id]);

            $fileBasename = basename($image['image_path']);
            if ($fileBasename) {
                $filePath = GALLERY_UPLOAD_DIR . $fileBasename;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Gallery delete failed: ' . $e->getMessage());
            http_response_code(500);
            $message = 'Failed to delete image. Please try again.';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $message .= ' ' . $e->getMessage();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        }
    }

    public function createGalleryCategory() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Category name is required.']);
            return;
        }

        $this->ensureGalleryCategoriesTable();

        $existing = $this->db->queryOne(
            'SELECT id FROM gallery_categories WHERE LOWER(name) = ?',
            [mb_strtolower($name)]
        );
        if ($existing) {
            http_response_code(409);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'A category with this name already exists.']);
            return;
        }

        try {
            $slug = $this->generateCategoryId($name);
            $this->db->execute(
                'INSERT INTO gallery_categories (id, name, description) VALUES (?, ?, ?)',
                [$slug, $name, $description !== '' ? $description : null]
            );

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'category' => [
                    'id' => $slug,
                    'name' => $name,
                    'description' => $description,
                ],
            ]);
        } catch (Exception $e) {
            error_log('Create gallery category failed: ' . $e->getMessage());
            http_response_code(500);
            $message = 'Failed to create category. Please try again.';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $message .= ' ' . $e->getMessage();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        }
    }

    public function updateGalleryCategory() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }

        $id = trim($_POST['id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id === '' || $name === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Category ID and name are required.']);
            return;
        }

        $this->ensureGalleryCategoriesTable();

        $category = $this->db->queryOne(
            'SELECT * FROM gallery_categories WHERE id = ?',
            [$id]
        );
        if (!$category) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Category not found.']);
            return;
        }

        $currentName = $category['name'] ?? '';
        if (mb_strtolower($currentName) !== mb_strtolower($name)) {
            $duplicate = $this->db->queryOne(
                'SELECT id FROM gallery_categories WHERE LOWER(name) = ? AND id <> ?',
                [mb_strtolower($name), $id]
            );
            if ($duplicate) {
                http_response_code(409);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Another category already uses that name.']);
                return;
            }
        }

        $this->db->execute(
            'UPDATE gallery_categories SET name = ?, description = ? WHERE id = ?',
            [$name, $description !== '' ? $description : null, $id]
        );

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'category' => [
                'id' => $id,
                'name' => $name,
                'description' => $description,
            ],
        ]);
    }

    public function deleteGalleryCategory() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }

        $id = trim($_POST['id'] ?? '');
        if ($id === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Category ID is required.']);
            return;
        }

        $this->ensureGalleryCategoriesTable();

        $category = $this->db->queryOne(
            'SELECT * FROM gallery_categories WHERE id = ?',
            [$id]
        );
        if (!$category) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Category not found.']);
            return;
        }

        $label = $category['name'] ?? '';
        $usage = $this->db->queryOne(
            'SELECT COUNT(*) AS total FROM gallery_images WHERE LOWER(category) IN (?, ?)',
            [mb_strtolower($id), mb_strtolower($label)]
        );
        if (($usage['total'] ?? 0) > 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Reassign or delete gallery stories in this category before removing it.']);
            return;
        }

        $this->db->execute('DELETE FROM gallery_categories WHERE id = ?', [$id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    private function generateCategoryId(string $name): string {
        $this->ensureGalleryCategoriesTable();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        if ($slug === '') {
            try {
                $slug = 'category-' . substr(bin2hex(random_bytes(3)), 0, 6);
            } catch (Exception $e) {
                $slug = 'category-' . time();
            }
        }

        $base = $slug;
        $counter = 1;
        while ($this->db->queryOne('SELECT id FROM gallery_categories WHERE id = ?', [$slug])) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
    
    /**
     * Site images management
     */
    public function siteImages() {
        AuthHelper::requireAuth();
        $data = [
            'title' => 'Site Images - Admin Dashboard',
            'site_images' => $this->getSiteImages(),
            'csrf_token' => AuthHelper::generateCsrfToken(),
        ];
        
        echo ViewHelper::render('admin/site-images', $data);
    }
    
    /**
     * Update site image
     */
    public function updateSiteImage() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $key = $_POST['key'] ?? '';
        $src = $_POST['src'] ?? '';
        
        if (empty($key) || empty($src)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        // Handle file upload if it's a base64 data URL
        // If it's a data URL, we can optionally save it as a file, but for now we'll store it as-is
        // If it's a regular URL, store it as-is
        // If it's a base64 data URL, store it as-is (can be large but works)
        
        // Optional: If it's a base64 data URL and you want to save it as a file:
        if (strpos($src, 'data:image/') === 0) {
            // Extract image data
            $matches = [];
            if (preg_match('/data:image\/(\w+);base64,(.+)/', $src, $matches)) {
                $imageType = $matches[1];
                $imageData = base64_decode($matches[2]);
                
                // Validate image data
                if ($imageData === false) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid base64 image data']);
                    return;
                }
                
                // Check file size (2MB limit)
                if (strlen($imageData) > UPLOAD_MAX_SIZE) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Image size exceeds 2MB limit']);
                    return;
                }
                
                // Save to uploads directory
                $uploadDir = UPLOAD_DIR . 'site-images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $filename = $key . '_' . time() . '.' . $imageType;
                $filepath = $uploadDir . $filename;
                
                if (file_put_contents($filepath, $imageData)) {
                    // Store relative URL instead of base64
                    $basePath = defined('APP_URL') && APP_URL !== 'https://yourdomain.com' 
                        ? rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/')
                        : '';
                    if (empty($basePath) || strpos($_SERVER['REQUEST_URI'], '/azzemanhotel_PHP') !== false) {
                        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                        $basePath = rtrim($scriptPath, '/');
                    }
                    $relativeUrl = $basePath . '/assets/uploads/site-images/' . $filename;
                    $src = $relativeUrl;
                }
                // If file save fails, fall back to storing base64 (but warn about size)
            }
        }
        
        // Store in database
        $this->db->execute(
            'INSERT INTO site_images (`key`, src) VALUES (?, ?) ON DUPLICATE KEY UPDATE src = ?',
            [$key, $src, $src]
        );
        
        echo json_encode(['success' => true, 'src' => $src]);
    }
    
    /**
     * Update room pricing, currency, details and per-guest price tiers
     */
    public function updateRoomPricing() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        header('Content-Type: application/json');

        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $id = intval($_POST['id'] ?? 0);
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing room ID']);
            return;
        }
        
        $allowedCurrencies = ['USD', 'ETB', 'EUR', 'GBP'];

        // Check if we are also editing room type details (full edit)
        $type       = trim($_POST['type'] ?? '');
        $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : null;
        $facilities = isset($_POST['facilities']) ? trim($_POST['facilities']) : null;
        $maxGuests  = isset($_POST['max_guests']) ? max(1, intval($_POST['max_guests'])) : null;

        // Base price & currency (fallback defaults)
        $pricePerAdult        = floatval($_POST['price_per_adult'] ?? 0);
        $pricePerChild        = floatval($_POST['price_per_child'] ?? 0);
        $priceAdditionalAdult = floatval($_POST['price_additional_adult'] ?? round($pricePerAdult * 0.5, 2));
        $basePrice            = $pricePerAdult; // price_per_night = price_per_adult for compat
        $baseCurrency         = trim($_POST['currency'] ?? 'USD');
        $allCurrencies        = $this->db->query('SELECT code FROM currencies');
        $allowedCurrencyCodes = array_column($allCurrencies, 'code');
        if (!in_array($baseCurrency, $allowedCurrencyCodes)) $baseCurrency = 'USD';

        // Per-currency prices: JSON object {code: {adult, additional_adult, child}}
        $currencyPricesRaw = $_POST['currency_prices'] ?? '{}';
        $currencyPrices    = json_decode($currencyPricesRaw, true) ?? [];

        $this->db->beginTransaction();
        try {
            if ($type !== '' && $quantity !== null && $facilities !== null) {
                // Full details edit
                $oldRoom = $this->db->queryOne('SELECT type FROM rooms WHERE id = ?', [$id]);
                if ($oldRoom && $oldRoom['type'] !== $type) {
                    $exists = $this->db->queryOne('SELECT id FROM rooms WHERE type = ? AND id <> ?', [$type, $id]);
                    if ($exists) {
                        $this->db->rollback();
                        http_response_code(400);
                        echo json_encode(['error' => 'A room type with this name already exists.']);
                        return;
                    }
                    $this->db->execute('UPDATE booking_rooms SET room_type = ? WHERE room_type = ?', [$type, $oldRoom['type']]);
                }

                $fields = 'type = ?, quantity = ?, facilities = ?, price_per_night = ?, price_per_adult = ?, price_per_child = ?, price_additional_adult = ?, currency = ?';
                $params = [$type, $quantity, $facilities, $basePrice, $pricePerAdult, $pricePerChild, $priceAdditionalAdult, $baseCurrency];
                if ($maxGuests !== null) { $fields .= ', max_guests = ?'; $params[] = $maxGuests; }
                $params[] = $id;
                $this->db->execute("UPDATE rooms SET {$fields} WHERE id = ?", $params);
            } else {
                $fields = 'price_per_night = ?, price_per_adult = ?, price_per_child = ?, price_additional_adult = ?, currency = ?';
                $params = [$basePrice, $pricePerAdult, $pricePerChild, $priceAdditionalAdult, $baseCurrency];
                if ($maxGuests !== null) { $fields .= ', max_guests = ?'; $params[] = $maxGuests; }
                $params[] = $id;
                $this->db->execute("UPDATE rooms SET {$fields} WHERE id = ?", $params);
            }

            // Save per-currency price rows
            foreach ($currencyPrices as $code => $rates) {
                if (!in_array($code, $allowedCurrencyCodes)) continue;
                $pAdult = floatval($rates['adult'] ?? $pricePerAdult);
                $pAdd   = floatval($rates['additional_adult'] ?? $priceAdditionalAdult);
                $pChild = floatval($rates['child'] ?? $pricePerChild);
                $this->db->execute(
                    'INSERT INTO room_currency_prices (room_id, currency, price_per_adult, price_additional_adult, price_per_child)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE price_per_adult = VALUES(price_per_adult),
                       price_additional_adult = VALUES(price_additional_adult),
                       price_per_child = VALUES(price_per_child)',
                    [$id, $code, $pAdult, $pAdd, $pChild]
                );
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('updateRoomPricing error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update room pricing.']);
            return;
        }
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Create new room type
     */
    public function createRoomPricing() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        header('Content-Type: application/json');

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $type                 = trim($_POST['type'] ?? '');
        $quantity             = intval($_POST['quantity'] ?? 0);
        $facilities           = trim($_POST['facilities'] ?? '');
        $pricePerAdult        = floatval($_POST['price_per_adult'] ?? 100.00);
        $pricePerChild        = floatval($_POST['price_per_child'] ?? 50.00);
        $priceAdditionalAdult = floatval($_POST['price_additional_adult'] ?? round($pricePerAdult * 0.5, 2));
        $price                = $pricePerAdult;
        $currency             = trim($_POST['currency'] ?? 'USD');
        $maxGuests            = max(1, intval($_POST['max_guests'] ?? 2));
        $currencyPricesRaw    = $_POST['currency_prices'] ?? '{}';
        $currencyPrices       = json_decode($currencyPricesRaw, true) ?? [];

        $allCurrencies        = $this->db->query('SELECT code FROM currencies');
        $allowedCurrencyCodes = array_column($allCurrencies, 'code');
        if (!in_array($currency, $allowedCurrencyCodes)) $currency = 'USD';
        
        if ($type === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Room type name is required']);
            return;
        }
        
        $exists = $this->db->queryOne('SELECT id FROM rooms WHERE type = ?', [$type]);
        if ($exists) {
            http_response_code(400);
            echo json_encode(['error' => 'A room type with this name already exists.']);
            return;
        }
        
        $this->db->beginTransaction();
        try {
            $this->db->execute(
                'INSERT INTO rooms (type, quantity, facilities, booked, price_per_night, currency, max_guests, price_per_adult, price_per_child, price_additional_adult) VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?)',
                [$type, $quantity, $facilities, $price, $currency, $maxGuests, $pricePerAdult, $pricePerChild, $priceAdditionalAdult]
            );
            $newId = $this->db->lastInsertId();

            // Seed per-currency price rows
            foreach ($currencyPrices as $code => $rates) {
                if (!in_array($code, $allowedCurrencyCodes)) continue;
                $pAdult = floatval($rates['adult'] ?? $pricePerAdult);
                $pAdd   = floatval($rates['additional_adult'] ?? $priceAdditionalAdult);
                $pChild = floatval($rates['child'] ?? $pricePerChild);
                $this->db->execute(
                    'INSERT INTO room_currency_prices (room_id, currency, price_per_adult, price_additional_adult, price_per_child) VALUES (?, ?, ?, ?, ?)',
                    [$newId, $code, $pAdult, $pAdd, $pChild]
                );
            }
            // Seed default row for base currency if not in currencyPrices
            if (!isset($currencyPrices[$currency])) {
                $this->db->execute(
                    'INSERT IGNORE INTO room_currency_prices (room_id, currency, price_per_adult, price_additional_adult, price_per_child) VALUES (?, ?, ?, ?, ?)',
                    [$newId, $currency, $pricePerAdult, $priceAdditionalAdult, $pricePerChild]
                );
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('createRoomPricing error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create room type.']);
            return;
        }
        
        echo json_encode(['success' => true]);
    }

    /**
     * Delete room type
     */
    public function deleteRoomPricing() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        header('Content-Type: application/json');

        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $id = intval($_POST['id'] ?? 0);
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing room ID']);
            return;
        }
        
        // Check if there are active bookings for this room type
        $room = $this->db->queryOne('SELECT type FROM rooms WHERE id = ?', [$id]);
        if ($room) {
            $hasBookings = $this->db->queryOne(
                "SELECT COUNT(*) as count FROM booking_rooms WHERE room_type = ? AND status IN ('pending', 'confirmed')",
                [$room['type']]
            );
            if ($hasBookings && $hasBookings['count'] > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Cannot delete this room type because it has active bookings.']);
                return;
            }
        }
        
        $this->db->execute('DELETE FROM room_price_tiers WHERE room_id = ?', [$id]);
        $this->db->execute('DELETE FROM room_currency_prices WHERE room_id = ?', [$id]);
        $this->db->execute('DELETE FROM rooms WHERE id = ?', [$id]);
        
        echo json_encode(['success' => true]);
    }

    /**
     * Create a new currency
     */
    public function createCurrency() {
        AuthHelper::requireAuth();
        header('Content-Type: application/json');
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403); echo json_encode(['error' => 'Invalid security token']); return;
        }
        $code   = strtoupper(trim($_POST['code'] ?? ''));
        $symbol = trim($_POST['symbol'] ?? '');
        $name   = trim($_POST['name'] ?? '');
        if (strlen($code) < 2 || strlen($code) > 10 || $symbol === '' || $name === '') {
            http_response_code(400); echo json_encode(['error' => 'Code (2-10 chars), symbol, and name are required']); return;
        }
        try {
            $this->db->execute(
                'INSERT INTO currencies (code, symbol, name, is_default) VALUES (?, ?, ?, 0)',
                [$code, $symbol, $name]
            );
            // Seed room_currency_prices for all existing rooms
            $allRooms = $this->db->query('SELECT id, price_per_adult, price_additional_adult, price_per_child FROM rooms');
            foreach ($allRooms as $rm) {
                $this->db->execute(
                    'INSERT IGNORE INTO room_currency_prices (room_id, currency, price_per_adult, price_additional_adult, price_per_child) VALUES (?, ?, ?, ?, ?)',
                    [$rm['id'], $code,
                     floatval($rm['price_per_adult'] ?? 100),
                     floatval($rm['price_additional_adult'] ?? 50),
                     floatval($rm['price_per_child'] ?? 40)]
                );
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500); echo json_encode(['error' => 'Failed to create currency: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a currency (cannot delete the default)
     */
    public function deleteCurrency() {
        AuthHelper::requireAuth();
        header('Content-Type: application/json');
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403); echo json_encode(['error' => 'Invalid security token']); return;
        }
        $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($code === '') { http_response_code(400); echo json_encode(['error' => 'Currency code required']); return; }
        $cur = $this->db->queryOne('SELECT is_default FROM currencies WHERE code = ?', [$code]);
        if (!$cur) { http_response_code(404); echo json_encode(['error' => 'Currency not found']); return; }
        if ($cur['is_default']) { http_response_code(400); echo json_encode(['error' => 'Cannot delete the default currency. Set another as default first.']); return; }
        $this->db->execute('DELETE FROM room_currency_prices WHERE currency = ?', [$code]);
        $this->db->execute('DELETE FROM currencies WHERE code = ?', [$code]);
        echo json_encode(['success' => true]);
    }

    /**
     * Set a currency as default
     */
    public function setDefaultCurrency() {
        AuthHelper::requireAuth();
        header('Content-Type: application/json');
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403); echo json_encode(['error' => 'Invalid security token']); return;
        }
        $code = strtoupper(trim($_POST['code'] ?? ''));
        if ($code === '') { http_response_code(400); echo json_encode(['error' => 'Currency code required']); return; }
        $cur = $this->db->queryOne('SELECT code FROM currencies WHERE code = ?', [$code]);
        if (!$cur) { http_response_code(404); echo json_encode(['error' => 'Currency not found']); return; }
        $this->db->execute('UPDATE currencies SET is_default = 0');
        $this->db->execute('UPDATE currencies SET is_default = 1 WHERE code = ?', [$code]);
        echo json_encode(['success' => true]);
    }
    
    /**
     * Virtual tour management
     */
    public function virtualTour() {
        AuthHelper::requireAuth();
        $data = [
            'title' => 'Virtual Tour - Admin Dashboard',
            'virtual_tour' => $this->getVirtualTour(),
            'csrf_token' => AuthHelper::generateCsrfToken(),
        ];
        
        echo ViewHelper::render('admin/virtual-tour', $data);
    }
    
    /**
     * Update virtual tour
     */
    public function updateVirtualTour() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $imageUrl = $_POST['image_url'] ?? '';
        $hotspots = json_decode($_POST['hotspots'] ?? '[]', true);
        
        // Update tour image
        if (!empty($imageUrl)) {
            $this->db->execute(
                'INSERT INTO virtual_tours (id, image_url) VALUES (1, ?) ON DUPLICATE KEY UPDATE image_url = ?',
                [$imageUrl, $imageUrl]
            );
        }
        
        // Update hotspots
        if (is_array($hotspots)) {
            $this->db->beginTransaction();
            try {
                $this->db->execute('DELETE FROM hotspots WHERE tour_id = 1', []);
                foreach ($hotspots as $hotspot) {
                    $this->db->execute(
                        'INSERT INTO hotspots (id, pitch, yaw, text, tour_id) VALUES (?, ?, ?, ?, 1)',
                        [
                            $hotspot['id'] ?? 'hs-' . time() . '-' . rand(1000, 9999),
                            $hotspot['pitch'] ?? 0,
                            $hotspot['yaw'] ?? 0,
                            $hotspot['text'] ?? '',
                        ]
                    );
                }
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        }
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Profile page
     */
    public function profile() {
        AuthHelper::requireAuth();
        $user = $this->db->queryOne(
            'SELECT * FROM admin_users WHERE id = ?',
            [AuthHelper::getAdminId()]
        );
        
        $data = [
            'title' => 'Profile - Admin Dashboard',
            'user' => $user,
            'csrf_token' => AuthHelper::generateCsrfToken(),
        ];
        
        echo ViewHelper::render('admin/profile', $data);
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        AuthHelper::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        
        $userId = AuthHelper::getAdminId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        
        // Verify current password
        $user = $this->db->queryOne(
            'SELECT * FROM admin_users WHERE id = ?',
            [$userId]
        );
        
        if (!password_verify($currentPassword, $user['password_hash'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Incorrect current password']);
            return;
        }
        
        // Update username if provided
        if (!empty($newUsername) && $newUsername !== $user['username']) {
            try {
                $this->db->execute(
                    'UPDATE admin_users SET username = ? WHERE id = ?',
                    [$newUsername, $userId]
                );
                $_SESSION['admin_username'] = $newUsername;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    http_response_code(400);
                    echo json_encode(['error' => 'Username already taken']);
                    return;
                }
                throw $e;
            }
        }
        
        // Update password if provided
        if (!empty($newPassword)) {
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                http_response_code(400);
                echo json_encode(['error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters']);
                return;
            }
            
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->execute(
                'UPDATE admin_users SET password_hash = ? WHERE id = ?',
                [$passwordHash, $userId]
            );
        }
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    }
    
    // Helper methods
    private function getRoomBookings() {
        return $this->db->query('SELECT * FROM booking_rooms ORDER BY created_at DESC');
    }
    
    private function getSpaBookings() {
        return $this->db->query('SELECT * FROM spa_bookings ORDER BY created_at DESC');
    }
    
    private function getMeetingBookings() {
        return $this->db->query('SELECT * FROM meeting_bookings ORDER BY created_at DESC');
    }
    
    private function getGalleryItems() {
        return $this->db->query(
            'SELECT gi.*, ib.title, ib.content,
                    gc.id AS category_id, gc.name AS category_name
             FROM gallery_images gi
             LEFT JOIN image_blogs ib ON ib.image_id = gi.id
             LEFT JOIN gallery_categories gc ON (
                 LOWER(gc.id) = LOWER(gi.category) OR LOWER(gc.name) = LOWER(gi.category)
             )
             ORDER BY gi.created_at DESC'
        );
    }
    
    private function getGalleryCategories() {
        $this->ensureGalleryCategoriesTable();
        try {
            $categories = $this->db->query(
                'SELECT id, name, description
                 FROM gallery_categories
                 ORDER BY name'
            );
        } catch (Exception $e) {
            error_log('Gallery categories query failed: ' . $e->getMessage());
            $categories = [];
        }

        if (!empty($categories)) {
            return $categories;
        }

        $fallback = $this->db->query(
            'SELECT DISTINCT category FROM gallery_images WHERE category IS NOT NULL AND category <> "" ORDER BY category'
        );

        $results = [];
        foreach ($fallback as $row) {
            $name = $row['category'] ?? '';
            if ($name === '') {
                continue;
            }
            $results[] = [
                'id' => $this->slugifyCategoryName($name),
                'name' => $name,
                'description' => null,
            ];
        }

        return $results;
    }
    
    private function getSiteImages() {
        $images = $this->db->query('SELECT * FROM site_images');
        $result = [];
        foreach ($images as $image) {
            $result[$image['key']] = $image['src'];
        }
        return $result;
    }
    
    private function getVirtualTour() {
        $tour = $this->db->queryOne('SELECT * FROM virtual_tours WHERE id = 1');
        $hotspots = $this->db->query('SELECT * FROM hotspots WHERE tour_id = 1');
        
        return [
            'image_url' => $tour['image_url'] ?? '',
            'hotspots' => $hotspots,
        ];
    }

    private function ensureGalleryCategoriesTable(): void {
        if ($this->categoryTableEnsured) {
            return;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS gallery_categories (
                    id VARCHAR(100) NOT NULL,
                    name VARCHAR(150) NOT NULL,
                    description TEXT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY idx_gallery_categories_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        try {
            $this->db->execute($sql);
            $this->categoryTableEnsured = true;
        } catch (Exception $e) {
            error_log('Failed to ensure gallery_categories table: ' . $e->getMessage());
        }
    }

    private function ensureRoomImagesTable(): void {
        if ($this->roomImagesTableEnsured) {
            return;
        }

        try {
            $this->db->execute(
                'CREATE TABLE IF NOT EXISTS room_images (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    room_id INT(11) NOT NULL,
                    image_path VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_room_images_room_id (room_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            // Move any legacy single image_path on rooms into room_images once
            $roomsCols = $this->db->query('SHOW COLUMNS FROM rooms');
            $existingRoomsCols = array_column($roomsCols, 'Field');
            if (in_array('image_path', $existingRoomsCols, true)) {
                $legacyRooms = $this->db->query(
                    "SELECT id, image_path FROM rooms WHERE image_path IS NOT NULL AND image_path != ''"
                );
                foreach ($legacyRooms as $legacy) {
                    $exists = $this->db->queryOne(
                        'SELECT id FROM room_images WHERE room_id = ? AND image_path = ? LIMIT 1',
                        [$legacy['id'], $legacy['image_path']]
                    );
                    if (!$exists) {
                        $this->db->execute(
                            'INSERT INTO room_images (room_id, image_path) VALUES (?, ?)',
                            [$legacy['id'], $legacy['image_path']]
                        );
                    }
                    $this->db->execute('UPDATE rooms SET image_path = NULL WHERE id = ?', [$legacy['id']]);
                }
            }

            $this->roomImagesTableEnsured = true;
        } catch (Exception $e) {
            error_log('Failed to ensure room_images table: ' . $e->getMessage());
        }
    }

    private function getRoomImages(): array {
        $this->ensureRoomImagesTable();
        return $this->db->query(
            'SELECT ri.id, ri.room_id, ri.image_path, ri.created_at, r.type AS room_type
             FROM room_images ri
             INNER JOIN rooms r ON r.id = ri.room_id
             ORDER BY r.type ASC, ri.id DESC'
        );
    }

    private function slugifyCategoryName(string $name): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        return $slug !== '' ? $slug : 'category';
    }

    private function autoMigrateDatabase() {
        try {
            $this->ensureRoomImagesTable();

            // -- Currencies table --
            $this->db->execute("CREATE TABLE IF NOT EXISTS `currencies` (
                `code` VARCHAR(10) NOT NULL,
                `symbol` VARCHAR(10) NOT NULL DEFAULT '',
                `name` VARCHAR(50) NOT NULL DEFAULT '',
                `is_default` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $currCount = $this->db->queryOne("SELECT COUNT(*) as cnt FROM currencies");
            if (intval($currCount['cnt'] ?? 0) === 0) {
                $this->db->execute("INSERT INTO currencies (code, symbol, name, is_default) VALUES
                    ('USD', '$', 'US Dollar', 1),
                    ('ETB', 'Br', 'Ethiopian Birr', 0),
                    ('EUR', '€', 'Euro', 0),
                    ('GBP', '£', 'British Pound', 0)
                ");
            }

            // -- room_currency_prices table --
            $this->db->execute("CREATE TABLE IF NOT EXISTS `room_currency_prices` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `room_id` INT(11) NOT NULL,
                `currency` VARCHAR(10) NOT NULL,
                `price_per_adult` DECIMAL(10, 2) NOT NULL DEFAULT 100.00,
                `price_additional_adult` DECIMAL(10, 2) NOT NULL DEFAULT 50.00,
                `price_per_child` DECIMAL(10, 2) NOT NULL DEFAULT 40.00,
                PRIMARY KEY (`id`),
                UNIQUE KEY `room_currency` (`room_id`, `currency`),
                KEY `idx_room_id` (`room_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // -- rooms columns --
            $roomsCols = $this->db->query("SHOW COLUMNS FROM rooms");
            $existingRoomsCols = array_column($roomsCols, 'Field');
            
            $roomsColumnsToAdd = [
                'price_per_night'        => 'DECIMAL(10, 2) NOT NULL DEFAULT 100.00',
                'currency'               => "VARCHAR(10) NOT NULL DEFAULT 'USD'",
                'max_guests'             => 'INT(11) NOT NULL DEFAULT 2',
                'price_per_adult'        => 'DECIMAL(10, 2) NOT NULL DEFAULT 100.00',
                'price_per_child'        => 'DECIMAL(10, 2) NOT NULL DEFAULT 50.00',
                'price_additional_adult' => 'DECIMAL(10, 2) NOT NULL DEFAULT 50.00',
                'image_path'             => 'VARCHAR(255) DEFAULT NULL',
            ];
            
            foreach ($roomsColumnsToAdd as $col => $definition) {
                if (!in_array($col, $existingRoomsCols)) {
                    $this->db->execute("ALTER TABLE rooms ADD COLUMN $col $definition");
                    if ($col === 'price_per_adult') {
                        $this->db->execute("UPDATE rooms SET price_per_adult = price_per_night");
                    }
                    if ($col === 'price_per_child') {
                        $this->db->execute("UPDATE rooms SET price_per_child = ROUND(price_per_night * 0.4)");
                    }
                    if ($col === 'price_additional_adult') {
                        $this->db->execute("UPDATE rooms SET price_additional_adult = ROUND(price_per_night * 0.5)");
                    }
                }
            }

            // Remove legacy discount columns if present (optional, non-fatal)
            foreach (['discount_3_6', 'discount_7_plus'] as $legacyCol) {
                if (in_array($legacyCol, $existingRoomsCols)) {
                    try {
                        $this->db->execute("ALTER TABLE rooms DROP COLUMN $legacyCol");
                    } catch (Exception $ignore) {}
                }
            }
            
            // -- booking_rooms columns --
            $bookingCols = $this->db->query("SHOW COLUMNS FROM booking_rooms");
            $existingBookingCols = array_column($bookingCols, 'Field');
            
            $bookingColumnsToAdd = [
                'price_per_night'        => 'DECIMAL(10, 2) DEFAULT NULL',
                'price_per_child'        => 'DECIMAL(10, 2) DEFAULT NULL',
                'price_additional_adult' => 'DECIMAL(10, 2) DEFAULT NULL',
                'currency'               => 'VARCHAR(10) DEFAULT NULL',
                'total_price'            => 'DECIMAL(10, 2) DEFAULT NULL',
                'guests_count'           => 'INT(11) DEFAULT NULL',
                'rooms_count'            => 'INT(11) NOT NULL DEFAULT 1',
                'adults_count'           => 'INT(11) NOT NULL DEFAULT 1',
                'children_count'         => 'INT(11) NOT NULL DEFAULT 0',
                'room_details'           => 'TEXT DEFAULT NULL',
                'payment_status'         => "VARCHAR(50) NOT NULL DEFAULT 'unpaid'",
            ];
            
            foreach ($bookingColumnsToAdd as $col => $definition) {
                if (!in_array($col, $existingBookingCols)) {
                    $this->db->execute("ALTER TABLE booking_rooms ADD COLUMN $col $definition");
                }
            }

            // Remove legacy discount_amount column if present
            if (in_array('discount_amount', $existingBookingCols)) {
                try {
                    $this->db->execute("ALTER TABLE booking_rooms DROP COLUMN discount_amount");
                } catch (Exception $ignore) {}
            }

            // Create room_price_tiers table for backwards compat
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS `room_price_tiers` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `room_id` INT(11) NOT NULL,
                    `guest_count` INT(11) NOT NULL,
                    `price_per_night` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `room_guest` (`room_id`, `guest_count`, `currency`),
                    KEY `idx_room_id` (`room_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $this->ensureSpaMeetingCatalog();

            // Auto-seed room_currency_prices for existing rooms
            $allRooms = $this->db->query("SELECT id, price_per_adult, price_per_child, price_additional_adult FROM rooms");
            $allCurrencies = $this->db->query("SELECT code FROM currencies");
            foreach ($allRooms as $rm) {
                foreach ($allCurrencies as $cur) {
                    $existing = $this->db->queryOne(
                        "SELECT id FROM room_currency_prices WHERE room_id = ? AND currency = ?",
                        [$rm['id'], $cur['code']]
                    );
                    if (!$existing) {
                        $this->db->execute(
                            "INSERT INTO room_currency_prices (room_id, currency, price_per_adult, price_additional_adult, price_per_child) VALUES (?, ?, ?, ?, ?)",
                            [$rm['id'], $cur['code'],
                             floatval($rm['price_per_adult'] ?? 100),
                             floatval($rm['price_additional_adult'] ?? 50),
                             floatval($rm['price_per_child'] ?? 40)]
                        );
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Auto-migration error: ' . $e->getMessage());
        }
    }

    private function ensureSpaMeetingCatalog(): void {
        $this->db->execute("CREATE TABLE IF NOT EXISTS spa_services (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->execute("CREATE TABLE IF NOT EXISTS spa_service_prices (
            id INT(11) NOT NULL AUTO_INCREMENT,
            service_id INT(11) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (id),
            UNIQUE KEY service_currency (service_id, currency),
            KEY idx_service_id (service_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->execute("CREATE TABLE IF NOT EXISTS meeting_venues (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            capacity_note VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->execute("CREATE TABLE IF NOT EXISTS meeting_venue_prices (
            id INT(11) NOT NULL AUTO_INCREMENT,
            venue_id INT(11) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (id),
            UNIQUE KEY venue_currency (venue_id, currency),
            KEY idx_venue_id (venue_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        foreach (['spa_bookings', 'meeting_bookings', 'booking_rooms'] as $table) {
            $cols = array_column($this->db->query("SHOW COLUMNS FROM `{$table}`"), 'Field');
            if (!in_array('payment_status', $cols, true)) {
                $this->db->execute("ALTER TABLE `{$table}` ADD COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid'");
            }
            if ($table !== 'booking_rooms') {
                if (!in_array('total_price', $cols, true)) {
                    $this->db->execute("ALTER TABLE `{$table}` ADD COLUMN total_price DECIMAL(10,2) DEFAULT NULL");
                }
                if (!in_array('currency', $cols, true)) {
                    $this->db->execute("ALTER TABLE `{$table}` ADD COLUMN currency VARCHAR(10) DEFAULT NULL");
                }
            }
        }

        if (in_array('payment_status', array_column($this->db->query('SHOW COLUMNS FROM booking_rooms'), 'Field'), true) === false) {
            // already handled above
        }

        $spaCount = $this->db->queryOne('SELECT COUNT(*) AS cnt FROM spa_services');
        if (intval($spaCount['cnt'] ?? 0) === 0) {
            $spaSeed = [
                ['Swedish Massage', 80],
                ['Deep Tissue Massage', 95],
                ['Aromatherapy Facial', 70],
                ['Hot Stone Therapy', 110],
            ];
            foreach ($spaSeed as [$name, $usd]) {
                $this->db->execute('INSERT INTO spa_services (name) VALUES (?)', [$name]);
                $id = $this->db->lastInsertId();
                $this->seedCatalogPrices('spa_service_prices', 'service_id', $id, $usd);
            }
        }

        $meetCount = $this->db->queryOne('SELECT COUNT(*) AS cnt FROM meeting_venues');
        if (intval($meetCount['cnt'] ?? 0) === 0) {
            $meetSeed = [
                ['Tiya', '(40-60 Pax) - 2nd Floor', 250],
                ['Jegol', '(15-25 Pax) - 2nd Floor', 150],
                ['Sofumer', '(50-100 Pax)', 350],
                ['Entoto', '(200-400 Pax)', 500],
                ['Ras Dashen', '(15-35 Pax)', 180],
            ];
            foreach ($meetSeed as [$name, $note, $usd]) {
                $this->db->execute('INSERT INTO meeting_venues (name, capacity_note) VALUES (?, ?)', [$name, $note]);
                $id = $this->db->lastInsertId();
                $this->seedCatalogPrices('meeting_venue_prices', 'venue_id', $id, $usd);
            }
        }
    }

    private function seedCatalogPrices(string $table, string $fkCol, $fkId, float $usd): void {
        $map = [
            'USD' => $usd,
            'ETB' => $usd * 100,
            'EUR' => round($usd * 0.9, 2),
            'GBP' => round($usd * 0.8, 2),
        ];
        foreach ($map as $code => $price) {
            $this->db->execute(
                "INSERT INTO {$table} ({$fkCol}, currency, price) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE price = VALUES(price)",
                [$fkId, $code, $price]
            );
        }
    }

    public function getSpaServices(): array {
        $this->ensureSpaMeetingCatalog();
        $services = $this->db->query('SELECT * FROM spa_services ORDER BY id');
        foreach ($services as &$s) {
            $prices = $this->db->query('SELECT currency, price FROM spa_service_prices WHERE service_id = ?', [$s['id']]);
            $s['currency_prices'] = [];
            foreach ($prices as $p) {
                $s['currency_prices'][$p['currency']] = floatval($p['price']);
            }
        }
        return $services;
    }

    public function getMeetingVenues(): array {
        $this->ensureSpaMeetingCatalog();
        $venues = $this->db->query('SELECT * FROM meeting_venues ORDER BY id');
        foreach ($venues as &$v) {
            $prices = $this->db->query('SELECT currency, price FROM meeting_venue_prices WHERE venue_id = ?', [$v['id']]);
            $v['currency_prices'] = [];
            foreach ($prices as $p) {
                $v['currency_prices'][$p['currency']] = floatval($p['price']);
            }
        }
        return $venues;
    }

    public function saveSpaService() {
        AuthHelper::requireAuth();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        $this->ensureSpaMeetingCatalog();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        $name = trim($_POST['name'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $prices = $_POST['prices'] ?? [];
        if ($name === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Service name is required']);
            return;
        }
        try {
            if ($id) {
                $this->db->execute('UPDATE spa_services SET name = ?, is_active = ? WHERE id = ?', [$name, $isActive, $id]);
            } else {
                $this->db->execute('INSERT INTO spa_services (name, is_active) VALUES (?, ?)', [$name, 1]);
                $id = (int) $this->db->lastInsertId();
            }
            if (is_array($prices)) {
                foreach ($prices as $currency => $price) {
                    $currency = strtoupper(trim((string) $currency));
                    if ($currency === '') continue;
                    $this->db->execute(
                        'INSERT INTO spa_service_prices (service_id, currency, price) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE price = VALUES(price)',
                        [$id, $currency, floatval($price)]
                    );
                }
            }
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save spa service']);
        }
    }

    public function deleteSpaService() {
        AuthHelper::requireAuth();
        header('Content-Type: application/json');
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        $this->db->execute('DELETE FROM spa_service_prices WHERE service_id = ?', [$id]);
        $this->db->execute('DELETE FROM spa_services WHERE id = ?', [$id]);
        echo json_encode(['success' => true]);
    }

    public function saveMeetingVenue() {
        AuthHelper::requireAuth();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        $this->ensureSpaMeetingCatalog();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        $name = trim($_POST['name'] ?? '');
        $capacityNote = trim($_POST['capacity_note'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $prices = $_POST['prices'] ?? [];
        if ($name === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Venue name is required']);
            return;
        }
        try {
            if ($id) {
                $this->db->execute(
                    'UPDATE meeting_venues SET name = ?, capacity_note = ?, is_active = ? WHERE id = ?',
                    [$name, $capacityNote !== '' ? $capacityNote : null, $isActive, $id]
                );
            } else {
                $this->db->execute(
                    'INSERT INTO meeting_venues (name, capacity_note, is_active) VALUES (?, ?, ?)',
                    [$name, $capacityNote !== '' ? $capacityNote : null, 1]
                );
                $id = (int) $this->db->lastInsertId();
            }
            if (is_array($prices)) {
                foreach ($prices as $currency => $price) {
                    $currency = strtoupper(trim((string) $currency));
                    if ($currency === '') continue;
                    $this->db->execute(
                        'INSERT INTO meeting_venue_prices (venue_id, currency, price) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE price = VALUES(price)',
                        [$id, $currency, floatval($price)]
                    );
                }
            }
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save meeting venue']);
        }
    }

    public function deleteMeetingVenue() {
        AuthHelper::requireAuth();
        header('Content-Type: application/json');
        if (!AuthHelper::verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid security token']);
            return;
        }
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        $this->db->execute('DELETE FROM meeting_venue_prices WHERE venue_id = ?', [$id]);
        $this->db->execute('DELETE FROM meeting_venues WHERE id = ?', [$id]);
        echo json_encode(['success' => true]);
    }
}


