// Navbar JavaScript
// Scroll effects and mobile menu functionality

// Navbar scroll effect - simple: transparent with white text at top, white background with black text when scrolled
function updateNavbarOnScroll() {
    const navbar = document.getElementById('mainNavbar');
    if (!navbar) return;
    
    // Get scroll position
    const scrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
    
    // Threshold: change navbar style after scrolling 50px from top
    const scrollThreshold = 50;
    
    // Apply background - white when scrolled, transparent when at top
    if (scrollY > scrollThreshold) {
        // Scrolled: white background with black text
        navbar.style.setProperty('background-color', 'rgba(255, 255, 255, 0.95)', 'important');
        navbar.style.setProperty('backdrop-filter', 'blur(14px)', 'important');
        navbar.style.setProperty('-webkit-backdrop-filter', 'blur(14px)', 'important');
        navbar.style.setProperty('box-shadow', '0 12px 24px rgba(15, 23, 42, 0.12)', 'important');
        navbar.style.setProperty('border-bottom', '1px solid rgba(15, 23, 42, 0.08)', 'important');
        navbar.classList.add('scrolled');
        navbar.classList.remove('at-top');
        applyBlackText(navbar);
    } else {
        // At top: translucent dark background with white text
        navbar.style.setProperty('background-color', 'rgba(17, 24, 39, 0.45)', 'important');
        navbar.style.setProperty('backdrop-filter', 'blur(16px)', 'important');
        navbar.style.setProperty('-webkit-backdrop-filter', 'blur(16px)', 'important');
        navbar.style.setProperty('box-shadow', '0 12px 32px rgba(15, 23, 42, 0.25)', 'important');
        navbar.style.setProperty('border-bottom', '1px solid rgba(255, 255, 255, 0.08)', 'important');
        navbar.classList.add('at-top');
        navbar.classList.remove('scrolled');
        applyWhiteText(navbar);
    }
}

// Helper function to apply black text
function applyBlackText(navbar) {
    // Update nav links color - black
    const navLinks = navbar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.style.setProperty('color', '#1f2937', 'important'); // gray-800 - black
        link.style.setProperty('--tw-text-opacity', '1', 'important');
        link.classList.add('text-gray-800');
        link.classList.remove('text-white');
    });
    
    // Update virtual tour text
    const navLinkTexts = navbar.querySelectorAll('.nav-link-text, .nav-link-text-bold');
    navLinkTexts.forEach(text => {
        text.style.setProperty('color', '#111827', 'important'); // gray-900 - black
    });
    
    // Update mobile menu icon color
    const navIcons = navbar.querySelectorAll('.nav-icon');
    navIcons.forEach(icon => {
        icon.style.setProperty('color', '#0E8040', 'important'); // brand-green - good contrast on white
        icon.style.setProperty('stroke', '#0E8040', 'important');
    });
    
    // Update logo - slightly darker when scrolled for better contrast
    const logo = navbar.querySelector('img[alt="Azzeman Hotel Logo"]');
    if (logo) {
        logo.style.setProperty('filter', 'brightness(0.85)', 'important');
    }
}

// Helper function to apply white text
function applyWhiteText(navbar) {
    // Update nav links color - white
    const navLinks = navbar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.style.setProperty('color', 'rgba(255, 255, 255, 0.9)', 'important'); // white
        link.style.setProperty('--tw-text-opacity', '0.9', 'important');
        link.classList.remove('text-gray-800', 'text-gray-900');
        link.classList.add('text-white');
    });
    
    // Update virtual tour text
    const navLinkTexts = navbar.querySelectorAll('.nav-link-text, .nav-link-text-bold');
    navLinkTexts.forEach(text => {
        text.style.setProperty('color', 'rgba(255, 255, 255, 0.9)', 'important'); // white
    });
    
    // Update mobile menu icon color
    const navIcons = navbar.querySelectorAll('.nav-icon');
    navIcons.forEach(icon => {
        icon.style.setProperty('color', 'white', 'important'); // white
        icon.style.setProperty('stroke', 'white', 'important');
    });
    
    // Update logo - normal brightness
    const logo = navbar.querySelector('img[alt="Azzeman Hotel Logo"]');
    if (logo) {
        logo.style.setProperty('filter', 'brightness(1)', 'important');
    }
}

// Listen to scroll events
window.addEventListener('scroll', updateNavbarOnScroll, { passive: true });
window.addEventListener('resize', updateNavbarOnScroll, { passive: true });

// Initialize on page load
function initializeNavbar() {
    // Small delay to ensure DOM is fully ready
    setTimeout(function() {
        updateNavbarOnScroll();
    }, 10);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNavbar);
} else {
    // DOM already loaded, set initial state immediately
    initializeNavbar();
}

// Also run on window load as backup
window.addEventListener('load', function() {
    updateNavbarOnScroll();
});

// Mobile menu toggle
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const menuIcon = document.getElementById('menuIcon');
    const closeIcon = document.getElementById('closeIcon');
    
    if (!menu || !menuIcon || !closeIcon) return;
    
    if (menu.classList.contains('max-h-0')) {
        menu.classList.remove('max-h-0');
        menu.classList.add('max-h-screen');
        menuIcon.classList.add('hidden');
        closeIcon.classList.remove('hidden');
    } else {
        menu.classList.add('max-h-0');
        menu.classList.remove('max-h-screen');
        menuIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
    }
}

// Gallery submenu toggle
function toggleGallerySubmenu() {
    const submenu = document.getElementById('gallerySubmenu');
    const chevron = document.getElementById('galleryChevron');
    
    if (!submenu || !chevron) return;
    
    if (submenu.classList.contains('max-h-0')) {
        submenu.classList.remove('max-h-0');
        submenu.classList.add('max-h-96');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        submenu.classList.add('max-h-0');
        submenu.classList.remove('max-h-96');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Scroll to section helper
function scrollToSection(sectionId) {
    // If we're on a gallery category page and trying to go to the gallery,
    // redirect to the main gallery page instead of scrolling to the category-specific list.
    const isGalleryCategoryPage = window.location.pathname.includes('/gallery/') && 
                                 !window.location.pathname.endsWith('/gallery') && 
                                 !window.location.pathname.endsWith('/gallery/');
    
    if (sectionId === 'gallery' && isGalleryCategoryPage) {
        const basePath = window.APP_BASE_PATH || '';
        window.location.href = basePath + '/gallery';
        return;
    }

    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    } else {
        const basePath = window.APP_BASE_PATH || '';
        if (sectionId === 'gallery') {
            window.location.href = basePath + '/gallery';
        } else if (sectionId === 'home') {
            window.location.href = basePath + '/';
        } else {
            window.location.href = basePath + '/#' + sectionId;
        }
    }
}

