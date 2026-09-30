<footer class="bg-brand-green text-white">
    <div class="container mx-auto px-6 py-12">
        <div class="grid md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <div class="mb-4 footer-logo">
                    <img src="<?= ViewHelper::asset('images/logo.png') ?>" alt="Azzeman Hotel Logo" class="h-24 w-auto" onerror="this.onerror=null; this.src='<?= ViewHelper::asset('images/logo.png') ?>';">
                </div>
                <p class="text-gray-300 max-w-md">
                    Experience the pinnacle of luxury and comfort at Azzeman Hotel, your home away from home in the vibrant heart of the city.
                </p>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="#about" onclick="scrollToSection('about'); return false;" class="text-gray-300 hover:text-white cursor-pointer">About Us</a></li>
                    <li><a href="#rooms" onclick="scrollToSection('rooms'); return false;" class="text-gray-300 hover:text-white cursor-pointer">Rooms</a></li>
                    <li><a href="#gallery" onclick="scrollToSection('gallery'); return false;" class="text-gray-300 hover:text-white cursor-pointer">Gallery</a></li>
                    <li><a href="#services" onclick="scrollToSection('services'); return false;" class="text-gray-300 hover:text-white cursor-pointer">Services</a></li>
                    <li><a href="#meetings" onclick="scrollToSection('meetings'); return false;" class="text-gray-300 hover:text-white cursor-pointer">Meetings & Events</a></li>
                    <li><a href="#contact" onclick="scrollToSection('contact'); return false;" class="text-gray-300 hover:text-white cursor-pointer">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-4">Follow Us</h4>
                <div class="flex space-x-4">
                    <a href="#" aria-label="Facebook" class="text-gray-300 hover:text-white">
                        <i data-lucide="facebook" class="w-6 h-6"></i>
                    </a>
                    <a href="#" aria-label="X (Twitter)" class="text-gray-300 hover:text-white">
                        <img src="https://cdn.simpleicons.org/x/ffffff" alt="X" class="w-6 h-6" />
                    </a>
                    <a href="#" aria-label="Instagram" class="text-gray-300 hover:text-white">
                        <i data-lucide="instagram" class="w-6 h-6"></i>
                    </a>
                    <a href="#" aria-label="TikTok" class="text-gray-300 hover:text-white">
                        <img src="https://cdn.simpleicons.org/tiktok/ffffff" alt="TikTok" class="w-6 h-6" />
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-12 border-t border-white/20 pt-8 text-center text-gray-400">
            <p>&copy; <?= date('Y') ?> Azzeman Hotel. All Rights Reserved.</p>
            <p class="mt-2">Developed by <a href="https://yenoral.com" target="_blank" rel="noopener noreferrer" class="underline hover:no-underline">Yenoral</a></p>
        </div>
    </div>
</footer>

<!-- Initialize Lucide icons -->
<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
</body>
</html>
