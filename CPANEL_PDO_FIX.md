# Fixing "could not find driver" Error on cPanel

## Problem

You're getting this error:
```
Database connection failed: could not find driver
```

This means the **PDO MySQL extension** (`pdo_mysql`) is not installed or enabled on your cPanel server.

## Solution: Enable PDO MySQL Extension

### Method 1: Using PHP Selector (Recommended)

1. **Log in to cPanel**
   - Go to your cPanel dashboard

2. **Open PHP Selector**
   - Look for **"Select PHP Version"** or **"PHP Selector"** in the Software section
   - Click on it

3. **Select PHP Version**
   - Choose PHP version **7.4 or higher** (recommended: 8.0 or 8.1)
   - Click **"Set as current"** or **"Apply"**

4. **Enable Extensions**
   - Click on the **"Extensions"** tab
   - Scroll down and find **`pdo_mysql`**
   - Check the box to enable it
   - Also ensure **`pdo`** is enabled (it should be by default)
   - Click **"Save"** or **"Apply"**

5. **Verify**
   - Visit: `https://yourdomain.com/check_php.php?key=azzeman_diagnostic_2024`
   - You should see ✅ next to "PDO MySQL Driver"

### Method 2: Using .htaccess (If PHP Selector is not available)

1. **Create or edit `.htaccess` file**
   - Navigate to your `public_html` directory in cPanel File Manager
   - Create or edit `.htaccess` file

2. **Add PHP extension directive**
   - Add this line at the top of your `.htaccess`:
   ```apache
   php_value extension pdo_mysql
   ```

3. **Note**: This method may not work on all hosting providers. If it doesn't work, use Method 1 or contact your hosting provider.

### Method 3: Contact Your Hosting Provider

If the above methods don't work:

1. Contact your hosting provider's support
2. Ask them to enable the `pdo_mysql` extension for your account
3. Provide them with this information:
   - Extension needed: `pdo_mysql`
   - PHP version: 7.4 or higher
   - Purpose: Database connectivity for PHP application

## Verify the Fix

After enabling the extension:

1. **Run the diagnostic script:**
   ```
   https://yourdomain.com/check_php.php?key=azzeman_diagnostic_2024
   ```

2. **Check for green checkmarks:**
   - ✅ PDO (PHP Data Objects) is installed
   - ✅ PDO MySQL Driver is available

3. **Test your application:**
   - Visit your website
   - The database connection error should be gone
   - UI components should load properly

## Additional Troubleshooting

### If UI components still aren't loading:

1. **Check asset paths:**
   - Open browser Developer Tools (F12)
   - Go to Network tab
   - Look for 404 errors on CSS/JS files
   - Verify `APP_URL` in `config.php` is set correctly

2. **Check file permissions:**
   - Ensure `assets/` directory has read permissions (755)
   - Ensure files in `assets/` have read permissions (644)

3. **Check .htaccess:**
   - Ensure `.htaccess` in `public/` directory allows access to assets
   - The file should have rules to allow direct access to `.css`, `.js`, and image files

4. **Clear browser cache:**
   - Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)

## Required PHP Extensions

Make sure these extensions are enabled:
- ✅ `pdo` - PHP Data Objects
- ✅ `pdo_mysql` - PDO MySQL Driver
- ✅ `mysqli` - MySQLi Extension (backup)
- ✅ `mbstring` - Multibyte String
- ✅ `json` - JSON
- ✅ `session` - Session
- ✅ `gd` - GD Image Library (for image processing)
- ✅ `curl` - cURL (for API calls)
- ✅ `openssl` - OpenSSL (for secure connections)

## Security Note

After fixing the issue, **delete the diagnostic script**:
- File: `public/check_php.php`
- Or protect it with a strong secret key

## Still Having Issues?

If you're still experiencing problems:

1. Check the error logs in cPanel
2. Verify your `config.php` database settings are correct
3. Ensure your database exists and user has proper permissions
4. Contact your hosting provider for assistance


