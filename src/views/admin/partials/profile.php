<?php
// Admin Profile
$user = isset($user) ? $user : [];
$basePath = isset($basePath) ? $basePath : '';
$csrf_token = isset($csrf_token) ? $csrf_token : '';
?>
<div class="bg-white shadow-md rounded-lg max-w-2xl mx-auto">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i data-lucide="user" class="w-6 h-6 mr-3 text-brand-green"></i> Admin Profile
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage your administrator account settings.</p>
    </div>
    <form onsubmit="updateProfile(event)" class="p-6 space-y-6">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <div class="mt-1">
                <input
                    id="username"
                    type="text"
                    value="<?= ViewHelper::e($user['username'] ?? '') ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold bg-white text-gray-900"
                />
            </div>
        </div>
        
        <div class="border-t pt-6">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i data-lucide="lock" class="w-5 h-5 mr-2 text-brand-green"></i>Change Password
            </h3>
            <p class="text-sm text-gray-500 mt-1">To change your password, enter your current and new passwords below.</p>
        </div>

        <div class="space-y-4">
            <div>
                <label for="currentPassword" class="block text-sm font-medium text-gray-700">Current Password</label>
                <div class="mt-1 relative">
                    <input
                        id="currentPassword"
                        name="current-password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold bg-white text-gray-900"
                    />
                    <button type="button" onclick="togglePassword('currentPassword')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i data-lucide="eye" class="w-5 h-5" id="currentPasswordIcon"></i>
                    </button>
                </div>
            </div>
            <div>
                <label for="newPassword" class="block text-sm font-medium text-gray-700">New Password</label>
                <div class="mt-1 relative">
                    <input
                        id="newPassword"
                        name="new-password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold bg-white text-gray-900"
                    />
                    <button type="button" onclick="togglePassword('newPassword')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i data-lucide="eye" class="w-5 h-5" id="newPasswordIcon"></i>
                    </button>
                </div>
            </div>
            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <div class="mt-1 relative">
                    <input
                        id="confirmPassword"
                        name="new-password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-gold focus:border-brand-gold bg-white text-gray-900"
                    />
                    <button type="button" onclick="togglePassword('confirmPassword')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i data-lucide="eye" class="w-5 h-5" id="confirmPasswordIcon"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-end">
            <button
                type="submit"
                id="profileSubmitBtn"
                class="flex justify-center items-center py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-gold hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-gold disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
                <i data-lucide="save" class="w-5 h-5 mr-2"></i>
                <span id="profileSubmitText">Save Changes</span>
            </button>
        </div>
    </form>
</div>

<script>
    const passwordVisibility = {
        currentPassword: false,
        newPassword: false,
        confirmPassword: false,
    };

    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + 'Icon');
        passwordVisibility[fieldId] = !passwordVisibility[fieldId];
        
        if (passwordVisibility[fieldId]) {
            field.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            field.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }

    async function updateProfile(event) {
        event.preventDefault();
        
        const btn = document.getElementById('profileSubmitBtn');
        const btnText = document.getElementById('profileSubmitText');
        btn.disabled = true;
        btnText.textContent = 'Saving...';
        
        const currentPassword = document.getElementById('currentPassword').value;
        const newUsername = document.getElementById('username').value.trim();
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validation
        if (!currentPassword) {
            showToast('Current password is required to make changes.', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save Changes';
            return;
        }
        
        if (newPassword && newPassword.length < 8) {
            showToast('New password must be at least 8 characters.', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save Changes';
            return;
        }
        
        if (newPassword && newPassword !== confirmPassword) {
            showToast('New passwords do not match.', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save Changes';
            return;
        }
        
        // Check if there are any actual changes
        const currentUsername = '<?= ViewHelper::e($user['username'] ?? '') ?>';
        if (newUsername === currentUsername && !newPassword) {
            showToast('No changes were made.', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save Changes';
            return;
        }
        
        const formData = new FormData();
        formData.append('current_password', currentPassword);
        formData.append('new_username', newUsername);
        if (newPassword) {
            formData.append('new_password', newPassword);
        }
        formData.append(csrfTokenName, csrfToken);
        
        try {
            const response = await fetch('<?= $basePath ?>/admin/profile/update', {
                method: 'POST',
                body: formData,
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showToast(data.message || 'Profile updated successfully', 'success');
                // Clear password fields
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to update profile', 'error');
                btn.disabled = false;
                btnText.textContent = 'Save Changes';
            }
        } catch (error) {
            showToast('An error occurred while updating the profile.', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save Changes';
        }
    }

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

