// Home Page JavaScript
// Scroll animations and gallery functionality

// Set hero background image from data attribute
document.addEventListener('DOMContentLoaded', () => {
    const heroBg = document.querySelector('.hero-background[data-bg-image]');
    if (heroBg) {
        const bgImage = heroBg.getAttribute('data-bg-image');
        if (bgImage) {
            heroBg.style.backgroundImage = `url('${bgImage}')`;
            // Ensure background covers the entire area including top
            heroBg.style.backgroundPosition = 'center top';
            heroBg.style.backgroundSize = 'cover';
        }
    }
    
    // Ensure hero section starts at the very top
    const heroSection = document.getElementById('home');
    if (heroSection) {
        heroSection.style.marginTop = '0';
        heroSection.style.paddingTop = '0';
    }
});

// Intersection Observer for scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all sections on page load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('section[id]').forEach(section => {
        observer.observe(section);
    });
    
    // Apply transition delays from data attributes
    document.querySelectorAll('[data-delay]').forEach(element => {
        const delay = element.getAttribute('data-delay');
        if (delay) {
            element.style.transitionDelay = delay + 'ms';
        }
    });
});

// Gallery filtering with progressive "See more" support
const galleryState = {
    activeCategory: 'all',
    expanded: false,
    limit: typeof window !== 'undefined' && Number.isFinite(window.HOME_GALLERY_LIMIT)
        ? window.HOME_GALLERY_LIMIT
        : 8
};

let applyGalleryFilter;
let updateGalleryFilterButtons;

function filterGallery(category) {
    galleryState.activeCategory = category || 'all';
    galleryState.expanded = false;

    if (typeof updateGalleryFilterButtons === 'function') {
        updateGalleryFilterButtons(galleryState.activeCategory);
    } else {
        document.querySelectorAll('.gallery-filter').forEach(btn => {
            const matches = btn.dataset.category === galleryState.activeCategory;
            btn.classList.toggle('active', matches);
            btn.classList.toggle('inactive', !matches);
        });
    }

    if (typeof applyGalleryFilter === 'function') {
        applyGalleryFilter();
    } else {
        document.querySelectorAll('.gallery-item').forEach(item => {
            if (galleryState.activeCategory === 'all' || item.dataset.category === galleryState.activeCategory) {
                item.style.display = '';
                item.classList.add('fade-in');
            } else {
                item.style.display = 'none';
            }
        });
    }
}

// Lightbox functionality
let lightboxImages = [];
let currentLightboxIndex = 0;

function initLightbox(images) {
    lightboxImages = images;
}

function openLightbox(index) {
    if (!lightboxImages || lightboxImages.length === 0) {
        // Get images from gallery items
        const items = document.querySelectorAll('.gallery-item img');
        lightboxImages = Array.from(items).map(img => img.src);
    }
    
    currentLightboxIndex = index;
    
    // Create lightbox modal
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-backdrop" onclick="closeLightbox()"></div>
        <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close lightbox">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <button class="lightbox-prev" onclick="prevLightbox()" aria-label="Previous image">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <button class="lightbox-next" onclick="nextLightbox()" aria-label="Next image">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
        <div class="lightbox-content">
            <img src="${lightboxImages[currentLightboxIndex]}" alt="Gallery view ${currentLightboxIndex + 1}" class="lightbox-image" />
        </div>
    `;
    
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    
    // Keyboard navigation
    document.addEventListener('keydown', handleLightboxKeydown);
}

function closeLightbox() {
    const lightbox = document.querySelector('.lightbox');
    if (lightbox) {
        lightbox.remove();
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleLightboxKeydown);
    }
}

function nextLightbox() {
    currentLightboxIndex = (currentLightboxIndex + 1) % lightboxImages.length;
    updateLightboxImage();
}

function prevLightbox() {
    currentLightboxIndex = (currentLightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
    updateLightboxImage();
}

function updateLightboxImage() {
    const img = document.querySelector('.lightbox-image');
    if (img) {
        img.src = lightboxImages[currentLightboxIndex];
    }
}

function handleLightboxKeydown(e) {
    if (e.key === 'ArrowRight') nextLightbox();
    if (e.key === 'ArrowLeft') prevLightbox();
    if (e.key === 'Escape') closeLightbox();
}

// Scroll to section helper
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    } else if (sectionId === 'home') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Initialize lightbox with gallery images
document.addEventListener('DOMContentLoaded', () => {
    const galleryItems = document.querySelectorAll('.gallery-item img');
    if (galleryItems.length > 0) {
        const images = Array.from(galleryItems).map(img => img.getAttribute('data-src') || img.src);
        initLightbox(images);

        galleryItems.forEach(img => {
            if (img.dataset.src && !img.src) {
                img.src = img.dataset.src;
            }
            img.style.opacity = '1';
        });
    }
});

// Setup gallery "See more" interactions
document.addEventListener('DOMContentLoaded', () => {
    const galleryGrid = document.getElementById('galleryGrid');
    if (!galleryGrid) return;

    const limitAttr = parseInt(galleryGrid.getAttribute('data-initial-limit') || '', 10);
    if (!Number.isNaN(limitAttr)) {
        galleryState.limit = limitAttr;
    }

    const galleryItems = Array.from(galleryGrid.querySelectorAll('.gallery-item'));
    const seeMoreButton = document.getElementById('gallerySeeMore');
    const seeMoreLabel = seeMoreButton?.querySelector('.gallery-see-more-label');

    updateGalleryFilterButtons = (category) => {
        document.querySelectorAll('.gallery-filter').forEach(btn => {
            const matches = btn.dataset.category === category;
            btn.classList.toggle('active', matches);
            btn.classList.toggle('inactive', !matches);
        });
    };

    applyGalleryFilter = () => {
        let visibleCount = 0;
        let matchesCount = 0;

        galleryItems.forEach(item => {
            const matches = galleryState.activeCategory === 'all' || item.dataset.category === galleryState.activeCategory;

            if (!matches) {
                item.style.display = 'none';
                item.classList.add('is-hidden-by-limit');
                return;
            }

            matchesCount += 1;

            if (!galleryState.expanded && visibleCount >= galleryState.limit) {
                item.style.display = 'none';
                item.classList.add('is-hidden-by-limit');
            } else {
                item.style.display = '';
                item.classList.remove('is-hidden-by-limit');
                item.classList.add('fade-in');
            }

            visibleCount += 1;
        });

        if (!seeMoreButton) {
            return;
        }

        const hasMoreThanLimit = matchesCount > galleryState.limit;
        const shouldShowButton = hasMoreThanLimit && !galleryState.expanded;

        seeMoreButton.classList.toggle('is-hidden', !shouldShowButton);
        seeMoreButton.dataset.expanded = galleryState.expanded ? 'true' : 'false';
        seeMoreButton.setAttribute('aria-expanded', galleryState.expanded ? 'true' : 'false');

        if (seeMoreLabel) {
            seeMoreLabel.textContent = 'See more';
        }
    };

    applyGalleryFilter();
    updateGalleryFilterButtons(galleryState.activeCategory);

    seeMoreButton?.addEventListener('click', () => {
        galleryState.expanded = true;
        applyGalleryFilter();
    });
});

