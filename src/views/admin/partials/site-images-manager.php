<?php
// Site Images Manager - Matching original React design
$site_images = isset($site_images) ? $site_images : [];
$basePath = isset($basePath) ? $basePath : '';
$csrf_token = isset($csrf_token) ? $csrf_token : '';
$site_image_data = [
    'hero_background' => ['description' => 'Hero / dial atmosphere image (homepage top).', 'default' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?q=80&w=2070&auto=format&fit=crop'],
    'about_image' => ['description' => 'About section + dial photos (Welcome to Azzeman).', 'default' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop'],
    'rooms_image' => ['description' => 'Default rooms elevator image when a room has no upload yet.', 'default' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1200&auto=format&fit=crop'],
    'meetings_image' => ['description' => 'Meetings & Events halls / map cards image.', 'default' => 'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=2070&auto=format&fit=crop'],
    'spa_image_1' => ['description' => 'Spa breathe collage — image 1.', 'default' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?q=80&w=800&auto=format&fit=crop'],
    'spa_image_2' => ['description' => 'Spa breathe collage — image 2.', 'default' => 'https://images.unsplash.com/photo-1519824145371-296894a0d72b?q=80&w=800&auto=format&fit=crop'],
    'spa_image_3' => ['description' => 'Spa breathe collage — image 3.', 'default' => 'https://images.unsplash.com/photo-1597015552392-4916a6953258?q=80&w=800&auto=format&fit=crop'],
    'spa_image_4' => ['description' => 'Spa breathe collage — image 4.', 'default' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=800&auto=format&fit=crop'],
    'gallery_page_fallback' => ['description' => 'Fallback header for gallery category pages with no images.', 'default' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=1200&auto=format&fit=crop'],
    'og_image' => ['description' => 'Social share image (og:image).', 'default' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?q=80&w=1200&auto=format&fit=crop'],
];
?>
<div class="bg-white shadow-md rounded-lg">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i data-lucide="image" class="w-6 h-6 mr-3 text-brand-green"></i> Site Image Management
        </h2>
        <p class="text-sm text-gray-500 mt-1">Update the main images used across the website.</p>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($site_image_data as $key => $data): ?>
                <div class="border rounded-lg shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 bg-gray-50 border-b">
                        <h3 class="font-semibold text-gray-800"><?= ViewHelper::e($data['description']) ?></h3>
                    </div>
                    <div class="p-4 flex-grow flex items-center justify-center bg-stone-100 min-h-[150px]">
                        <img
                            src="<?= ViewHelper::e($site_images[$key] ?? $data['default']) ?>"
                            alt="<?= ViewHelper::e($data['description']) ?>"
                            class="max-h-36 max-w-full object-contain rounded"
                        />
                    </div>
                    <div class="p-3 bg-gray-50 border-t">
                        <button
                            onclick="openSiteImageModal('<?= ViewHelper::e($key) ?>', '<?= ViewHelper::e($data['description']) ?>', '<?= ViewHelper::e($site_images[$key] ?? $data['default']) ?>')"
                            class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-green hover:brightness-110"
                        >
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                            Change Image
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Site Image Upload Modal - Matching original React design -->
<div id="siteImageModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4" onclick="closeSiteImageModal()">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-xl relative" onclick="event.stopPropagation()">
        <button onclick="closeSiteImageModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700" aria-label="Close upload modal">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
        <div class="p-8">
            <h2 class="text-2xl font-bold font-sans text-brand-green mb-1">Change Image</h2>
            <p class="text-gray-500 mb-6" id="siteImageDescription"></p>

            <div class="space-y-6">
                <!-- Side-by-side preview: Current vs New -->
                <div class="grid grid-cols-2 gap-4 items-center">
                    <div class="text-center">
                        <h4 class="font-semibold text-gray-600 mb-2">Current Image</h4>
                        <img id="siteImageCurrent" src="" alt="Current" class="max-h-32 mx-auto rounded-md object-contain border p-1" />
                    </div>
                    <div class="text-center">
                        <h4 class="font-semibold text-gray-600 mb-2">New Image</h4>
                        <div id="siteImageNewPreview" class="h-32 flex items-center justify-center text-gray-400 bg-gray-100 rounded-md border">
                            Preview
                        </div>
                    </div>
                </div>

                <!-- File Upload Area -->
                <div 
                    id="siteImageDropArea"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-brand-gold hover:bg-gray-50 transition-colors"
                    onclick="document.getElementById('siteImageFileInput').click()"
                    ondrop="handleSiteImageDrop(event)"
                    ondragover="event.preventDefault()"
                >
                    <input type="file" id="siteImageFileInput" onchange="handleSiteImageFileChange(event)" accept="image/*" class="hidden" />
                    <div class="text-gray-500">
                        <i data-lucide="upload-cloud" class="w-12 h-12 mx-auto"></i>
                        <p class="mt-2 font-semibold">Click to browse or drag & drop</p>
                        <p class="text-sm">PNG, JPG, WEBP (max 2MB)</p>
                    </div>
                </div>

                <!-- URL Input (Alternative) -->
                <div>
                    <label for="siteImageUrl" class="block text-sm font-medium text-gray-700 mb-2">Or enter image URL</label>
                    <input
                        type="url"
                        id="siteImageUrl"
                        placeholder="https://example.com/image.jpg or paste base64 data URL"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold bg-white text-gray-900"
                        oninput="updateSiteImageUrlPreview()"
                    />
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeSiteImageModal()" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button
                        onclick="saveSiteImage()"
                        id="saveSiteImageBtn"
                        disabled
                        class="inline-flex justify-center items-center px-6 py-2 text-sm font-medium text-white bg-brand-gold border border-transparent rounded-md shadow-sm hover:brightness-95 disabled:bg-gray-400 disabled:cursor-not-allowed"
                    >
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // This script is part of site-images-manager.php and relies on admin.js for toast and fetch functions
    let currentSiteImageKey = '';
    let currentSiteImageSrc = '';
    let selectedSiteImageFile = null;
    let previewSiteImageUrl = null;

    document.addEventListener('DOMContentLoaded', () => {
        const siteImageUrlInput = document.getElementById('siteImageUrl');
        if (siteImageUrlInput) {
            siteImageUrlInput.addEventListener('input', updateSiteImageUrlPreview);
        }
    });

    function openSiteImageModal(key, description, currentSrc) {
        currentSiteImageKey = key;
        currentSiteImageSrc = currentSrc;
        
        // Reset state
        selectedSiteImageFile = null;
        if (previewSiteImageUrl) {
            URL.revokeObjectURL(previewSiteImageUrl);
            previewSiteImageUrl = null;
        }
        
        // Set modal content
        document.getElementById('siteImageDescription').textContent = description;
        document.getElementById('siteImageCurrent').src = currentSrc;
        document.getElementById('siteImageNewPreview').innerHTML = '<div class="h-32 flex items-center justify-center text-gray-400 bg-gray-100 rounded-md">Preview</div>';
        document.getElementById('siteImageUrl').value = '';
        document.getElementById('siteImageFileInput').value = '';
        document.getElementById('saveSiteImageBtn').disabled = true;
        
        // Show modal
        document.getElementById('siteImageModal').classList.remove('hidden');
        document.getElementById('siteImageModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeSiteImageModal() {
        document.getElementById('siteImageModal').classList.add('hidden');
        document.getElementById('siteImageModal').classList.remove('flex');
        
        // Clean up
        currentSiteImageKey = '';
        currentSiteImageSrc = '';
        selectedSiteImageFile = null;
        if (previewSiteImageUrl) {
            URL.revokeObjectURL(previewSiteImageUrl);
            previewSiteImageUrl = null;
        }
        document.getElementById('siteImageUrl').value = '';
        document.getElementById('siteImageFileInput').value = '';
        document.getElementById('saveSiteImageBtn').disabled = true;
    }

    function handleSiteImageFileChange(event) {
        const file = event.target.files?.[0];
        handleSiteImageFileSelect(file);
    }

    function handleSiteImageDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        const file = event.dataTransfer.files?.[0];
        handleSiteImageFileSelect(file);
    }

    function handleSiteImageFileSelect(file) {
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            showToast('Please select a valid image file.', 'error');
            return;
        }

        if (file.size > <?= UPLOAD_MAX_SIZE ?>) { // 2MB limit from config
            showToast('Image size should be less than 2MB.', 'error');
            return;
        }

        selectedSiteImageFile = file;
        
        // Clear URL input
        document.getElementById('siteImageUrl').value = '';
        
        // Create preview
        if (previewSiteImageUrl) {
            URL.revokeObjectURL(previewSiteImageUrl);
        }
        previewSiteImageUrl = URL.createObjectURL(file);
        
        // Update preview
        const previewDiv = document.getElementById('siteImageNewPreview');
        previewDiv.innerHTML = `<img src="${previewSiteImageUrl}" alt="Preview" class="max-h-32 mx-auto rounded-md object-contain border p-1" />`;
        
        // Enable save button
        document.getElementById('saveSiteImageBtn').disabled = false;
    }

    function updateSiteImageUrlPreview() {
        const imageUrl = document.getElementById('siteImageUrl').value.trim();
        const previewDiv = document.getElementById('siteImageNewPreview');
        
        if (!imageUrl) {
            previewDiv.innerHTML = '<div class="h-32 flex items-center justify-center text-gray-400 bg-gray-100 rounded-md">Preview</div>';
            document.getElementById('saveSiteImageBtn').disabled = true;
            return;
        }

        // Clear file selection
        selectedSiteImageFile = null;
        if (previewSiteImageUrl) {
            URL.revokeObjectURL(previewSiteImageUrl);
            previewSiteImageUrl = null;
        }
        document.getElementById('siteImageFileInput').value = '';

        // Try to load preview
        const img = new Image();
        img.onload = () => {
            previewDiv.innerHTML = `<img src="${imageUrl}" alt="Preview" class="max-h-32 mx-auto rounded-md object-contain border p-1" />`;
            document.getElementById('saveSiteImageBtn').disabled = false;
        };
        img.onerror = () => {
            previewDiv.innerHTML = '<div class="h-32 flex items-center justify-center text-red-400 bg-red-50 rounded-md border border-red-200">Invalid URL or image failed to load</div>';
            document.getElementById('saveSiteImageBtn').disabled = true;
        };
        img.src = imageUrl;
    }

    async function saveSiteImage() {
        if (!currentSiteImageKey) {
            showToast('No image selected for update.', 'error');
            return;
        }

        let imageSrc = '';

        // If file is selected, convert to base64
        if (selectedSiteImageFile) {
            const reader = new FileReader();
            reader.onloadend = async () => {
                if (typeof reader.result === 'string' && reader.result.startsWith('data:image/')) {
                    await sendSiteImageUpdate(reader.result);
                } else {
                    showToast('Failed to read the image file. Invalid format.', 'error');
                }
            };
            reader.onerror = () => {
                showToast('An error occurred while reading the image.', 'error');
            };
            reader.readAsDataURL(selectedSiteImageFile);
            return;
        }

        // Otherwise, use URL input
        const imageUrl = document.getElementById('siteImageUrl').value.trim();
        if (!imageUrl) {
            showToast('Please select an image file or enter an image URL.', 'error');
            return;
        }

        await sendSiteImageUpdate(imageUrl);
    }

    async function sendSiteImageUpdate(imageSrc) {
        const formData = new FormData();
        formData.append('key', currentSiteImageKey);
        formData.append('src', imageSrc);
        formData.append(csrfTokenName, csrfToken);
        
        const btn = document.getElementById('saveSiteImageBtn');
        const btnIcon = btn.querySelector('i[data-lucide="save"]');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i>Saving...';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        try {
            const response = await fetch('<?= ViewHelper::e($basePath) ?>/admin/site-images/update', {
                method: 'POST',
                body: formData,
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('Image updated successfully!', 'success');
                closeSiteImageModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to update image.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } catch (error) {
            console.error('Update site image error:', error);
            showToast('An error occurred while updating the image.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
</script>
