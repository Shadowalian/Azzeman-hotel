<?php
// Virtual Tour Manager
$virtual_tour = isset($virtual_tour) ? $virtual_tour : ['image_url' => '', 'hotspots' => []];
$basePath = isset($basePath) ? $basePath : '';
$csrf_token = isset($csrf_token) ? $csrf_token : '';
$image_url = isset($virtual_tour['image_url']) ? $virtual_tour['image_url'] : '';
$hotspots = isset($virtual_tour['hotspots']) ? $virtual_tour['hotspots'] : [];
?>
<div class="bg-white shadow-md rounded-lg">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i data-lucide="globe" class="w-6 h-6 mr-3 text-brand-green"></i> Virtual Tour Management
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage the 360° panoramic image and its interactive hotspots.</p>
    </div>

    <!-- Image Upload Section -->
    <div class="p-6 grid md:grid-cols-2 gap-8 items-start">
        <div>
            <h3 class="font-semibold text-gray-800 mb-2">Upload New Tour Image</h3>
            <div
                class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-brand-gold hover:bg-gray-50 transition-colors"
                onclick="document.getElementById('tourFileInput').click()"
            >
                <input type="file" id="tourFileInput" accept="image/jpeg,image/png" class="hidden" onchange="handleTourFileSelect(event)" />
                <div id="tourUploadArea" class="text-gray-500">
                    <i data-lucide="upload-cloud" class="w-12 h-12 mx-auto mb-2"></i>
                    <p class="mt-2 font-semibold">Click to browse or drag & drop</p>
                    <p class="text-sm">High-resolution equirectangular panorama. No file size limit.</p>
                </div>
            </div>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800 mb-2">Current Image Preview</h3>
            <div class="border rounded-lg p-2 bg-stone-100 min-h-[200px] flex items-center justify-center">
                <?php if (!empty($image_url)): ?>
                    <img src="<?= ViewHelper::e($image_url) ?>" alt="Virtual Tour Preview" class="max-w-full max-h-48 object-contain rounded" id="tourImagePreview" />
                <?php else: ?>
                    <p class="text-gray-500" id="tourImagePreview">No image uploaded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Hotspot Management Section -->
    <div class="p-6 border-t">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i data-lucide="map-pin" class="w-6 h-6 mr-3 text-brand-green"></i> Interactive Hotspots
            </h2>
            <button onclick="addHotspot()" class="flex items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-green hover:brightness-110">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Add Hotspot
            </button>
        </div>

        <div class="p-3 bg-blue-50 border-l-4 border-blue-400 text-blue-700 text-xs mb-4">
            <div class="flex">
                <div class="py-1"><i data-lucide="help-circle" class="w-4 h-4 mr-2"></i></div>
                <div><strong>Pitch</strong> is the vertical angle (up/down from -90 to 90). <strong>Yaw</strong> is the horizontal angle (left/right from -180 to 180).</div>
            </div>
        </div>

        <div class="space-y-3" id="hotspotsList">
            <?php if (empty($hotspots)): ?>
                <div class="text-center py-8 text-gray-500">
                    <p>No hotspots created yet. Click "Add Hotspot" to begin.</p>
                </div>
            <?php else: ?>
                <?php foreach ($hotspots as $index => $spot): ?>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-gray-50 p-3 rounded-lg border hotspot-item" data-id="<?= $spot['id'] ?? 'hs-' . $index ?>">
                        <div class="md:col-span-6">
                            <label class="block text-xs font-medium text-gray-600">Text</label>
                            <input type="text" value="<?= ViewHelper::e($spot['text'] ?? '') ?>" class="hotspot-text mt-1 w-full text-sm px-2 py-1 border border-gray-300 rounded-md shadow-sm bg-white" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Pitch</label>
                            <input type="number" step="0.1" value="<?= $spot['pitch'] ?? 0 ?>" class="hotspot-pitch mt-1 w-full text-sm px-2 py-1 border border-gray-300 rounded-md shadow-sm bg-white" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600">Yaw</label>
                            <input type="number" step="0.1" value="<?= $spot['yaw'] ?? 0 ?>" class="hotspot-yaw mt-1 w-full text-sm px-2 py-1 border border-gray-300 rounded-md shadow-sm bg-white" />
                        </div>
                        <div class="md:col-span-2 flex justify-end items-end h-full">
                            <button onclick="deleteHotspot(this)" class="text-red-500 hover:text-red-700 p-2 rounded-md hover:bg-red-100" title="Delete Hotspot">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($hotspots)): ?>
            <div class="flex justify-end mt-6">
                <button onclick="saveHotspots()" class="flex items-center py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-gold hover:brightness-95">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Save All Hotspot Changes
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    let tourFileSelected = false;
    let tourFileData = null;

    function handleTourFileSelect(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
            showToast('Please select a valid image file (JPEG, PNG, etc.).', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onloadend = async () => {
            if (typeof reader.result === 'string') {
                tourFileData = reader.result;
                tourFileSelected = true;
                
                // Update preview
                document.getElementById('tourImagePreview').outerHTML = `<img src="${reader.result}" alt="Virtual Tour Preview" class="max-w-full max-h-48 object-contain rounded" id="tourImagePreview" />`;
                
                // Upload immediately
                await uploadTourImage(reader.result);
            } else {
                showToast('Failed to read the image file.', 'error');
            }
        };
        reader.readAsDataURL(file);
    }

    async function uploadTourImage(imageData) {
        const formData = new FormData();
        formData.append('image_url', imageData);
        formData.append('hotspots', JSON.stringify(getHotspotsData()));
        formData.append(csrfTokenName, csrfToken);
        
        try {
            const response = await fetch('<?= $basePath ?>/admin/virtual-tour/update', {
                method: 'POST',
                body: formData,
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showToast('Virtual tour image updated successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to update image', 'error');
            }
        } catch (error) {
            showToast('An error occurred while updating the image.', 'error');
        }
    }

    function addHotspot() {
        const hotspotId = 'hs-' + Date.now();
        const hotspotHtml = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-gray-50 p-3 rounded-lg border hotspot-item" data-id="${hotspotId}">
                <div class="md:col-span-6">
                    <label class="block text-xs font-medium text-gray-600">Text</label>
                    <input type="text" value="New hotspot info" class="hotspot-text mt-1 w-full text-sm px-2 py-1 border border-gray-300 rounded-md shadow-sm bg-white" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600">Pitch</label>
                    <input type="number" step="0.1" value="0" class="hotspot-pitch mt-1 w-full text-sm px-2 py-1 border border-gray-300 rounded-md shadow-sm bg-white" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600">Yaw</label>
                    <input type="number" step="0.1" value="0" class="hotspot-yaw mt-1 w-full text-sm px-2 py-1 border border-gray-300 rounded-md shadow-sm bg-white" />
                </div>
                <div class="md:col-span-2 flex justify-end items-end h-full">
                    <button onclick="deleteHotspot(this)" class="text-red-500 hover:text-red-700 p-2 rounded-md hover:bg-red-100" title="Delete Hotspot">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        `;
        
        const list = document.getElementById('hotspotsList');
        if (list.querySelector('.text-center')) {
            list.innerHTML = hotspotHtml;
        } else {
            list.insertAdjacentHTML('afterbegin', hotspotHtml);
        }
        
        // Show save button if hidden
        const saveBtn = list.parentElement.querySelector('button[onclick="saveHotspots()"]');
        if (!saveBtn) {
            const saveButtonHtml = `
                <div class="flex justify-end mt-6">
                    <button onclick="saveHotspots()" class="flex items-center py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-gold hover:brightness-95">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Save All Hotspot Changes
                    </button>
                </div>
            `;
            list.parentElement.insertAdjacentHTML('beforeend', saveButtonHtml);
        }
        
        lucide.createIcons();
    }

    function deleteHotspot(button) {
        const item = button.closest('.hotspot-item');
        item.remove();
        
        // Check if list is empty
        const list = document.getElementById('hotspotsList');
        if (list.querySelectorAll('.hotspot-item').length === 0) {
            list.innerHTML = '<div class="text-center py-8 text-gray-500"><p>No hotspots created yet. Click "Add Hotspot" to begin.</p></div>';
            const saveBtn = list.parentElement.querySelector('button[onclick="saveHotspots()"]');
            if (saveBtn) saveBtn.parentElement.remove();
        }
    }

    function getHotspotsData() {
        const items = document.querySelectorAll('.hotspot-item');
        const hotspots = [];
        items.forEach(item => {
            hotspots.push({
                id: item.dataset.id,
                text: item.querySelector('.hotspot-text').value,
                pitch: parseFloat(item.querySelector('.hotspot-pitch').value) || 0,
                yaw: parseFloat(item.querySelector('.hotspot-yaw').value) || 0,
            });
        });
        return hotspots;
    }

    async function saveHotspots() {
        const hotspots = getHotspotsData();
        
        const formData = new FormData();
        formData.append('hotspots', JSON.stringify(hotspots));
        formData.append('image_url', '<?= ViewHelper::e($image_url) ?>');
        formData.append(csrfTokenName, csrfToken);
        
        try {
            const response = await fetch('<?= $basePath ?>/admin/virtual-tour/update', {
                method: 'POST',
                body: formData,
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showToast('Hotspots saved successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to save hotspots', 'error');
            }
        } catch (error) {
            showToast('An error occurred while saving hotspots.', 'error');
        }
    }

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

