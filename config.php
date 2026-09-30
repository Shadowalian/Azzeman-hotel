<?php
/**
 * Azzeman Hotel - Configuration (env-driven)
 *
 * Local: copy .env.example → .env
 * Vercel: set the same keys in Project → Settings → Environment Variables
 * Brevo/SMTP can stay empty until you connect email later.
 */

// Lightweight .env loader (no Composer dependency)
(function () {
    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile) || !is_readable($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }
        $value = trim($value, "\"'");
        if (getenv($name) === false) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
        }
    }
})();

if (!function_exists('azzeman_env')) {
    function azzeman_env(string $key, $default = '') {
        $v = getenv($key);
        if ($v === false || $v === '') {
            if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
                return $_ENV[$key];
            }
            return $default;
        }
        return $v;
    }
}

$isLocal = false;
if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    if (
        $host === 'localhost'
        || strpos($host, 'localhost:') === 0
        || $host === '127.0.0.1'
        || strpos($host, '192.168.') === 0
        || strpos($host, '.vercel.app') !== false && azzeman_env('APP_ENV', '') === 'development'
    ) {
        $isLocal = (
            $host === 'localhost'
            || strpos($host, 'localhost:') === 0
            || $host === '127.0.0.1'
            || strpos($host, '192.168.') === 0
        );
    }
} elseif (PHP_SAPI === 'cli') {
    $isLocal = true;
}

$defaultAppUrl = $isLocal ? 'http://localhost:8000' : (azzeman_env('VERCEL_URL') ? ('https://' . azzeman_env('VERCEL_URL')) : 'https://azzemanhotel.com');
$appUrl = rtrim(azzeman_env('APP_URL', $defaultAppUrl), '/');
$emailBase = rtrim(azzeman_env('EMAIL_BASE_URL', $appUrl . '/'), '/') . '/';

define('DB_HOST', azzeman_env('DB_HOST', $isLocal ? 'localhost' : ''));
define('DB_NAME', azzeman_env('DB_NAME', $isLocal ? 'azzemanhotel_sinq' : ''));
define('DB_USER', azzeman_env('DB_USER', $isLocal ? 'root' : ''));
define('DB_PASS', azzeman_env('DB_PASS', $isLocal ? '' : ''));
define('DB_CHARSET', azzeman_env('DB_CHARSET', 'utf8mb4'));

define('APP_NAME', azzeman_env('APP_NAME', 'Azzeman Hotel'));
define('APP_URL', $appUrl);
define('APP_ENV', azzeman_env('APP_ENV', $isLocal ? 'development' : 'production'));
define('APP_DEBUG', filter_var(azzeman_env('APP_DEBUG', $isLocal ? 'true' : 'false'), FILTER_VALIDATE_BOOLEAN));
define('EMAIL_BASE_URL', $emailBase);
define('ADMIN_DASHBOARD_URL', EMAIL_BASE_URL . 'admin');

define('SESSION_LIFETIME', 3600 * 24 * 7);
define('SESSION_NAME', 'azzeman_hotel_session');
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
define('UPLOAD_MAX_SIZE', 50 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('GALLERY_UPLOAD_DIR', UPLOAD_DIR . 'gallery/');
define('GALLERY_UPLOAD_URL', 'uploads/gallery/');
define('ROOM_UPLOAD_DIR', UPLOAD_DIR . 'rooms/');
define('ROOM_UPLOAD_URL', 'uploads/rooms/');

define('GEMINI_API_KEY', azzeman_env('GEMINI_API_KEY', ''));
define('GEMINI_MODEL', azzeman_env('GEMINI_MODEL', 'gemini-2.0-flash'));
define('GEMINI_IMAGE_MODEL', azzeman_env('GEMINI_IMAGE_MODEL', 'imagen-4.0-generate-001'));

define('OPENROUTER_API_KEY', azzeman_env('OPENROUTER_API_KEY', ''));
define('OPENROUTER_MODEL', azzeman_env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-70b-instruct:free'));

define('SMTP_HOST', azzeman_env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int) azzeman_env('SMTP_PORT', '587'));
define('SMTP_USER', azzeman_env('SMTP_USER', ''));
define('SMTP_PASS', azzeman_env('SMTP_PASS', ''));
define('SMTP_FROM_EMAIL', azzeman_env('SMTP_FROM_EMAIL', ''));
define('SMTP_FROM_NAME', azzeman_env('SMTP_FROM_NAME', 'Azzeman Hotel'));

define('SMTP2_HOST', azzeman_env('SMTP2_HOST', ''));
define('SMTP2_PORT', (int) azzeman_env('SMTP2_PORT', '465'));
define('SMTP2_USER', azzeman_env('SMTP2_USER', ''));
define('SMTP2_PASS', azzeman_env('SMTP2_PASS', ''));
define('SMTP2_FROM_EMAIL', azzeman_env('SMTP2_FROM_EMAIL', ''));
define('SMTP2_FROM_NAME', azzeman_env('SMTP2_FROM_NAME', 'Azzeman Hotel'));

define('ADMIN_EMAIL', azzeman_env('ADMIN_EMAIL', 'reservation@azzemanhotel.com'));
define('ADMIN_EMAILS', azzeman_env('ADMIN_EMAILS', 'reservation@azzemanhotel.com,admin@azzemanhotel.com'));

define('CHATBOT_RATE_LIMIT', 10);
define('CHATBOT_RATE_WINDOW', 60);

// Leave empty until Brevo is connected in Vercel / .env
define('BREVO_API_KEY', azzeman_env('BREVO_API_KEY', ''));
define('BREVO_SENDER_EMAIL', azzeman_env('BREVO_SENDER_EMAIL', 'reservation@azzemanhotel.com'));
define('BREVO_SENDER_NAME', azzeman_env('BREVO_SENDER_NAME', 'Azzeman Hotel'));

define('BASE_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/cpanel-public');
define('VIEWS_PATH', BASE_PATH . '/src/views');
define('LOGS_PATH', BASE_PATH . '/logs');

date_default_timezone_set('Africa/Addis_Ababa');

if (APP_DEBUG) {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
    ini_set('display_errors', 0);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

ini_set('log_errors', 1);
if (!is_dir(LOGS_PATH)) {
    @mkdir(LOGS_PATH, 0755, true);
}
ini_set('error_log', LOGS_PATH . '/php_errors.log');
