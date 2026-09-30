<?php
/**
 * Azzeman Hotel - Front Controller
 * 
 * Simple routing system for handling all requests.
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Define gallery upload constants if not already defined in config.php
if (!defined('GALLERY_UPLOAD_DIR')) {
    define('GALLERY_UPLOAD_DIR', __DIR__ . '/assets/uploads/gallery/');
}
if (!defined('GALLERY_UPLOAD_URL')) {
    define('GALLERY_UPLOAD_URL', 'uploads/gallery/');
}
if (!defined('ROOM_UPLOAD_DIR')) {
    define('ROOM_UPLOAD_DIR', __DIR__ . '/assets/uploads/rooms/');
}
if (!defined('ROOM_UPLOAD_URL')) {
    define('ROOM_UPLOAD_URL', 'uploads/rooms/');
}

// Load core classes
require_once __DIR__ . '/src/models/Database.php';
require_once __DIR__ . '/src/helpers/AuthHelper.php';
require_once __DIR__ . '/src/helpers/ViewHelper.php';
require_once __DIR__ . '/src/helpers/RateLimiter.php';

// Load controllers
require_once __DIR__ . '/src/controllers/HomeController.php';
require_once __DIR__ . '/src/controllers/AdminController.php';
require_once __DIR__ . '/src/controllers/ApiController.php';
require_once __DIR__ . '/src/controllers/BookingController.php';

// Start session
AuthHelper::startSession();

// Auto-migrate database schema to prevent cPanel out-of-sync database errors
try {
    $db = Database::getInstance();
    
    // 1. Ensure gallery_categories table exists
    $db->execute("CREATE TABLE IF NOT EXISTS gallery_categories (
        id VARCHAR(100) NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_gallery_categories_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Ensure rooms columns exist
    $roomsCols = $db->query("SHOW COLUMNS FROM rooms");
    $existingRoomsCols = array_column($roomsCols, 'Field');
    $roomsColumnsToAdd = [
        'price_per_night'        => 'DECIMAL(10, 2) NOT NULL DEFAULT 100.00',
        'currency'               => "VARCHAR(10) NOT NULL DEFAULT 'USD'",
        'max_guests'             => 'INT(11) NOT NULL DEFAULT 2',
        'price_per_adult'        => 'DECIMAL(10, 2) NOT NULL DEFAULT 100.00',
        'price_per_child'        => 'DECIMAL(10, 2) NOT NULL DEFAULT 50.00',
        'price_additional_adult' => 'DECIMAL(10, 2) NOT NULL DEFAULT 50.00'
    ];
    foreach ($roomsColumnsToAdd as $col => $definition) {
        if (!in_array($col, $existingRoomsCols)) {
            $db->execute("ALTER TABLE rooms ADD COLUMN $col $definition");
            if ($col === 'price_per_adult') {
                $db->execute("UPDATE rooms SET price_per_adult = price_per_night");
            }
            if ($col === 'price_per_child') {
                $db->execute("UPDATE rooms SET price_per_child = ROUND(price_per_night * 0.4)");
            }
            if ($col === 'price_additional_adult') {
                $db->execute("UPDATE rooms SET price_additional_adult = ROUND(price_per_night * 0.5)");
            }
        }
    }

    // Ensure proper max guests occupancy
    $db->execute("UPDATE rooms SET max_guests = 4 WHERE type = 'Executive Suite' AND max_guests = 2");
    $db->execute("UPDATE rooms SET max_guests = 3 WHERE type = 'Junior suite' AND max_guests = 2");

    // Room booking images (many per room type)
    $db->execute("CREATE TABLE IF NOT EXISTS `room_images` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `room_id` INT(11) NOT NULL,
        `image_path` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_room_images_room_id` (`room_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 5. Ensure booking_rooms columns exist
    $bookingCols = $db->query("SHOW COLUMNS FROM booking_rooms");
    $existingBookingCols = array_column($bookingCols, 'Field');
    $bookingColumnsToAdd = [
        'price_per_night'        => 'DECIMAL(10, 2) DEFAULT NULL',
        'price_per_child'        => 'DECIMAL(10, 2) DEFAULT NULL',
        'price_additional_adult' => 'DECIMAL(10, 2) DEFAULT NULL',
        'currency'               => 'VARCHAR(10) DEFAULT NULL',
        'total_price'            => 'DECIMAL(10, 2) DEFAULT NULL',
        'rooms_count'            => 'INT(11) NOT NULL DEFAULT 1',
        'adults_count'           => 'INT(11) NOT NULL DEFAULT 1',
        'children_count'         => 'INT(11) NOT NULL DEFAULT 0',
        'room_details'           => 'TEXT DEFAULT NULL',
    ];
    foreach ($bookingColumnsToAdd as $col => $definition) {
        if (!in_array($col, $existingBookingCols)) {
            $db->execute("ALTER TABLE booking_rooms ADD COLUMN $col $definition");
        }
    }

    // 6. Ensure currencies and room_currency_prices tables exist
    $db->execute("CREATE TABLE IF NOT EXISTS `currencies` (
        `code` VARCHAR(10) NOT NULL,
        `symbol` VARCHAR(10) NOT NULL DEFAULT '',
        `name` VARCHAR(50) NOT NULL DEFAULT '',
        `is_default` TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $currCount = $db->queryOne("SELECT COUNT(*) as cnt FROM currencies");
    if (intval($currCount['cnt'] ?? 0) === 0) {
        $db->execute("INSERT INTO currencies (code, symbol, name, is_default) VALUES
            ('USD', '\$', 'US Dollar', 1),
            ('ETB', 'Br', 'Ethiopian Birr', 0),
            ('EUR', '€', 'Euro', 0),
            ('GBP', '£', 'British Pound', 0)
        ");
    }
    $db->execute("CREATE TABLE IF NOT EXISTS `room_currency_prices` (
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

    // 7. Auto-seed room_currency_prices for existing rooms if missing
    $allRooms = $db->query("SELECT id, price_per_adult, price_per_child, price_additional_adult FROM rooms");
    $allCurrencies = $db->query("SELECT code FROM currencies");
    foreach ($allRooms as $rm) {
        foreach ($allCurrencies as $cur) {
            $existing = $db->queryOne(
                "SELECT id FROM room_currency_prices WHERE room_id = ? AND currency = ?",
                [$rm['id'], $cur['code']]
            );
            if (!$existing) {
                $db->execute(
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
    error_log('Database auto-migration in front controller failed: ' . $e->getMessage());
}


// Get request path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = str_replace('\\', '/', dirname($scriptName));

// Remove base path from request URI
// Handle different scenarios:
// 1. If script is in subdirectory: /azzemanhotel_PHP/index.php -> /azzemanhotel_PHP
// 2. If request includes the script directory, remove it
// 3. Otherwise use request URI as-is

$path = $requestUri;

// If script directory is not root and request starts with it, remove it
if ($scriptDir !== '/' && $scriptDir !== '.' && strpos($requestUri, $scriptDir) === 0) {
    $path = substr($requestUri, strlen($scriptDir));
}

// Also handle case where request URI includes /azzemanhotel_PHP/booking/room
// and we need to extract just /booking/room
if (strpos($path, '/azzemanhotel_PHP') === 0) {
    $path = substr($path, strlen('/azzemanhotel_PHP'));
}

// Normalize path
$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// DEBUG: Show requested path
// if (strpos($path, '/api/') === false && strpos($path, '/admin') === false) {
//     echo '<div style="background:purple; color:white; padding:10px; text-align:center; font-weight:bold; z-index:10000; position:relative;">DEBUG: Request Path: ' . htmlspecialchars($path) . '</div>';
// }

// Debug: Log path information for booking requests
if (strpos($requestUri, '/booking') !== false) {
    error_log("Booking Request - REQUEST_URI: " . $requestUri);
    error_log("Booking Request - SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
    error_log("Booking Request - Script Name (dirname): " . $scriptName);
    error_log("Booking Request - Parsed Path: " . $path);
    error_log("Booking Request - Method: " . $method);
}

// Simple routing
try {
    // API routes
    if (strpos($path, '/api/') === 0) {
        $apiController = new ApiController();
        
        if ($path === '/api/chatbot' && $method === 'POST') {
            $apiController->chatbot();
        } elseif ($path === '/api/health' && $method === 'GET') {
            $apiController->health();
        } elseif ($path === '/api/health/db' && $method === 'GET') {
            $apiController->healthDb();
        } elseif ($path === '/api/bootstrap' && $method === 'GET') {
            $apiController->bootstrap();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }
    
    // Admin routes
    if (strpos($path, '/admin') === 0 || $path === '/admin-login') {
        $adminController = new AdminController();
        
        if ($path === '/admin-login' || $path === '/admin/login') {
            if ($method === 'GET') {
                $adminController->loginForm();
            } elseif ($method === 'POST') {
                $adminController->login();
            }
        } elseif ($path === '/admin/logout') {
            $adminController->logout();
        } elseif ($path === '/admin' || $path === '/admin/dashboard') {
            if (!AuthHelper::isAuthenticated()) {
                // Get base path for redirect
                $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $basePath = rtrim($basePath, '/');
                header('Location: ' . $basePath . '/admin-login');
                exit;
            }
            $adminController->dashboard();
        } elseif ($path === '/admin/bookings' && $method === 'GET') {
            if (!AuthHelper::isAuthenticated()) {
                $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $basePath = rtrim($basePath, '/');
                header('Location: ' . $basePath . '/admin-login');
                exit;
            }
            $adminController->bookings();
        } elseif ($path === '/admin/bookings/update' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->updateBooking();
        } elseif ($path === '/admin/bookings/delete' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->deleteBooking();
        } elseif ($path === '/admin/gallery' && $method === 'GET') {
            if (!AuthHelper::isAuthenticated()) {
                $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $basePath = rtrim($basePath, '/');
                header('Location: ' . $basePath . '/admin-login');
                exit;
            }
            $adminController->gallery();
        } elseif ($path === '/admin/gallery/upload' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->uploadGalleryImage();
        } elseif ($path === '/admin/gallery/delete' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->deleteGalleryImage();
        } elseif ($path === '/admin/room-images/update' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->updateRoomImage();
        } elseif ($path === '/admin/room-images/delete' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->deleteRoomImage();
        } elseif ($path === '/admin/site-images' && $method === 'GET') {
            if (!AuthHelper::isAuthenticated()) {
                $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $basePath = rtrim($basePath, '/');
                header('Location: ' . $basePath . '/admin-login');
                exit;
            }
            $adminController->siteImages();
        } elseif ($path === '/admin/site-images/update' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->updateSiteImage();
        } elseif ($path === '/admin/room-pricing/update' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->updateRoomPricing();
        } elseif ($path === '/admin/room-pricing/create' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->createRoomPricing();
        } elseif ($path === '/admin/room-pricing/delete' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->deleteRoomPricing();
        } elseif ($path === '/admin/spa-pricing/save' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
            $adminController->saveSpaService();
        } elseif ($path === '/admin/spa-pricing/delete' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
            $adminController->deleteSpaService();
        } elseif ($path === '/admin/meeting-pricing/save' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
            $adminController->saveMeetingVenue();
        } elseif ($path === '/admin/meeting-pricing/delete' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
            $adminController->deleteMeetingVenue();
        } elseif ($path === '/admin/currencies/create' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
            $adminController->createCurrency();
        } elseif ($path === '/admin/currencies/delete' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
            $adminController->deleteCurrency();
        } elseif ($path === '/admin/currencies/set-default' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
            $adminController->setDefaultCurrency();
        } elseif ($path === '/admin/virtual-tour' && $method === 'GET') {
            if (!AuthHelper::isAuthenticated()) {
                $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $basePath = rtrim($basePath, '/');
                header('Location: ' . $basePath . '/admin-login');
                exit;
            }
            $adminController->virtualTour();
        } elseif ($path === '/admin/virtual-tour/update' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->updateVirtualTour();
        } elseif ($path === '/admin/profile' && $method === 'GET') {
            if (!AuthHelper::isAuthenticated()) {
                $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $basePath = rtrim($basePath, '/');
                header('Location: ' . $basePath . '/admin-login');
                exit;
            }
            $adminController->profile();
        } elseif ($path === '/admin/profile/update' && $method === 'POST') {
            if (!AuthHelper::isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $adminController->updateProfile();
        } else {
            http_response_code(404);
            echo 'Page not found';
        }
        exit;
    }
    
    // Booking routes
    if (strpos($path, '/booking') === 0) {
        error_log("Booking route matched - Path: {$path}, Method: {$method}");
        $bookingController = new BookingController();
        
        if ($path === '/booking/room' && $method === 'POST') {
            error_log("Calling bookRoom()");
            $bookingController->bookRoom();
        } elseif ($path === '/booking/spa' && $method === 'POST') {
            error_log("Calling bookSpa()");
            $bookingController->bookSpa();
        } elseif ($path === '/booking/meeting' && $method === 'POST') {
            error_log("Calling bookMeeting()");
            $bookingController->bookMeeting();
        } else {
            error_log("Booking route not matched - Path: {$path}, Method: {$method}");
            http_response_code(404);
            echo json_encode(['error' => 'Not found', 'path' => $path, 'method' => $method]);
        }
        exit;
    }
    
    // Public routes
    $homeController = new HomeController();
    
    if ($path === '/' || $path === '/home') {
        $homeController->index();
    } elseif ($path === '/gallery') {
        $homeController->gallery();
    } elseif (preg_match('#^/gallery/([^/]+)$#', $path, $matches)) {
        $homeController->galleryCategory($matches[1]);
    } else {
        http_response_code(404);
        echo 'Page not found';
    }
    
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Show detailed error in development mode
    if (defined('APP_DEBUG') && APP_DEBUG) {
        http_response_code(500);
        echo '<h1>Error</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo 'An error occurred. Please try again later.';
        // In production, check error log: ' . LOGS_PATH . '/php_errors.log';
    }
}

