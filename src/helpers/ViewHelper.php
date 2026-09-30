<?php
/**
 * View Helper
 * 
 * Provides utility functions for rendering views.
 */

class ViewHelper {
    /**
     * Render a view template
     */
    public static function render($view, $data = []) {
        extract($data);
        ob_start();
        include VIEWS_PATH . '/' . $view . '.php';
        return ob_get_clean();
    }
    
    /**
     * Escape HTML output
     */
    public static function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format date
     */
    public static function formatDate($date, $format = 'Y-m-d') {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format($format);
    }
    
    /**
     * Format datetime
     */
    public static function formatDateTime($date, $format = 'Y-m-d H:i:s') {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format($format);
    }
    
    /**
     * Get asset URL
     */
    public static function asset($path) {
        $cleanPath = ltrim($path, '/');
        
        // Build the path segment (relative to root)
        $basePath = '';

        // If APP_URL has a sub-path (e.g. /azzemanhotel_PHP/public), use it as base
        if (defined('APP_URL') && APP_URL !== 'https://yourdomain.com') {
            $urlSubPath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
            if ($urlSubPath && $urlSubPath !== '/') {
                $basePath = $urlSubPath;
            }
        }

        // For XAMPP sub-directory installs, derive from SCRIPT_NAME
        if (empty($basePath) && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/azzemanhotel_PHP') !== false) {
            $basePath = '/azzemanhotel_PHP';
        }

        // If the path already starts with 'assets/', don't prepend it again
        if (strpos($cleanPath, 'assets/') === 0) {
            $assetPath = $basePath . '/' . $cleanPath;
        } else {
            $assetPath = $basePath . '/assets/' . $cleanPath;
        }

        // Return absolute URL if APP_URL is set to a full URL (preserving scheme, host AND port)
        if (defined('APP_URL') && APP_URL !== 'https://yourdomain.com' && filter_var(APP_URL, FILTER_VALIDATE_URL)) {
            $urlParts = parse_url(APP_URL);
            $scheme = isset($urlParts['scheme']) ? $urlParts['scheme'] : 'https';
            $host   = isset($urlParts['host'])   ? $urlParts['host']   : '';
            $port   = isset($urlParts['port'])   ? ':' . $urlParts['port'] : '';
            if ($host) {
                return $scheme . '://' . $host . $port . $assetPath;
            }
        }

        return $assetPath;
    }
    
    /**
     * Get site image URL
     */
    public static function siteImage($key, $default = '') {
        try {
            $db = Database::getInstance();
            $image = $db->queryOne(
                'SELECT src FROM site_images WHERE `key` = ?',
                [$key]
            );
            return $image ? $image['src'] : $default;
        } catch (Exception $e) {
            // If database not available, return default
            error_log('ViewHelper::siteImage error: ' . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Include partial view
     */
    public static function partial($partial, $data = []) {
        extract($data);
        include VIEWS_PATH . '/partials/' . $partial . '.php';
    }
}

