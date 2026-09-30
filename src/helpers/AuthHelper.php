<?php
/**
 * Authentication Helper
 * 
 * Handles session management, CSRF tokens, and authentication checks.
 */

class AuthHelper {
    /**
     * Start session if not already started
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            
            // Always use "/" as cookie path to ensure the session cookie
            // is sent on all requests regardless of URL path.
            // This avoids mismatches between GET (login form) and POST (login submit).
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => (APP_ENV === 'production'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        self::startSession();
        return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
    }
    
    /**
     * Get current admin user ID
     */
    public static function getAdminId() {
        self::startSession();
        return $_SESSION['admin_id'] ?? null;
    }
    
    /**
     * Get current admin username
     */
    public static function getAdminUsername() {
        self::startSession();
        return $_SESSION['admin_username'] ?? null;
    }
    
    /**
     * Login user
     */
    public static function login($userId, $username) {
        self::startSession();
        $_SESSION['admin_id'] = $userId;
        $_SESSION['admin_username'] = $username;
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        self::startSession();
        session_unset();
        session_destroy();
    }
    
    /**
     * Require authentication (redirect if not authenticated)
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            // Get base path for redirect
            $basePath = defined('APP_URL') && APP_URL !== 'https://yourdomain.com' 
                ? rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/')
                : '';
            if (empty($basePath) || strpos($_SERVER['REQUEST_URI'], '/azzemanhotel_PHP') !== false) {
                $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $basePath = rtrim($scriptPath, '/');
            }
            header('Location: ' . $basePath . '/admin-login');
            exit;
        }
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        self::startSession();
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        self::startSession();
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Get CSRF token input field HTML
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
}

