<?php 
// Admin Dashboard - Matching original React design
// Ensure all variables are defined with defaults to prevent undefined variable warnings
$tab = isset($tab) ? $tab : 'rooms';
$room_bookings = isset($room_bookings) ? $room_bookings : [];
$spa_bookings = isset($spa_bookings) ? $spa_bookings : [];
$meeting_bookings = isset($meeting_bookings) ? $meeting_bookings : [];
$gallery_categories = isset($gallery_categories) ? $gallery_categories : [];
$gallery_images = isset($gallery_images) ? $gallery_images : [];
$site_images = isset($site_images) ? $site_images : [];
$virtual_tour = isset($virtual_tour) ? $virtual_tour : ['image_url' => '', 'hotspots' => []];
$user = isset($user) ? $user : [];
$csrf_token = isset($csrf_token) ? $csrf_token : '';
$title = isset($title) ? $title : 'Admin Dashboard - Azzeman Hotel';

// Get base path for links
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
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ViewHelper::asset('images/logo.png') ?>" sizes="32x32">
    <link rel="icon" type="image/png" href="<?= ViewHelper::asset('images/logo.png') ?>" sizes="16x16">
    <link rel="apple-touch-icon" href="<?= ViewHelper::asset('images/logo.png') ?>">
    <link rel="shortcut icon" href="<?= ViewHelper::asset('images/logo.png') ?>" type="image/png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <link href="<?= ViewHelper::asset('css/admin.css') ?>?v=2" rel="stylesheet">
    <script>
        window.APP_BASE_PATH = <?= json_encode($basePath) ?>;
    </script>
</head>
<body class="admin-azzeman">
    <header class="admin-top">
        <div class="admin-top-in">
            <a class="admin-brand" href="<?= ViewHelper::e($basePath) ?>/admin">
                <img src="<?= ViewHelper::asset('images/logo.png') ?>" alt="">
                <span><b>Azzeman</b><small>Admin · Addis Ababa</small></span>
            </a>
            <div class="admin-top-actions">
                <a class="admin-icon-btn" href="<?= ViewHelper::e($basePath) ?>/" title="View site" target="_blank" rel="noopener">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                </a>
                <button type="button" onclick="refreshData()" id="refreshBtn" class="admin-icon-btn" title="Refresh data">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
                <a href="<?= ViewHelper::e($basePath) ?>/admin/logout" class="admin-logout">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="admin-shell">
        <div class="admin-page-head">
            <div class="eyebrow">Control centre</div>
            <h1>Hotel dashboard</h1>
            <p>Manage bookings, pricing, gallery, and every image that powers the public site.</p>
        </div>

        <?php
        $adminTabs = [
            'rooms' => ['icon' => 'bed', 'label' => 'Room Bookings', 'count' => count($room_bookings)],
            'pricing' => ['icon' => 'dollar-sign', 'label' => 'Room Pricing'],
            'spa-pricing' => ['icon' => 'sparkles', 'label' => 'Spa Pricing'],
            'meeting-pricing' => ['icon' => 'wallet', 'label' => 'Meeting Pricing'],
            'currencies' => ['icon' => 'coins', 'label' => 'Currencies'],
            'spa' => ['icon' => 'heart-pulse', 'label' => 'Spa Bookings', 'count' => count($spa_bookings)],
            'meetings' => ['icon' => 'presentation', 'label' => 'Meetings & Events', 'count' => count($meeting_bookings)],
            'gallery' => ['icon' => 'camera', 'label' => 'Gallery'],
            'site-images' => ['icon' => 'image', 'label' => 'Site Images'],
            'virtual-tour' => ['icon' => 'globe', 'label' => 'Virtual Tour'],
            'profile' => ['icon' => 'user', 'label' => 'Profile'],
        ];
        ?>
        <nav class="admin-tabs" aria-label="Tabs">
            <?php foreach ($adminTabs as $tabKey => $meta): ?>
                <a href="<?= ViewHelper::e($basePath) ?>/admin?tab=<?= ViewHelper::e($tabKey) ?>" class="<?= $tab === $tabKey ? 'is-active' : '' ?>">
                    <i data-lucide="<?= ViewHelper::e($meta['icon']) ?>" class="w-4 h-4"></i>
                    <span>
                        <?= ViewHelper::e($meta['label']) ?>
                        <?php if (isset($meta['count'])): ?>
                            <?php
                            $countId = $tabKey === 'rooms' ? 'roomCount' : ($tabKey === 'spa' ? 'spaCount' : ($tabKey === 'meetings' ? 'meetingCount' : ''));
                            ?>
                            (<span<?= $countId ? ' id="' . ViewHelper::e($countId) . '"' : '' ?>><?= (int) $meta['count'] ?></span>)
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Search Bar (for bookings tabs) -->
        <?php if (in_array($tab, ['rooms', 'spa', 'meetings'])): ?>
        <div class="mb-6">
            <div class="relative max-w-lg">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
                </span>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search by name, email, or phone..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold bg-white text-gray-900"
                    onkeyup="filterBookings()"
                />
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Tab Content -->
        <?php if ($tab === 'rooms'): ?>
            <?php 
            // Pass all required variables to partial
            $room_bookings = $room_bookings;
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/room-bookings.php'; 
            ?>
        <?php elseif ($tab === 'pricing'): ?>
            <?php 
            $rooms = $rooms;
            $currencies = $currencies ?? [];
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/room-pricing.php'; 
            ?>
        <?php elseif ($tab === 'spa-pricing'): ?>
            <?php
            $spa_services = $spa_services ?? [];
            $currencies = $currencies ?? [];
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/spa-pricing.php';
            ?>
        <?php elseif ($tab === 'meeting-pricing'): ?>
            <?php
            $meeting_venues = $meeting_venues ?? [];
            $currencies = $currencies ?? [];
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/meeting-pricing.php';
            ?>
        <?php elseif ($tab === 'currencies'): ?>
            <?php 
            $currencies = $currencies ?? [];
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/currencies-manager.php'; 
            ?>
        <?php elseif ($tab === 'spa'): ?>
            <?php 
            $spa_bookings = $spa_bookings;
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/spa-bookings.php'; 
            ?>
        <?php elseif ($tab === 'meetings'): ?>
            <?php 
            $meeting_bookings = $meeting_bookings;
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/meeting-bookings.php'; 
            ?>
        <?php elseif ($tab === 'gallery'): ?>
            <?php 
            $gallery_items = $gallery_items;
            $categories = $gallery_categories;
            $rooms = $rooms;
            $room_images = $room_images ?? [];
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/gallery-manager.php'; 
            ?>
        <?php elseif ($tab === 'site-images'): ?>
            <?php 
            $site_images = $site_images;
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/site-images-manager.php'; 
            ?>
        <?php elseif ($tab === 'virtual-tour'): ?>
            <?php 
            $virtual_tour = $virtual_tour;
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/virtual-tour-manager.php'; 
            ?>
        <?php elseif ($tab === 'profile'): ?>
            <?php 
            $user = $user;
            $basePath = $basePath;
            $csrf_token = $csrf_token;
            include __DIR__ . '/partials/profile.php'; 
            ?>
        <?php endif; ?>
    </main>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Scripts -->
    <script>
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // CSRF Token
        const csrfToken = '<?= ViewHelper::e($csrf_token) ?>';
        const csrfTokenName = '<?= CSRF_TOKEN_NAME ?>';

        // Refresh data
        async function refreshData() {
            const btn = document.getElementById('refreshBtn');
            const icon = btn.querySelector('i[data-lucide="refresh-cw"]');
            btn.disabled = true;
            icon.classList.add('animate-spin');
            
            try {
                const response = await fetch('<?= ViewHelper::e($basePath) ?>/admin?tab=<?= ViewHelper::e($tab) ?>');
                if (response.ok) {
                    showToast('Data refreshed from server.', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast('Using local data. Server sync unavailable.', 'error');
                }
            } catch (error) {
                showToast('Error refreshing data. Using local cache.', 'error');
            } finally {
                btn.disabled = false;
                icon.classList.remove('animate-spin');
            }
        }

        // Filter bookings
        function filterBookings() {
            const query = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
            const rows = document.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Update booking status
        async function updateBooking(type, id, status) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('type', type);
            formData.append('status', status);
            formData.append(csrfTokenName, csrfToken);
            
            try {
                const response = await fetch('<?= ViewHelper::e($basePath) ?>/admin/bookings/update', {
                    method: 'POST',
                    body: formData,
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showToast(`Booking status updated to ${status}.`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Failed to update booking', 'error');
                }
            } catch (error) {
                showToast('An error occurred while updating the booking.', 'error');
            }
        }

        // Delete booking
        async function deleteBooking(type, id) {
            if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('type', type);
            formData.append(csrfTokenName, csrfToken);
            
            try {
                const response = await fetch('<?= ViewHelper::e($basePath) ?>/admin/bookings/delete', {
                    method: 'POST',
                    body: formData,
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showToast('Booking successfully deleted.', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Failed to delete booking', 'error');
                }
            } catch (error) {
                showToast('An error occurred while deleting the booking.', 'error');
            }
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type} bg-${type === 'success' ? 'green' : 'red'}-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center justify-between min-w-[300px] animate-fade-in`;
            
            toast.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            `;
            
            container.appendChild(toast);
            lucide.createIcons();
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
    <script src="<?= ViewHelper::asset('js/admin.js') ?>"></script>
</body>
</html>
