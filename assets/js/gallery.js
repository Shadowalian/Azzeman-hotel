// Gallery modal interactivity for public gallery page

(() => {
    let currentIndex = 0;
    let imageIds = Array.isArray(window.GALLERY_IMAGE_IDS) ? window.GALLERY_IMAGE_IDS : [];
    const endpoint = window.GALLERY_BLOG_ENDPOINT || '/get_image_blog.php';

    const modal = document.getElementById('galleryModal');
    if (!modal) return;

    const modalCard = modal.querySelector('[data-modal-card]');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const closeBtn = document.getElementById('closeGalleryModal');
    const prevBtn = document.getElementById('prevImage');
    const nextBtn = document.getElementById('nextImage');

    const galleryItems = document.querySelectorAll('#galleryGrid [data-image-id]');
    if (galleryItems.length === 0) return;

    // Ensure IDs match grid order
    imageIds = Array.from(galleryItems).map(item => parseInt(item.getAttribute('data-image-id'), 10));

    const openModal = (index) => {
        const item = galleryItems[index];
        if (!item) return;

        const imageId = imageIds[index];
        const imageSrc = item.getAttribute('data-image-src');

        currentIndex = index;
        populateModal(imageId, imageSrc);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            modalCard.classList.remove('opacity-0', 'scale-95');
            modalCard.classList.add('opacity-100', 'scale-100');
        });
    };

    const closeModal = () => {
        modalCard.classList.add('opacity-0', 'scale-95');
        modalCard.classList.remove('opacity-100', 'scale-100');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    };

    const populateModal = async (imageId, imageSrc) => {
        modalImage.src = imageSrc || '';
        modalTitle.textContent = 'Loading details...';
        modalContent.innerHTML = '<p class="text-gray-500">Fetching story...</p>';

        try {
            const response = await fetch(`${endpoint}?id=${encodeURIComponent(imageId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const data = await response.json();
            if (!data || !data.success) {
                throw new Error(data?.error || 'Unknown error');
            }

            modalImage.src = data.image || imageSrc || '';
            modalTitle.textContent = data.blog_title || 'Azzeman Hotel';
            modalContent.innerHTML = data.blog_content || '<p class="text-gray-500">No additional story has been added yet.</p>';
        } catch (error) {
            console.error('Failed to load gallery story', error);
            modalTitle.textContent = 'Something went wrong';
            modalContent.innerHTML = '<p class="text-red-500">We were unable to load this story. Please try again later.</p>';
        }
    };

    const showNext = () => {
        currentIndex = (currentIndex + 1) % galleryItems.length;
        const nextId = imageIds[currentIndex];
        const nextSrc = galleryItems[currentIndex].getAttribute('data-image-src');
        populateModal(nextId, nextSrc);
    };

    const showPrev = () => {
        currentIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;
        const prevId = imageIds[currentIndex];
        const prevSrc = galleryItems[currentIndex].getAttribute('data-image-src');
        populateModal(prevId, prevSrc);
    };

    galleryItems.forEach((item, index) => {
        item.addEventListener('click', () => openModal(index));
        item.addEventListener('keypress', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openModal(index);
            }
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    prevBtn?.addEventListener('click', showPrev);
    nextBtn?.addEventListener('click', showNext);

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (modal.classList.contains('hidden')) return;
        if (event.key === 'Escape') closeModal();
        if (event.key === 'ArrowRight') showNext();
        if (event.key === 'ArrowLeft') showPrev();
    });
})();

// Gallery Filtering Logic
function filterGallery(category) {
    const items = document.querySelectorAll('.gallery-item, #galleryGrid > div');
    const buttons = document.querySelectorAll('.gallery-filter');

    // Update active button state
    buttons.forEach(btn => {
        if (btn.getAttribute('data-category') === category) {
            btn.classList.add('active');
            btn.classList.add('bg-brand-gold', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-700');
        } else {
            btn.classList.remove('active');
            btn.classList.remove('bg-brand-gold', 'text-white');
            btn.classList.add('bg-white', 'text-gray-700');
        }
    });

    // Filter items
    items.forEach(item => {
        const itemCategory = item.getAttribute('data-category');
        if (category === 'all' || itemCategory === category) {
            item.classList.remove('hidden');
            item.classList.add('block');
            // Add animation class if needed
            item.style.opacity = '0';
            setTimeout(() => {
                item.style.opacity = '1';
            }, 50);
        } else {
            item.classList.add('hidden');
            item.classList.remove('block');
        }
    });
}

// Initialize filters on load
document.addEventListener('DOMContentLoaded', () => {
    // Check if there's a hash in the URL for filtering
    const hash = window.location.hash.replace('#', '');
    if (hash && document.querySelector(`.gallery-filter[data-category="${hash}"]`)) {
        filterGallery(hash);
    }
});

