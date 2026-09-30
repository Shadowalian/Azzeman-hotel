<?php 
$title = $title ?? 'Gallery Management - Azzeman Hotel';
$gallery_items = $gallery_items ?? [];
$categories = $categories ?? [];
$csrf_token = $csrf_token ?? '';

$basePath = $basePath ?? '';
if ($basePath === '') {
    $basePath = defined('APP_URL') && APP_URL !== 'https://yourdomain.com'
        ? rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/')
        : '';
    if (empty($basePath)) {
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $basePath = rtrim($scriptPath, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ViewHelper::e($title) ?></title>
    <meta name="base-path" content="<?= ViewHelper::e($basePath) ?>">

    <link rel="icon" type="image/png" href="<?= ViewHelper::asset('images/logo.png') ?>" sizes="32x32">
    <link rel="icon" type="image/png" href="<?= ViewHelper::asset('images/logo.png') ?>" sizes="16x16">
    <link rel="apple-touch-icon" href="<?= ViewHelper::asset('images/logo.png') ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-green': '#0E8040',
                        'brand-gold': '#7A6960',
                    },
                },
            },
        }
    </script>

    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="<?= ViewHelper::asset('css/admin.css') ?>" rel="stylesheet">
    <script>
        window.APP_BASE_PATH = <?= json_encode($basePath) ?>;
    </script>
</head>
<body class="min-h-screen bg-stone-100">
    <header class="bg-brand-green text-white shadow-sm">
        <div class="container mx-auto px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i data-lucide="image" class="w-6 h-6"></i>
                    Gallery Management
                </h1>
                <p class="text-sm text-white/80">Curate immersive stories for the public gallery experience.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= ViewHelper::e($basePath) ?>/admin?tab=gallery" class="hidden md:inline-flex items-center bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg border border-white/20 transition">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i>
                    Back to Dashboard
                </a>
                <a href="<?= ViewHelper::e($basePath) ?>/admin/logout" class="bg-red-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-600 flex items-center">
                    <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-8 space-y-6">
        <?php 
        $basePath = $basePath;
        $gallery_items = $gallery_items;
        $categories = $categories;
        $rooms = $rooms ?? [];
        $room_images = $room_images ?? [];
        $csrf_token = $csrf_token;
        include __DIR__ . '/partials/gallery-manager.php';
        ?>
    </main>

    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        const csrfToken = '<?= ViewHelper::e($csrf_token) ?>';
        const csrfTokenName = '<?= CSRF_TOKEN_NAME ?>';

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast toast-${type} bg-${type === 'success' ? 'green' : 'red'}-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center justify-between min-w-[300px] animate-fade-in`;
            toast.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            `;
            container.appendChild(toast);
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    <script src="<?= ViewHelper::asset('js/admin.js') ?>"></script>
</body>
</html>

