// Admin Dashboard JavaScript
// Additional admin-specific functionality

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Set up drag and drop for file uploads
    setupDragAndDrop();
});

// Setup drag and drop for file uploads
function setupDragAndDrop() {
    const dropZones = document.querySelectorAll('[id*="DropZone"], [id*="dropZone"]');
    
    dropZones.forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.add('border-brand-gold', 'bg-gray-50');
        });
        
        zone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('border-brand-gold', 'bg-gray-50');
        });
        
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('border-brand-gold', 'bg-gray-50');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = zone.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    });
}

// Format date helper
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Format datetime helper
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Debounce helper for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Enhanced search with debounce
const debouncedFilter = debounce(() => {
    filterBookings();
}, 300);

// Update search input to use debounced filter
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debouncedFilter);
    }
});

// Auto-refresh data every 5 minutes
setInterval(() => {
    // Silently refresh data in background
    fetch('/admin?tab=' + (new URLSearchParams(window.location.search).get('tab') || 'rooms'))
        .then(response => {
            if (response.ok) {
                // Data refreshed successfully
                console.log('Data auto-refreshed');
            }
        })
        .catch(error => {
            console.error('Auto-refresh failed:', error);
        });
}, 5 * 60 * 1000); // 5 minutes

