<?php include __DIR__ . '/partials/header.php'; ?>

<?php 
$gallery_categories = $categories ?? [];
include __DIR__ . '/partials/navbar.php'; 

$resolveImageUrl = function ($path) {
    $fallback = ViewHelper::asset('images/about-hotel.jpg');
    if (!$path) {
        return $fallback;
    }
    if (preg_match('#^https?://#', $path) === 1) {
        return $path; // Return external URLs as-is
    }

    // Fix legacy broken paths
    if (strpos($path, 'GALLERY_UPLOAD_URL') !== false) {
        $path = str_replace('GALLERY_UPLOAD_URL', GALLERY_UPLOAD_URL, $path);
    }

    // Return the asset URL - let the browser handle missing images with onerror
    $relativePath = ltrim($path, '/');
    return ViewHelper::asset($relativePath);
};

$heroImage = $resolveImageUrl($images[0]['image_path'] ?? $fallback_image ?? null);
$categoryName = $category['name'] ?? 'Gallery';
?>

<main class="bg-stone-50 min-h-screen flex flex-col">
    <header class="relative pt-32 pb-16 md:pt-40 md:pb-24 text-center text-white overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center filter blur-sm scale-110" style="background-image: url('<?= ViewHelper::e($heroImage) ?>');"></div>
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 container mx-auto px-6">
            <h1 class="text-4xl md:text-6xl font-bold font-sans drop-shadow-lg"><?= ViewHelper::e($categoryName) ?></h1>
            <p class="text-lg md:text-xl mt-4 max-w-3xl mx-auto text-white/85">
                <?= ViewHelper::e($category['description'] ?? "Discover curated moments from our " . strtolower($categoryName) . " collection.") ?>
            </p>
        </div>
    </header>
    <!-- DEBUG: Category ID: <?= ViewHelper::e($category['id']) ?>, Slug: <?= ViewHelper::e($categorySlug ?? 'N/A') ?>, Name: <?= ViewHelper::e($category['name']) ?> -->

    <section id="gallery" class="py-16 md:py-24">
        <div class="container mx-auto px-6">
            <?php if (!empty($images)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6" id="galleryGrid">
                    <?php foreach ($images as $image): 
                        $imageUrl = $resolveImageUrl($image['image_path'] ?? '');
                    ?>
                        <div class="relative group cursor-pointer rounded-xl overflow-hidden shadow-lg transform transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl" data-image-id="<?= ViewHelper::e($image['id']) ?>" data-image-src="<?= ViewHelper::e($imageUrl) ?>" role="button" tabindex="0" aria-label="View story for <?= ViewHelper::e($image['title'] ?? 'gallery image') ?>" style="background-image:url('<?= ViewHelper::e($imageUrl) ?>'); background-size:cover; background-position:center;">
                            <img src="<?= ViewHelper::e($imageUrl) ?>" data-src="<?= ViewHelper::e($imageUrl) ?>" alt="<?= ViewHelper::e($image['title'] ?? 'Gallery Image') ?>" class="w-full h-72 object-cover transition-all duration-500 transform group-hover:scale-105 group-hover:brightness-90" loading="eager" onerror="this.onerror=null; this.src='<?= ViewHelper::asset('images/about-hotel.jpg') ?>';" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition ease-in-out duration-300 flex flex-col justify-end p-4">
                                <?php if (!empty($image['title'])): ?>
                                    <h3 class="text-white text-lg font-semibold mb-1"><?= ViewHelper::e($image['title']) ?></h3>
                                <?php endif; ?>
                                <span class="mt-4 inline-flex items-center text-white text-sm font-medium">
                                    View Story
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16 text-gray-500">
                    <p>There are currently no images in the <strong><?= ViewHelper::e($categoryName) ?></strong> collection.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Modal / Lightbox (shared with main gallery) -->
<div id="galleryModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm items-center justify-center hidden z-[80]">
    <div class="relative bg-white rounded-3xl shadow-2xl max-w-4xl w-full mx-4 overflow-hidden transform transition-all duration-300 scale-95 opacity-0" data-modal-card>
        <button id="closeGalleryModal" class="absolute top-4 right-4 bg-gray-900 text-white rounded-full w-10 h-10 flex items-center justify-center text-2xl hover:bg-gray-700 transition" aria-label="Close">&times;</button>
        <div class="flex flex-col">
            <div class="overflow-hidden">
                <img id="modalImage" src="" alt="Gallery Item" class="w-full max-h-[480px] object-cover transition-all duration-500">
            </div>
            <div class="p-6 md:p-8 space-y-4">
                <h2 id="modalTitle" class="text-2xl md:text-3xl font-semibold text-gray-900"></h2>
                <div id="modalContent" class="prose prose-stone max-w-none text-gray-700 leading-relaxed"></div>
            </div>
            <div class="flex items-center justify-between px-6 md:px-8 pb-6 text-sm text-gray-500">
                <button id="prevImage" class="inline-flex items-center gap-2 text-gray-600 hover:text-brand-green transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                </button>
                <button id="nextImage" class="inline-flex items-center gap-2 text-gray-600 hover:text-brand-green transition">
                    Next
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
    window.GALLERY_BLOG_ENDPOINT = (window.APP_BASE_PATH || '') + '/get_image_blog.php';
    window.GALLERY_IMAGE_IDS = <?= json_encode(array_column($images, 'id')) ?>;
</script>
<script src="<?= ViewHelper::asset('js/gallery.js') ?>" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

