// Virtual Tour JavaScript
// Handles virtual tour modal functionality using Pannellum

let pannellumViewer = null;
let isVirtualTourOpen = false;

async function ensurePannellumLoaded() {
    const cssHref = 'https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css';
    const jsSrc = 'https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js';

    // Load CSS
    if (!document.querySelector(`link[href="${cssHref}"]`)) {
        await new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = cssHref;
            link.onload = () => resolve();
            link.onerror = () => reject(new Error('Failed to load Pannellum CSS'));
            document.head.appendChild(link);
        });
    }

    // Load JS
    if (!window.pannellum) {
        await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = jsSrc;
            script.async = true;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Failed to load Pannellum JS'));
            document.body.appendChild(script);
        });
    }
}

async function openVirtualTourModal() {
    if (isVirtualTourOpen) return;
    
    isVirtualTourOpen = true;
    
    try {
        // Load Pannellum library
        await ensurePannellumLoaded();
        
        // Get virtual tour data from API
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/') || '';
        const apiUrl = basePath + '/api/bootstrap';
        
        const response = await fetch(apiUrl);
        const data = await response.json();
        
        const imageUrl = data.virtualTourImage || 'https://pannellum.org/images/alma.jpg';
        const hotspots = data.virtualTourHotspots || [];
        
        // Create modal
        const modal = document.createElement('div');
        modal.id = 'virtualTourModal';
        modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-[100]';
        modal.innerHTML = `
            <style>
                .pnlm-hotspot.pnlm-info-hotspot .pnlm-sprite {
                    background-color: #0E8040 !important;
                    border-radius: 50%;
                    transform: scale(0.8);
                    transition: transform 0.2s ease-in-out;
                }
                .pnlm-hotspot.pnlm-info-hotspot:hover .pnlm-sprite {
                    transform: scale(1);
                }
                .pnlm-tooltip {
                    background-color: rgba(0, 0, 0, 0.7) !important;
                    color: #fff !important;
                    border: 1px solid rgba(255, 255, 255, 0.5) !important;
                    border-radius: 4px !important;
                    padding: 8px 12px !important;
                }
            </style>
            <div class="relative w-full h-full" onclick="event.stopPropagation()">
                <button type="button" onclick="closeVirtualTourModal()" class="absolute top-4 right-4 text-white bg-black/50 rounded-full p-2 hover:bg-black/80 z-20" aria-label="Close virtual tour">
                    <i data-lucide="x" class="w-8 h-8"></i>
                </button>
                <div id="panorama-container" class="w-full h-full"></div>
            </div>
        `;
        
        modal.onclick = closeVirtualTourModal;
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Wait for Pannellum to be available
        let retries = 0;
        const maxRetries = 10;
        const checkPannellum = setInterval(() => {
            if (window.pannellum) {
                clearInterval(checkPannellum);
                
                // Format hotspots for Pannellum
                const pannellumHotspots = hotspots.map(spot => ({
                    pitch: parseFloat(spot.pitch) || 0,
                    yaw: parseFloat(spot.yaw) || 0,
                    type: 'info',
                    text: spot.text || ''
                }));
                
                // Initialize Pannellum viewer
                pannellumViewer = window.pannellum.viewer('panorama-container', {
                    type: 'equirectangular',
                    panorama: imageUrl,
                    autoLoad: true,
                    autoRotate: -2,
                    showControls: true,
                    showZoomCtrl: true,
                    showFullscreenCtrl: true,
                    keyboardZoom: true,
                    mouseZoom: true,
                    compass: true,
                    draggable: true,
                    hotSpots: pannellumHotspots
                });
            } else {
                retries++;
                if (retries >= maxRetries) {
                    clearInterval(checkPannellum);
                    alert('Failed to load virtual tour viewer. Please try again.');
                    closeVirtualTourModal();
                }
            }
        }, 100);
        
    } catch (error) {
        console.error('Failed to open virtual tour:', error);
        alert('Failed to load virtual tour. Please try again.');
        closeVirtualTourModal();
    }
}

function closeVirtualTourModal() {
    if (!isVirtualTourOpen) return;
    
    isVirtualTourOpen = false;
    
    // Destroy Pannellum viewer
    if (pannellumViewer && typeof pannellumViewer.destroy === 'function') {
        pannellumViewer.destroy();
        pannellumViewer = null;
    }
    
    // Remove modal
    const modal = document.getElementById('virtualTourModal');
    if (modal) {
        modal.remove();
    }
    
    document.body.style.overflow = '';
}

// Make functions globally available
window.openVirtualTourModal = openVirtualTourModal;
window.closeVirtualTourModal = closeVirtualTourModal;

