<?php
$gallery_items = $gallery_items ?? [];
$categories = $categories ?? [];
$rooms = $rooms ?? [];
$room_images = $room_images ?? [];
$basePath = $basePath ?? '';
$csrf_token = $csrf_token ?? '';

$placeholderImage = ViewHelper::asset('images/about-hotel.jpg');
$normalizedItems = array_map(function ($item) use ($placeholderImage) {
    $imagePath = $item['image_path'] ?? '';
    $imageUrl = $imagePath;
    if ($imageUrl && strpos($imageUrl, 'http') !== 0) {
        $imageUrl = ViewHelper::asset(ltrim($imageUrl, '/'));
    }
    if (!$imageUrl) {
        $imageUrl = $placeholderImage;
    }
    $item['image_url'] = $imageUrl;
    $item['category_value'] = !empty($item['category_id']) ? $item['category_id'] : ($item['category'] ?? '');
    $item['category_label'] = $item['category_name'] ?? $item['category'] ?? 'Uncategorized';
    return $item;
}, $gallery_items);

$normalizedRoomImages = array_map(function ($item) {
    $imagePath = $item['image_path'] ?? '';
    $imageUrl = '';
    if ($imagePath) {
        $imageUrl = (strpos($imagePath, 'http') === 0)
            ? $imagePath
            : ViewHelper::asset(ltrim($imagePath, '/'));
    }
    $item['image_url'] = $imageUrl;
    return $item;
}, $room_images);

$categoriesForJs = array_map(function ($category) {
    return [
        'id' => $category['id'] ?? '',
        'name' => $category['name'] ?? '',
        'description' => $category['description'] ?? '',
    ];
}, $categories);
?>

<div class="space-y-12">
    <section class="bg-white shadow-md rounded-3xl overflow-hidden">
        <div class="px-6 py-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 flex items-center gap-3">
                    <i data-lucide="bed-double" class="w-6 h-6 text-brand-green"></i>
                    Room Booking Images
                </h2>
                <p class="text-sm text-gray-500 mt-1 max-w-3xl">
                    Upload as many images as you want. Choose the room name from the dropdown so guests see those photos when they book that room.
                </p>
            </div>
        </div>
        <div class="p-6 space-y-8">
            <?php if (empty($rooms)): ?>
                <div class="border-2 border-dashed border-gray-300 rounded-3xl py-12 px-8 text-center text-gray-500">
                    <i data-lucide="bed" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                    <p class="font-semibold text-gray-700">No room types found.</p>
                    <p class="text-sm mt-1">Add room types under Room Pricing first, then upload images here.</p>
                </div>
            <?php else: ?>
                <form id="roomImageUploadForm" class="bg-stone-50 border border-stone-200 rounded-2xl p-5 grid gap-4 md:grid-cols-3 md:items-end">
                    <div>
                        <label for="roomImageRoomId" class="block text-sm font-semibold text-gray-700 mb-1">Room name <span class="text-red-500">*</span></label>
                        <select id="roomImageRoomId" name="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green bg-white">
                            <option value="">Select a room…</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= ViewHelper::e($room['id']) ?>"><?= ViewHelper::e($room['type'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="roomImageFile" class="block text-sm font-semibold text-gray-700 mb-1">Image <span class="text-red-500">*</span></label>
                        <input
                            type="file"
                            id="roomImageFile"
                            name="image"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            required
                            class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-brand-green file:text-white file:font-semibold file:cursor-pointer"
                        />
                    </div>
                    <div>
                        <button type="submit" id="roomImageUploadBtn" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-green text-white font-semibold rounded-lg shadow hover:brightness-110 transition">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            Upload Image
                        </button>
                    </div>
                    <p id="roomImageUploadStatus" class="md:col-span-3 text-xs text-gray-500 min-h-[1rem]"></p>
                </form>

                <?php if (empty($normalizedRoomImages)): ?>
                    <div class="border border-dashed border-gray-300 rounded-2xl p-8 text-center text-gray-500">
                        <i data-lucide="image-plus" class="w-10 h-10 mx-auto mb-3"></i>
                        <p class="font-semibold">No room images yet.</p>
                        <p class="text-sm">Use the form above to upload your first booking photo.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($normalizedRoomImages as $item): ?>
                            <article class="room-image-card border border-stone-200 rounded-2xl overflow-hidden bg-stone-50" data-image-id="<?= ViewHelper::e($item['id']) ?>">
                                <div class="relative h-44 bg-gray-100">
                                    <img
                                        src="<?= ViewHelper::e($item['image_url']) ?>"
                                        alt="<?= ViewHelper::e($item['room_type'] ?? 'Room') ?>"
                                        class="w-full h-full object-cover"
                                    />
                                    <span class="absolute top-3 left-3 inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-black/70 text-white">
                                        <?= ViewHelper::e($item['room_type'] ?? 'Room') ?>
                                    </span>
                                </div>
                                <div class="px-4 py-3 flex items-center justify-between gap-2 bg-white border-t border-stone-200">
                                    <span class="text-xs text-gray-400">
                                        <?= ViewHelper::e(ViewHelper::formatDateTime($item['created_at'] ?? 'now', 'M d, Y')) ?>
                                    </span>
                                    <button
                                        type="button"
                                        class="room-image-delete-btn inline-flex items-center gap-2 px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition"
                                        data-image-id="<?= ViewHelper::e($item['id']) ?>"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        Delete
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="bg-white shadow-md rounded-3xl overflow-hidden">
        <div class="px-6 py-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 flex items-center gap-3">
                    <i data-lucide="images" class="w-6 h-6 text-brand-green"></i>
                    Gallery Stories
                </h2>
                <p class="text-sm text-gray-500 mt-1 max-w-3xl">
                    Publish captivating visuals paired with stories to bring the Azzeman Hotel experience to life. Every image can be categorised, narrated, and updated at any time.
                </p>
            </div>
            <button id="openAddImageBtn" class="inline-flex items-center gap-2 px-5 py-3 bg-brand-green text-white font-semibold rounded-xl shadow hover:brightness-110 transition">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Add Image Story
            </button>
        </div>

        <div class="p-6">
            <?php if (empty($normalizedItems)): ?>
                <div class="border-2 border-dashed border-gray-300 rounded-3xl py-16 px-8 text-center text-gray-500">
                    <i data-lucide="image-plus" class="w-14 h-14 mx-auto mb-4 text-gray-400"></i>
                    <h3 class="text-xl font-semibold text-gray-700">No gallery stories yet</h3>
                    <p class="mt-2 text-sm">Click below to upload your first story with an image, category and narrative.</p>
                    <button id="emptyStateAddBtn" class="mt-6 inline-flex items-center gap-2 px-5 py-3 bg-brand-green text-white font-semibold rounded-xl shadow hover:brightness-110 transition">
                        <i data-lucide="upload" class="w-5 h-5"></i>
                        Upload Image Story
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($normalizedItems as $item):
                        $excerpt = trim(strip_tags($item['content'] ?? ''));
                        if (mb_strlen($excerpt) > 140) {
                            $excerpt = mb_substr($excerpt, 0, 140) . '…';
                        }
                        ?>
                        <article class="group bg-stone-50 border border-stone-200 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition" data-gallery-id="<?= ViewHelper::e($item['id']) ?>">
                            <div class="relative h-56 overflow-hidden bg-gray-100">
                                <img src="<?= ViewHelper::e($item['image_url']) ?>" alt="<?= ViewHelper::e($item['title'] ?? 'Gallery Image') ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" onerror="this.onerror=null; this.src='<?= ViewHelper::asset('images/about-hotel.jpg') ?>';" />
                                <span class="absolute top-4 left-4 inline-flex items-center px-3 py-1 text-xs font-semibold uppercase tracking-wide rounded-full <?= $item['category_value'] ? 'bg-black/70 text-white' : 'bg-gray-300 text-gray-700' ?>">
                                    <?= ViewHelper::e($item['category_label']) ?>
                                </span>
                            </div>
                            <div class="px-6 py-5 space-y-3">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <i data-lucide="feather" class="w-4 h-4 text-brand-green"></i>
                                    <?= ViewHelper::e($item['title'] ?? 'Untitled Story') ?>
                                </h3>
                                <p class="text-sm text-gray-600 leading-relaxed min-h-[3.5rem]">
                                    <?= $excerpt ? ViewHelper::e($excerpt) : '<span class="italic text-gray-400">No story content yet.</span>' ?>
                                </p>
                                <div class="text-xs text-gray-400 flex items-center gap-2">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    <?= ViewHelper::e(ViewHelper::formatDateTime($item['created_at'] ?? 'now', 'M d, Y')) ?>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t border-stone-200 flex items-center justify-end gap-2 bg-white">
                                <button class="edit-image-btn inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 text-white text-sm font-semibold rounded-lg hover:bg-yellow-600 transition" data-gallery-id="<?= ViewHelper::e($item['id']) ?>">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                    Edit
                                </button>
                                <button class="delete-image-btn inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-lg hover:bg-red-600 transition" data-gallery-id="<?= ViewHelper::e($item['id']) ?>">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    Delete
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="bg-white shadow-md rounded-3xl overflow-hidden">
        <div class="px-6 py-6 border-b border-gray-200 flex items-center gap-3">
            <i data-lucide="layers" class="w-6 h-6 text-brand-green"></i>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Manage Categories</h2>
                <p class="text-sm text-gray-500">Organise your gallery by creating categories that guests can filter on the public site.</p>
            </div>
        </div>
        <div class="p-6 grid gap-6 lg:grid-cols-3">
            <form id="categoryForm" class="lg:col-span-1 bg-stone-50 border border-stone-200 rounded-2xl p-5 space-y-4">
                <div>
                    <label for="categoryName" class="block text-sm font-semibold text-gray-700">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" id="categoryName" name="name" required placeholder="e.g., Suites" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green" />
                </div>
                <div>
                    <label for="categoryDescription" class="block text-sm font-semibold text-gray-700">Description</label>
                    <textarea id="categoryDescription" name="description" rows="3" placeholder="Optional summary shown in the admin interface." class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green"></textarea>
                </div>
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= ViewHelper::e($csrf_token) ?>" />
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-brand-green text-white font-semibold rounded-lg shadow hover:brightness-110 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Create Category
                </button>
            </form>

            <div class="lg:col-span-2">
                <?php if (empty($categories)): ?>
                    <div class="border border-dashed border-gray-300 rounded-2xl p-8 text-center text-gray-500">
                        <i data-lucide="folder-plus" class="w-10 h-10 mx-auto mb-3"></i>
                        <p class="font-semibold">No categories yet.</p>
                        <p class="text-sm">Create your first category to start organising gallery stories.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto border border-stone-200 rounded-2xl">
                        <table class="min-w-full divide-y divide-stone-200 text-sm text-gray-700">
                            <thead class="bg-stone-100 text-xs uppercase tracking-wide text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left">Name</th>
                                    <th class="px-4 py-3 text-left">Description</th>
                                    <th class="px-4 py-3 text-left">Slug</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 bg-white">
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-900"><?= ViewHelper::e($category['name'] ?? '') ?></td>
                                        <td class="px-4 py-3 text-gray-600"><?= ViewHelper::e($category['description'] ?? '') ?: '—' ?></td>
                                        <td class="px-4 py-3 text-xs font-mono text-gray-500"><?= ViewHelper::e($category['id'] ?? '') ?></td>
                                        <td class="px-4 py-3 text-right flex items-center justify-end gap-2">
                                            <button class="edit-category-btn inline-flex items-center gap-1 px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-xs font-semibold hover:bg-yellow-600 transition" data-category-id="<?= ViewHelper::e($category['id'] ?? '') ?>">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                                Edit
                                            </button>
                                            <button class="delete-category-btn inline-flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold hover:bg-red-600 transition" data-category-id="<?= ViewHelper::e($category['id'] ?? '') ?>">
                                                <i data-lucide="trash" class="w-4 h-4"></i>
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Add Image Modal -->
<div id="addImageModal" class="fixed inset-0 hidden items-center justify-center bg-black/60 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Publish New Gallery Story</h3>
                <p class="text-sm text-gray-500 mt-1">Upload an image, select a category, and craft the accompanying story.</p>
            </div>
            <button type="button" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100" data-close-modal>
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="addGalleryForm" action="<?= ViewHelper::e($basePath) ?>/admin/gallery/upload" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 px-6 py-6 space-y-6 overflow-y-auto">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Image <span class="text-red-500">*</span></label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-green" />
                            <p class="text-xs text-gray-500 mt-1">Maximum size <?= (int) (UPLOAD_MAX_SIZE / 1024 / 1024) ?>MB. Accepted formats: JPG, PNG, WEBP, GIF.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Category <span class="text-red-500">*</span></label>
                            <select id="addStoryCategory" name="category" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green">
                                <option value="" disabled selected>Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= ViewHelper::e($category['id'] ?? '') ?>"><?= ViewHelper::e($category['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Story Title</label>
                            <input type="text" name="title" placeholder="Sunlit Executive Suite" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Story Content</label>
                        <div id="addStoryEditor" class="h-48 bg-white border border-gray-200 rounded-lg text-gray-900"></div>
                        <input type="hidden" name="content" id="addStoryContent" />
                    </div>
                </div>
            </div>
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= ViewHelper::e($csrf_token) ?>" />
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-white">
                <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100" data-close-modal>Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-green text-white font-semibold hover:brightness-110 flex items-center gap-2">
                    <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    Publish Story
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Image Modal -->
<div id="editImageModal" class="fixed inset-0 hidden items-center justify-center bg-black/60 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Update Gallery Story</h3>
                <p class="text-sm text-gray-500 mt-1">Adjust the imagery, category, or narrative for this gallery entry.</p>
            </div>
            <button type="button" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100" data-close-modal>
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="editGalleryForm" action="<?= ViewHelper::e($basePath) ?>/admin/gallery/update" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 px-6 py-6 space-y-6 overflow-y-auto">
                <input type="hidden" name="id" id="editStoryId" />
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Current Image</label>
                            <div class="mt-2 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                <img id="editImagePreview" src="<?= $placeholderImage ?>" alt="Current image preview" class="w-full h-40 object-cover" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Replace Image</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-green" />
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep the current image.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Category <span class="text-red-500">*</span></label>
                            <select id="editStoryCategory" name="category" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green">
                                <option value="" disabled>Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= ViewHelper::e($category['id'] ?? '') ?>"><?= ViewHelper::e($category['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Story Title</label>
                            <input type="text" name="title" id="editStoryTitle" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Story Content</label>
                        <div id="editStoryEditor" class="h-48 bg-white border border-gray-200 rounded-lg text-gray-900"></div>
                        <input type="hidden" name="content" id="editStoryContent" />
                    </div>
                </div>
            </div>
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= ViewHelper::e($csrf_token) ?>" />
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-white">
                <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100" data-close-modal>Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-yellow-500 text-white font-semibold hover:brightness-110 flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    window.GALLERY_ADMIN_ITEMS = <?= json_encode($normalizedItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.GALLERY_CATEGORIES = <?= json_encode($categoriesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script>
(() => {
    const csrfToken = '<?= ViewHelper::e($csrf_token) ?>';
    const csrfTokenName = '<?= CSRF_TOKEN_NAME ?>';
    const rawBase = (typeof window !== 'undefined' && typeof window.APP_BASE_PATH === 'string')
        ? window.APP_BASE_PATH
        : '<?= ViewHelper::e($basePath) ?>';
    const appUrl = <?= json_encode(rtrim(APP_URL, '/')) ?>;
    const baseUrl = appUrl || (rawBase || '').replace(/\/$/, '');

    const addModal = document.getElementById('addImageModal');
    const editModal = document.getElementById('editImageModal');
    const addForm = document.getElementById('addGalleryForm');
    const editForm = document.getElementById('editGalleryForm');
    const openAddBtn = document.getElementById('openAddImageBtn');
    const emptyStateAddBtn = document.getElementById('emptyStateAddBtn');
    const addContentInput = document.getElementById('addStoryContent');
    const editContentInput = document.getElementById('editStoryContent');
    const editStoryId = document.getElementById('editStoryId');
    const editStoryTitle = document.getElementById('editStoryTitle');
    const editStoryCategory = document.getElementById('editStoryCategory');
    const editImagePreview = document.getElementById('editImagePreview');
    const addCategorySelect = document.getElementById('addStoryCategory');
    const categoryForm = document.getElementById('categoryForm');

    if (addForm) {
        addForm.action = baseUrl + '/admin/gallery/upload';
    }
    if (editForm) {
        editForm.action = baseUrl + '/admin/gallery/update';
    }

    const addQuill = new Quill('#addStoryEditor', {
        theme: 'snow',
        placeholder: 'Describe the ambience, service, or unique story behind this moment...'
    });
    const editQuill = new Quill('#editStoryEditor', {
        theme: 'snow',
        placeholder: 'Update the narrative for this gallery story...'
    });

    const closeModal = (modal) => {
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        document.body.style.overflow = '';
    };

    const openModal = (modal) => {
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    };

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            closeModal(addModal);
            closeModal(editModal);
        });
    });

    addModal?.addEventListener('click', (event) => {
        if (event.target === addModal) closeModal(addModal);
    });
    editModal?.addEventListener('click', (event) => {
        if (event.target === editModal) closeModal(editModal);
    });

    const refreshCategorySelects = () => {
        const categories = window.GALLERY_CATEGORIES || [];
        const updateSelect = (select) => {
            if (!select) return;
            const current = select.value;
            select.innerHTML = '<option value="" disabled>Select category</option>';
            categories.forEach((category) => {
                if (!category.id) return;
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name || category.id;
                select.appendChild(option);
            });
            if (current) {
                select.value = current;
            }
        };
        updateSelect(addCategorySelect);
        updateSelect(editStoryCategory);
    };

    refreshCategorySelects();

    const populateEditModal = (id) => {
        const items = window.GALLERY_ADMIN_ITEMS || [];
        const item = items.find((entry) => String(entry.id) === String(id));
        if (!item) {
            showToast('Unable to load this gallery story. Please refresh and try again.', 'error');
            return;
        }
        editStoryId.value = item.id;
        editStoryTitle.value = item.title || '';
        const categoryValue = item.category_value || '';
        if (categoryValue && ![...editStoryCategory.options].some((opt) => opt.value === categoryValue)) {
            const option = new Option(item.category_label || categoryValue, categoryValue, true, true);
            editStoryCategory.appendChild(option);
        }
        editStoryCategory.value = categoryValue;
        editQuill.root.innerHTML = item.content || '';
        editImagePreview.src = item.image_url || '<?= $placeholderImage ?>';
        openModal(editModal);
    };

    openAddBtn?.addEventListener('click', () => {
        addForm?.reset();
        addQuill.root.innerHTML = '';
        refreshCategorySelects();
        openModal(addModal);
    });

    emptyStateAddBtn?.addEventListener('click', () => {
        openAddBtn?.click();
    });

    document.querySelectorAll('.edit-image-btn').forEach((button) => {
        button.addEventListener('click', () => populateEditModal(button.dataset.galleryId));
    });

    document.querySelectorAll('.delete-image-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.galleryId;
            if (!id) return;
            if (!confirm('Delete this gallery story? The image and story content will be removed.')) return;
            const formData = new FormData();
            formData.append('id', id);
            formData.append(csrfTokenName, csrfToken);
            try {
                const response = await requestWithFallback('/admin/gallery/delete', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => null);
                if (response.ok && data?.success) {
                    showToast('Gallery story deleted successfully.', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data?.error || 'Failed to delete gallery story.', 'error');
                }
            } catch (error) {
                showToast('A network error occurred while deleting. Please try again.', 'error');
            }
        });
    });

    const requestWithFallback = async (relativePath, options = {}) => {
        const url = baseUrl + relativePath;
        let response = await fetch(url, options);
        if (response.status === 404 && !baseUrl.endsWith('/public')) {
            const fallbackUrl = baseUrl + '/public' + relativePath;
            response = await fetch(fallbackUrl, options);
        }
        return response;
    };

    addForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        addContentInput.value = addQuill.root.innerHTML.trim();
        const formData = new FormData(addForm);
        try {
            const response = await requestWithFallback('/admin/gallery/upload', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => null);
            if (response.ok && data?.success) {
                showToast('Gallery story published successfully.', 'success');
                closeModal(addModal);
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data?.error || 'Failed to publish gallery story.', 'error');
            }
        } catch (error) {
            showToast('A network error occurred while publishing. Please try again.', 'error');
        }
    });

    editForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        editContentInput.value = editQuill.root.innerHTML.trim();
        const formData = new FormData(editForm);
        try {
            const response = await requestWithFallback('/admin/gallery/update', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => null);
            if (response.ok && data?.success) {
                showToast('Gallery story updated successfully.', 'success');
                closeModal(editModal);
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data?.error || 'Failed to update gallery story.', 'error');
            }
        } catch (error) {
            showToast('A network error occurred while updating. Please try again.', 'error');
        }
    });

    categoryForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submitBtn = categoryForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75');
        const formData = new FormData(categoryForm);
        formData.append(csrfTokenName, csrfToken);
        try {
            const response = await requestWithFallback('/admin/gallery/categories/create', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => null);
            if (response.ok && data?.success) {
                showToast('Category created successfully.', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data?.error || 'Failed to create category.', 'error');
            }
        } catch (error) {
            showToast('A network error occurred while creating the category.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-75');
        }
    });

    document.querySelectorAll('.edit-category-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.categoryId;
            const categories = window.GALLERY_CATEGORIES || [];
            const category = categories.find((cat) => cat.id === id);
            if (!category) {
                showToast('Unable to load category details.', 'error');
                return;
            }
            const name = prompt('Update category name:', category.name || '');
            if (name === null) return;
            const description = prompt('Update category description (optional):', category.description || '');
            const formData = new FormData();
            formData.append('id', id);
            formData.append('name', name.trim());
            formData.append('description', (description ?? '').trim());
            formData.append(csrfTokenName, csrfToken);
            try {
                const response = await requestWithFallback('/admin/gallery/categories/update', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => null);
                if (response.ok && data?.success) {
                    showToast('Category updated.', 'success');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast(data?.error || 'Failed to update category.', 'error');
                }
            } catch (error) {
                showToast('A network error occurred while updating the category.', 'error');
            }
        });
    });

    document.querySelectorAll('.delete-category-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.categoryId;
            if (!id) return;
            if (!confirm('Delete this category? Any gallery story using it must be reassigned first.')) return;
            const formData = new FormData();
            formData.append('id', id);
            formData.append(csrfTokenName, csrfToken);
            try {
                const response = await requestWithFallback('/admin/gallery/categories/delete', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => null);
                if (response.ok && data?.success) {
                    showToast('Category deleted.', 'success');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast(data?.error || 'Cannot delete category while stories still reference it.', 'error');
                }
            } catch (error) {
                showToast('A network error occurred while deleting the category.', 'error');
            }
        });
    });

    // Room booking images (many per room type)
    const roomImageForm = document.getElementById('roomImageUploadForm');
    const roomImageStatus = document.getElementById('roomImageUploadStatus');

    const setRoomImageStatus = (msg, isError = false) => {
        if (!roomImageStatus) return;
        roomImageStatus.textContent = msg || '';
        roomImageStatus.classList.toggle('text-red-500', !!isError);
        roomImageStatus.classList.toggle('text-gray-500', !isError);
    };

    roomImageForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const roomSelect = document.getElementById('roomImageRoomId');
        const fileInput = document.getElementById('roomImageFile');
        const uploadBtn = document.getElementById('roomImageUploadBtn');
        if (!roomSelect?.value) {
            setRoomImageStatus('Select a room name first.', true);
            return;
        }
        if (!fileInput?.files?.length) {
            setRoomImageStatus('Choose an image first.', true);
            return;
        }
        const formData = new FormData();
        formData.append('room_id', roomSelect.value);
        formData.append('image', fileInput.files[0]);
        formData.append(csrfTokenName, csrfToken);
        if (uploadBtn) uploadBtn.disabled = true;
        setRoomImageStatus('Uploading…');
        try {
            const response = await requestWithFallback('/admin/room-images/update', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => null);
            if (response.ok && data?.success) {
                setRoomImageStatus('Image uploaded.');
                showToast('Room image uploaded.', 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                setRoomImageStatus(data?.error || 'Upload failed.', true);
                showToast(data?.error || 'Failed to upload room image.', 'error');
            }
        } catch (error) {
            setRoomImageStatus('Network error. Try again.', true);
            showToast('A network error occurred while uploading.', 'error');
        } finally {
            if (uploadBtn) uploadBtn.disabled = false;
        }
    });

    document.querySelectorAll('.room-image-delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const imageId = button.dataset.imageId;
            if (!imageId || !confirm('Delete this room image?')) return;
            const formData = new FormData();
            formData.append('id', imageId);
            formData.append(csrfTokenName, csrfToken);
            button.disabled = true;
            try {
                const response = await requestWithFallback('/admin/room-images/delete', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => null);
                if (response.ok && data?.success) {
                    showToast('Room image deleted.', 'success');
                    const card = button.closest('.room-image-card');
                    card?.remove();
                } else {
                    showToast(data?.error || 'Failed to delete room image.', 'error');
                    button.disabled = false;
                }
            } catch (error) {
                showToast('A network error occurred while deleting.', 'error');
                button.disabled = false;
            }
        });
    });

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
</script>

