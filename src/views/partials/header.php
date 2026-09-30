<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ViewHelper::e($title ?? 'Azzeman Hotel') ?></title>
    <meta name="description" content="Four-star hotel in Bole, Addis Ababa, 2 km from Bole International Airport. Airport shuttle, spa, fitness centre, restaurant, and meeting halls. Book direct.">
    <meta name="theme-color" content="#0E8040">
    
    <?php
    // Calculate base path for JavaScript
    // This needs to match the actual URL structure
    $jsBasePath = '';
    
    // Get the request URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // If we're in a subdirectory, extract it
    if (strpos($requestUri, '/azzemanhotel_PHP') !== false) {
        // Extract the base path from REQUEST_URI
        $parts = explode('/azzemanhotel_PHP', $requestUri);
        if (count($parts) > 0) {
            $jsBasePath = '/azzemanhotel_PHP';
        }
    } else {
        // Try from SCRIPT_NAME (normalize backslashes on Windows)
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '') {
            $jsBasePath = rtrim($scriptDir, '/');
        }
        
        // If still empty, try from APP_URL
        if (empty($jsBasePath) && defined('APP_URL') && APP_URL !== 'https://yourdomain.com') {
            $urlPath = parse_url(APP_URL, PHP_URL_PATH);
            if ($urlPath && $urlPath !== '/') {
                $jsBasePath = rtrim($urlPath, '/');
            }
        }
    }
    
    // Ensure it's not empty string if we're at root
    if (empty($jsBasePath)) {
        $jsBasePath = '';
    }
    ?>
    <meta name="base-path" content="<?= ViewHelper::e($jsBasePath) ?>">
    <script>
        window.APP_BASE_PATH = <?= json_encode($jsBasePath) ?>;
        console.log('APP_BASE_PATH set to:', window.APP_BASE_PATH);
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ViewHelper::asset('images/logo.png') ?>" sizes="32x32">
    <link rel="icon" type="image/png" href="<?= ViewHelper::asset('images/logo.png') ?>" sizes="16x16">
    <link rel="apple-touch-icon" href="<?= ViewHelper::asset('images/logo.png') ?>">
    <link rel="shortcut icon" href="<?= ViewHelper::asset('images/logo.png') ?>" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Instrument+Sans:wght@400;500;600;700&family=Noto+Sans+Ethiopic:wght@400;600&family=Caveat:wght@500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-green': '#0E8040',
                        'brand-gold': '#7A6960',
                    },
                    fontFamily: {
                        sans: ['"Instrument Sans"', '"Helvetica Neue"', 'Helvetica', 'Arial', 'sans-serif'],
                        body: ['"Instrument Sans"', '"Georgia"', 'serif'],
                    },
                },
            },
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Custom CSS -->
    <link href="<?= ViewHelper::asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= ViewHelper::asset('css/booking.css') ?>" rel="stylesheet">
    <link href="<?= ViewHelper::asset('css/azzeman-landing.css') ?>?v=11" rel="stylesheet">
    
    <!-- JavaScript Files -->
    <!-- Ensure APP_BASE_PATH is available before scripts load -->
    <script>
        // Verify APP_BASE_PATH is set
        if (typeof window.APP_BASE_PATH === 'undefined') {
            console.warn('APP_BASE_PATH not set! Trying to get from meta tag...');
            const metaBasePath = document.querySelector('meta[name="base-path"]');
            if (metaBasePath) {
                window.APP_BASE_PATH = metaBasePath.getAttribute('content') || '';
                console.log('APP_BASE_PATH set from meta tag:', window.APP_BASE_PATH);
            }
        }
    </script>
    <script src="<?= ViewHelper::asset('js/navbar.js') ?>" defer></script>
    <script src="<?= ViewHelper::asset('js/chatbot.js') ?>" defer></script>
    <script src="<?= ViewHelper::asset('js/booking.js') ?>" defer></script>
    <script src="<?= ViewHelper::asset('js/virtual-tour.js') ?>" defer></script>
    
</head>
<body class="azzeman-landing" style="margin: 0; padding: 0;">

