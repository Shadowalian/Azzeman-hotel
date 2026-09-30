<?php
$basePath = $basePath ?? '';
$basePath = defined('APP_URL') && APP_URL !== 'https://yourdomain.com'
    ? rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/')
    : '';
if (empty($basePath) || strpos($_SERVER['REQUEST_URI'], '/azzemanhotel_PHP') !== false) {
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $basePath = rtrim($scriptPath, '/');
}
$loginAction = $basePath . '/admin-login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ViewHelper::e($title ?? 'Admin Login - Azzeman Hotel') ?></title>
    <link rel="icon" type="image/png" href="<?= ViewHelper::asset('images/logo.png') ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="<?= ViewHelper::asset('css/admin.css') ?>?v=2" rel="stylesheet">
</head>
<body class="admin-azzeman">
    <div class="admin-login">
        <div class="admin-login-inner">
            <div class="admin-tibeb" aria-hidden="true"></div>
            <div class="admin-login-brand">
                <a href="<?= ViewHelper::e($basePath) ?>/">
                    <img src="<?= ViewHelper::asset('images/logo.png') ?>" alt="Azzeman Hotel">
                </a>
                <div class="tag">Administrator</div>
                <h1>Azzeman Hotel</h1>
                <p>Sign in to manage bookings, gallery, and site content.</p>
            </div>

            <div class="admin-card">
                <h2>Admin sign in</h2>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="admin-alert"><?= ViewHelper::e($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <form method="POST" action="<?= ViewHelper::e($loginAction) ?>">
                    <?= AuthHelper::csrfField() ?>
                    <div class="admin-field">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" autocomplete="username" required>
                    </div>
                    <div class="admin-field">
                        <label for="password">Password</label>
                        <div class="admin-field-wrap">
                            <input id="password" name="password" type="password" autocomplete="current-password" required style="padding-right:44px">
                            <button type="button" class="eye" onclick="togglePassword()" aria-label="Toggle password visibility">
                                <i data-lucide="eye" id="passwordIcon" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div style="margin-top:22px">
                        <button type="submit" class="admin-btn" id="submitBtn">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            <span id="submitText">Sign in</span>
                        </button>
                    </div>
                </form>
                <p class="admin-login-foot" style="margin-top:18px;margin-bottom:0">For access, contact the IT department.</p>
            </div>

            <p class="admin-login-foot"><a href="<?= ViewHelper::e($basePath) ?>/">&larr; Back to main site</a></p>
        </div>
    </div>
    <script>
        let showPassword = false;
        function togglePassword() {
            showPassword = !showPassword;
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            passwordInput.type = showPassword ? 'text' : 'password';
            passwordIcon.setAttribute('data-lucide', showPassword ? 'eye-off' : 'eye');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
        document.querySelector('form').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('submitText');
            btn.disabled = true;
            text.textContent = 'Signing in…';
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
