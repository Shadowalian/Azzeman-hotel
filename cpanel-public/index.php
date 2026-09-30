<?php
/**
 * Azzeman Hotel - Front Controller
 * 
 * Simple routing system for handling all requests.
 */

// Load configuration
require_once __DIR__ . '/../config.php';

// Load core classes
require_once __DIR__ . '/../src/models/Database.php';
require_once __DIR__ . '/../src/helpers/AuthHelper.php';
require_once __DIR__ . '/../src/helpers/ViewHelper.php';
require_once __DIR__ . '/../src/helpers/RateLimiter.php';

// Load controllers
require_once __DIR__ . '/../src/controllers/HomeController.php';
require_once __DIR__ . '/../src/controllers/AdminController.php';
require_once __DIR__ . '/../src/controllers/ApiController.php';
require_once __DIR__ . '/../src/controllers/BookingController.php';

// Start session
AuthHelper::startSession();

// Get request path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

// Remove base path from request URI
// For example: /azzemanhotel_PHP/public/ -> /
if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
    $path = substr($requestUri, strlen($scriptName));
} else {
    $path = $requestUri;
}

// Normalize path
$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Debug: Uncomment to see the path being used
// if (defined('APP_DEBUG') && APP_DEBUG) {
//     error_log("Request URI: " . $requestUri);
//     error_log("Script Name: " . $scriptName);
//     error_log("Parsed Path: " . $path);
// }

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
    if (strpos($path, '/admin') === 0) {
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
            $adminController->dashboard();
        } elseif ($path === '/admin/bookings' && $method === 'GET') {
            $adminController->bookings();
        } elseif ($path === '/admin/bookings/update' && $method === 'POST') {
            $adminController->updateBooking();
        } elseif ($path === '/admin/bookings/delete' && $method === 'POST') {
            $adminController->deleteBooking();
        } elseif ($path === '/admin/gallery' && $method === 'GET') {
            $adminController->gallery();
        } elseif ($path === '/admin/gallery/upload' && $method === 'POST') {
            $adminController->uploadGalleryImage();
        } elseif ($path === '/admin/gallery/update' && $method === 'POST') {
            $adminController->updateGalleryImage();
        } elseif ($path === '/admin/gallery/delete' && $method === 'POST') {
            $adminController->deleteGalleryImage();
        } elseif ($path === '/admin/room-images/update' && $method === 'POST') {
            $adminController->updateRoomImage();
        } elseif ($path === '/admin/room-images/delete' && $method === 'POST') {
            $adminController->deleteRoomImage();
        } elseif ($path === '/admin/gallery/categories/create' && $method === 'POST') {
            $adminController->createGalleryCategory();
        } elseif ($path === '/admin/gallery/categories/update' && $method === 'POST') {
            $adminController->updateGalleryCategory();
        } elseif ($path === '/admin/gallery/categories/delete' && $method === 'POST') {
            $adminController->deleteGalleryCategory();
        } elseif ($path === '/admin/site-images' && $method === 'GET') {
            $adminController->siteImages();
        } elseif ($path === '/admin/site-images/update' && $method === 'POST') {
            $adminController->updateSiteImage();
        } elseif ($path === '/admin/room-pricing/update' && $method === 'POST') {
            $adminController->updateRoomPricing();
        } elseif ($path === '/admin/spa-pricing/save' && $method === 'POST') {
            $adminController->saveSpaService();
        } elseif ($path === '/admin/spa-pricing/delete' && $method === 'POST') {
            $adminController->deleteSpaService();
        } elseif ($path === '/admin/meeting-pricing/save' && $method === 'POST') {
            $adminController->saveMeetingVenue();
        } elseif ($path === '/admin/meeting-pricing/delete' && $method === 'POST') {
            $adminController->deleteMeetingVenue();
        } elseif ($path === '/admin/virtual-tour' && $method === 'GET') {
            $adminController->virtualTour();
        } elseif ($path === '/admin/virtual-tour/update' && $method === 'POST') {
            $adminController->updateVirtualTour();
        } elseif ($path === '/admin/profile' && $method === 'GET') {
            $adminController->profile();
        } elseif ($path === '/admin/profile/update' && $method === 'POST') {
            $adminController->updateProfile();
        } else {
            http_response_code(404);
            echo 'Page not found';
        }
        exit;
    }
    
    // Booking routes
    if (strpos($path, '/booking') === 0) {
        $bookingController = new BookingController();
        
        if ($path === '/booking/room' && $method === 'POST') {
            $bookingController->bookRoom();
        } elseif ($path === '/booking/spa' && $method === 'POST') {
            $bookingController->bookSpa();
        } elseif ($path === '/booking/meeting' && $method === 'POST') {
            $bookingController->bookMeeting();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
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

