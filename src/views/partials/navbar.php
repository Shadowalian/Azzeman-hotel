<?php
// Navbar component matching original React design
$scrolled = isset($_GET['scrolled']) ? $_GET['scrolled'] : false;
$isOpen = isset($_GET['menuOpen']) ? $_GET['menuOpen'] : false;
$gallery_categories = $gallery_categories ?? [];

// Get base path for links (similar to admin dashboard)
$basePath = defined('APP_URL') && APP_URL !== 'https://yourdomain.com' 
    ? rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/')
    : '';
if (empty($basePath) || strpos($_SERVER['REQUEST_URI'], '/azzemanhotel_PHP') !== false) {
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $basePath = rtrim($scriptPath, '/');
}
if (empty($basePath)) {
    $basePath = '';
}
?>
<nav class="fixed w-full top-0 z-40 transition-all duration-300 bg-transparent at-top" id="mainNavbar">
    <div class="container mx-auto px-6">
        <div class="flex justify-between items-center h-24 w-full">
            <a href="#home" onclick="scrollToSection('home'); return false;" class="flex items-center cursor-pointer flex-shrink-0">
                <img src="<?= ViewHelper::asset('images/logo.png') ?>" alt="Azzeman Hotel Logo" class="navbar-logo" onerror="this.onerror=null; this.src='<?= ViewHelper::asset('images/logo.png') ?>';">
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center flex-1 justify-center" id="desktopMenu">
                <div class="flex items-center space-x-6">
                    <a href="#home" onclick="scrollToSection('home'); return false;" class="font-medium transition-colors cursor-pointer nav-link">Home</a>
                    <a href="#about" onclick="scrollToSection('about'); return false;" class="font-medium transition-colors cursor-pointer nav-link">About</a>
                    <a href="#rooms" onclick="scrollToSection('rooms'); return false;" class="font-medium transition-colors cursor-pointer nav-link">Rooms</a>
                    <a href="#services" onclick="scrollToSection('services'); return false;" class="font-medium transition-colors cursor-pointer nav-link">Services</a>
                    <a href="#meetings" onclick="scrollToSection('meetings'); return false;" class="font-medium transition-colors cursor-pointer nav-link">Meetings & Events</a>
                    <a href="#contact" onclick="scrollToSection('contact'); return false;" class="font-medium transition-colors cursor-pointer nav-link">Contact</a>

                    <!-- Gallery Dropdown -->
                    <div class="relative group">
                        <a href="#gallery" onclick="scrollToSection('gallery'); return false;" class="font-medium transition-colors cursor-pointer flex items-center nav-link">
                            Gallery <i data-lucide="chevron-down" class="w-4 h-4 ml-1 transition-transform group-hover:rotate-180"></i>
                        </a>
                        <div class="absolute top-full right-0 pt-2 w-48 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none group-hover:pointer-events-auto z-10">
                            <div class="bg-white rounded-md shadow-lg py-2">
                                <a href="#gallery" onclick="scrollToSection('gallery'); return false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-brand-green/10 hover:text-brand-green">All on This Page</a>
                                <?php if (isset($gallery_categories) && is_array($gallery_categories)): ?>
                                    <?php foreach ($gallery_categories as $navCategory): ?>
                                        <a href="<?= ViewHelper::e($basePath) ?>/gallery/<?= ViewHelper::e($navCategory['id']) ?>" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-brand-green/10 hover:text-brand-green"><?= ViewHelper::e($navCategory['name']) ?></a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden md:flex items-center space-x-4 flex-shrink-0">
                <!-- Virtual Tour Button -->
                <button type="button" onclick="openVirtualTourModal()" class="flex items-center group transition-transform transform hover:scale-105">
                    <div class="font-medium transition-colors text-right nav-link-text">
                        <p class="text-xs whitespace-nowrap leading-tight">Take the</p>
                        <p class="text-sm whitespace-nowrap font-bold group-hover:text-brand-green nav-link-text-bold">virtual tour!</p>
                    </div>
                    <img src="<?= ViewHelper::asset('images/360.png') ?>" alt="360 Virtual Tour" class="h-14 w-14 ml-1 drop-shadow-md" onerror="this.style.display='none'">
                </button>

                <!-- Book Now Button -->
                <button type="button" onclick="openBookingModal()" class="bg-brand-gold text-white font-semibold py-2 px-5 rounded-lg hover:brightness-95 transition-all transform hover:scale-105">
                    Book Now
                </button>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button type="button" onclick="toggleMobileMenu()" id="mobileMenuBtn">
                    <i data-lucide="menu" class="h-7 w-7 nav-icon" id="menuIcon"></i>
                    <i data-lucide="x" class="h-7 w-7 nav-icon hidden" id="closeIcon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden transition-all duration-300 max-h-0 overflow-hidden" id="mobileMenu">
        <div class="px-6 pb-6 space-y-2 pt-2 bg-white">
            <a href="#home" onclick="scrollToSection('home'); toggleMobileMenu(); return false;" class="block font-medium transition-colors py-2 text-gray-600 hover:text-brand-green">Home</a>
            <a href="#about" onclick="scrollToSection('about'); toggleMobileMenu(); return false;" class="block font-medium transition-colors py-2 text-gray-600 hover:text-brand-green">About</a>
            <a href="#rooms" onclick="scrollToSection('rooms'); toggleMobileMenu(); return false;" class="block font-medium transition-colors py-2 text-gray-600 hover:text-brand-green">Rooms</a>
            <a href="#services" onclick="scrollToSection('services'); toggleMobileMenu(); return false;" class="block font-medium transition-colors py-2 text-gray-600 hover:text-brand-green">Services</a>
            <a href="#meetings" onclick="scrollToSection('meetings'); toggleMobileMenu(); return false;" class="block font-medium transition-colors py-2 text-gray-600 hover:text-brand-green">Meetings & Events</a>
            <a href="#contact" onclick="scrollToSection('contact'); toggleMobileMenu(); return false;" class="block font-medium transition-colors py-2 text-gray-600 hover:text-brand-green">Contact</a>
            
            <!-- Mobile Gallery Submenu -->
            <div>
                <button type="button" onclick="toggleGallerySubmenu()" class="w-full flex justify-between items-center font-medium transition-colors py-2 text-gray-600 hover:text-brand-green">
                    <span>Gallery</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" id="galleryChevron"></i>
                </button>
                <div class="transition-all duration-300 overflow-hidden max-h-0" id="gallerySubmenu">
                    <div class="pl-4 mt-2 space-y-2">
                        <a href="#gallery" onclick="scrollToSection('gallery'); toggleMobileMenu(); return false;" class="block font-medium transition-colors py-2 text-sm text-left w-full text-gray-600 hover:text-brand-green">All on This Page</a>
                        <?php if (isset($gallery_categories) && is_array($gallery_categories)): ?>
                            <?php foreach ($gallery_categories as $navCategory): ?>
                                <a href="<?= ViewHelper::e($basePath) ?>/gallery/<?= ViewHelper::e($navCategory['id']) ?>" onclick="toggleMobileMenu(); return true;" class="block font-medium transition-colors py-2 text-sm text-left w-full text-gray-600 hover:text-brand-green"><?= ViewHelper::e($navCategory['name']) ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <button type="button" onclick="openVirtualTourModal(); toggleMobileMenu();" class="block font-medium transition-colors py-2 w-full text-left text-gray-600 hover:text-brand-green">Virtual Tour</button>
            <button type="button" onclick="openBookingModal(); toggleMobileMenu();" class="w-full bg-brand-gold text-white font-semibold py-3 px-5 rounded-lg hover:brightness-95 transition-colors mt-4">
                Book Now
            </button>
        </div>
    </div>
</nav>

