<?php
/**
 * Rate Limiter
 * 
 * Simple IP-based rate limiting for API endpoints.
 */

class RateLimiter {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Check if request is within rate limit
     */
    public function checkLimit($endpoint, $maxRequests, $windowSeconds) {
        $ip = $this->getClientIp();
        $key = $endpoint . ':' . $ip;
        $now = time();
        
        // Clean old entries (simple cleanup)
        $this->cleanup($now - $windowSeconds);
        
        // Count requests in window
        $count = $this->getRequestCount($key, $now - $windowSeconds);
        
        if ($count >= $maxRequests) {
            return false;
        }
        
        // Record this request
        $this->recordRequest($key, $now);
        
        return true;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get request count for key in time window
     */
    private function getRequestCount($key, $since) {
        // Simple file-based rate limiting (can be replaced with Redis/DB)
        $file = LOGS_PATH . '/ratelimit_' . md5($key) . '.json';
        
        if (!file_exists($file)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            return 0;
        }
        
        $count = 0;
        foreach ($data as $timestamp) {
            if ($timestamp > $since) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Record a request
     */
    private function recordRequest($key, $timestamp) {
        $file = LOGS_PATH . '/ratelimit_' . md5($key) . '.json';
        
        $data = [];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
        }
        
        $data[] = $timestamp;
        
        // Keep only last 100 entries
        if (count($data) > 100) {
            $data = array_slice($data, -100);
        }
        
        file_put_contents($file, json_encode($data));
    }
    
    /**
     * Cleanup old entries
     */
    private function cleanup($before) {
        $files = glob(LOGS_PATH . '/ratelimit_*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $data = array_filter($data, function($timestamp) use ($before) {
                    return $timestamp > $before;
                });
                if (empty($data)) {
                    unlink($file);
                } else {
                    file_put_contents($file, json_encode(array_values($data)));
                }
            }
        }
    }
}

