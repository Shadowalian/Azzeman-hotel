# Azzeman Hotel - cPanel Deployment Guide

Complete step-by-step instructions for deploying the Azzeman Hotel PHP application to cPanel hosting.

## 📋 Pre-Deployment Checklist

Before uploading, ensure you have:
- [ ] cPanel hosting account with PHP 7.4+ and MySQL 5.7+
- [ ] **PDO MySQL extension enabled** (see Step 0 below)
- [ ] Google Gemini API key (for chatbot) - Get from https://aistudio.google.com/app/apikey
- [ ] SMTP email credentials (Gmail, SendGrid, or your email provider)
- [ ] Domain name configured in cPanel
- [ ] FTP/cPanel File Manager access

## ⚙️ Step 0: Enable Required PHP Extensions

**IMPORTANT:** Before deploying, ensure the PDO MySQL extension is enabled on your cPanel server.

### 0.1 Enable PDO MySQL Extension

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

### 0.2 Verify Extensions (After Upload)

After uploading files, verify extensions are enabled:

1. Upload the diagnostic script: `public/check_php.php`
2. Visit: `https://yourdomain.com/check_php.php?key=azzeman_diagnostic_2024`
3. Check for green checkmarks next to:
   - ✅ PDO (PHP Data Objects) is installed
   - ✅ PDO MySQL Driver is available

**If you see errors:** See `CPANEL_PDO_FIX.md` for detailed troubleshooting steps.

**Note:** After verification, delete `check_php.php` for security.

## 🚀 Step 1: Prepare and Package Files for Upload

We have provided a packaging script `scripts/package_cpanel.php` that automatically filters, prepares, and zips the entire project into a clean, deployable file named `azzemanhotel_cpanel.zip` in your root directory.

### 1.1 Pre-Configured Dynamic Configuration
The application now uses a dynamic, environment-aware `config.php` file. 
- When run locally (`localhost`, `127.0.0.1`), it automatically uses local database settings and URLs.
- When uploaded to the server (`azzemanhotel.com`), it automatically switches to your production database credentials (`azzemanhotel_sinqtech` / `DWr5zpxCsdn!3xHl`) and production URL (`https://azzemanhotel.com`).
- Critical settings like SMTP (Gmail & Custom domain) and API keys (OpenRouter & Brevo) are already pre-configured for both environments.

### 1.2 Generate the cPanel Zip Package
1. Open a terminal in the root directory.
2. Run: `php scripts/package_cpanel.php`
3. This creates a clean `azzemanhotel_cpanel.zip` in your project root, automatically excluding local logs, cache, temporary files, and development tools.

## 📤 Step 2: Upload Files to cPanel

### Option A: Upload to Root Domain (Recommended)

**If you want the site at `https://azzemanhotel.com`:**

1. **Via cPanel File Manager:**
   - Log into cPanel
   - Open "File Manager"
   - Navigate to the `public_html` folder
   - Upload the generated `azzemanhotel_cpanel.zip` file directly to `public_html`
   - Extract the zip file in `public_html`
   - Move all files from the extracted folder structure directly into `public_html` if they were extracted into a subdirectory. The `index.php` and `.htaccess` must reside in the root of `public_html`.

2. **Via FTP:**
   - Connect to your server via FTP
   - Navigate to the `public_html` directory
   - Upload the contents of `azzemanhotel_cpanel.zip` (extract locally first, then upload, or upload the zip and extract in cPanel File Manager).

### Option B: Upload to Subdirectory

**If you want the site at `https://yourdomain.com/hotel`:**

1. Create a folder `hotel` in `public_html`
2. Upload all files from `azzemanhotel_PHP` to `public_html/hotel/`
3. Update `APP_URL` in `config.php` to include the subdirectory

### 2.1 File Structure After Upload

After uploading, your `public_html` should look like this:

```
public_html/
├── index.php              (front controller - in root)
├── .htaccess              (URL rewriting - in root)
├── config.php             (we'll create this - in root)
├── config.sample.php      (in root)
├── azzemanhotel_mysql.sql (in root)
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/           (create this folder)
│       └── site-images/   (create this subfolder)
├── logs/                   (create this folder)
├── src/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── services/
│   └── helpers/
├── tests/
└── ... (other files)
```

**Important:** The `index.php` and `.htaccess` files are in the **root** of the project, not in a `public/` subdirectory. Upload everything directly to `public_html/`.

## 🗄️ Step 3: Database Setup in cPanel

## 🗄️ Step 3: Database Setup in cPanel

### 3.1 Use Existing Database & User
Since you already have the database and user set up on cPanel:
- Database Name: `azzemanhotel_sinq`
- Database User: `azzemanhotel_sinqtech`
- Database Password: `DWr5zpxCsdn!3xHl`

Ensure the user `azzemanhotel_sinqtech` has **ALL PRIVILEGES** assigned to the `azzemanhotel_sinq` database. (If not, scroll down in the cPanel **"MySQL Databases"** screen to **"Add User to Database"**, select the user and database, click **"Add"**, check **"ALL PRIVILEGES"** and save).

### 3.2 Import Database Schema

1. In cPanel, go to **"phpMyAdmin"**
2. Click on your database name in the left sidebar
3. Click **"Import"** tab at the top
4. Click **"Choose File"** and select `azzemanhotel_mysql.sql`
5. Scroll down and click **"Go"**
6. Wait for import to complete (should see "Import has been successfully finished")

**Verify Import:**
- You should see tables: `admin_users`, `rooms`, `booking_rooms`, `spa_bookings`, `meeting_bookings`, `gallery_categories`, `gallery_images`, `site_images`, `virtual_tours`, `hotspots`
- Check `admin_users` table has one user: `azzeman_admin`

## ⚙️ Step 4: Configure Application

### 4.1 Automated Environment Detection (No Action Required)

The application has been upgraded with a dynamic `config.php` file that is already pre-configured with the production database credentials and server settings. 

When the website is loaded in the browser:
- If the host is **localhost** (local testing), it connects to local MySQL (no password) and sets environment to `development` (errors visible).
- If the host is **azzemanhotel.com** (live website), it automatically connects to your production cPanel database (`azzemanhotel_sinqtech` / `DWr5zpxCsdn!3xHl`) and sets the environment to `production` (errors hidden for security).

### 4.2 Updating Configuration (Optional)

If you ever need to change your database credentials, API keys, or email settings in the future:

1. Log into your cPanel dashboard.
2. Open **File Manager** and navigate to `public_html`.
3. Right-click on `config.php` and click **"Edit"**.
4. Modify the required constants in the editor:

```php
// Database Configuration (Production values)
define('DB_USER', 'azzemanhotel_sinqtech');
define('DB_PASS', 'DWr5zpxCsdn!3xHl');

// Application URL
define('APP_URL', 'https://azzemanhotel.com');
```

5. Click **"Save Changes"**.

### 4.3 Configure Google Gemini API (Optional)

The Gemini API (used for image generation) is pre-configured with your API key. If you need to rotate or change this key in the future:
1. Get a new API key from https://aistudio.google.com/app/apikey
2. Edit `config.php` and replace:
   ```php
   define('GEMINI_API_KEY', 'your_new_key_here');
   ```

### 4.4 Configure OpenRouter API (Optional)
The chatbot utilizes the free OpenRouter tier using your API key. To change the key:
1. Edit `config.php` and replace:
   ```php
   define('OPENROUTER_API_KEY', 'your_new_key_here');
   ```

### 4.5 Configure SMTP Email & Brevo API (Optional)
Email settings (SMTP1 for Gmail, SMTP2 for custom domain, and Brevo API for transactional approval/rejection emails) are fully pre-configured in `config.php` using the credentials you provided. You can modify these in the `config.php` file if you change mail servers.


## 📁 Step 5: Set File Permissions

### 5.1 Via cPanel File Manager

1. Navigate to `public_html` in File Manager
2. Set permissions for these folders/files:

**Folders (755):**
- `assets/uploads/` → Right-click → **"Change Permissions"** → Set to `755`
- `logs/` → Right-click → **"Change Permissions"** → Set to `755`

**Files (644):**
- `config.php` → Right-click → **"Change Permissions"** → Set to `644`

### 5.2 Create Required Directories

If `assets/uploads/` or `logs/` don't exist:

1. In File Manager, navigate to `assets/`
2. Click **"Create Folder"** → Name: `uploads`
3. Inside `uploads/`, create folder: `site-images`
4. Go back to `public_html/`
5. Click **"Create Folder"** → Name: `logs`
6. Set permissions to `755` for both folders

## 🔧 Step 6: Verify .htaccess

### 6.1 Check .htaccess Exists

1. In File Manager, ensure `.htaccess` file exists in `public_html/` (root directory)
2. If missing, create it with the content from the project's `.htaccess` file
3. **Important:** The `.htaccess` file must be in the same directory as `index.php`

### 6.2 Verify mod_rewrite

1. In cPanel, go to **"Select PHP Version"** or **"PHP Configuration"**
2. Ensure `mod_rewrite` is enabled (usually enabled by default)
3. If not enabled, contact your hosting provider

### 6.3 Verify .htaccess is Working

After upload, test if URL rewriting works:
- Visit: `https://yourdomain.com/` (should load homepage)
- Visit: `https://yourdomain.com/admin-login` (should load admin login)
- Visit: `https://yourdomain.com/gallery` (should load gallery page)

If you see "Page not found" errors, check:
- `.htaccess` file exists in root
- `mod_rewrite` is enabled
- File permissions are correct

## ✅ Step 7: Testing

### 7.1 Test Database Connection

1. Visit: `https://yourdomain.com/tests/test_env.php`
2. Should return JSON:
```json
{
    "database": true,
    "session": true,
    "config": true
}
```

If you see `"database": false`, check:
- Database credentials in `config.php`
- Database user has proper privileges
- Database name is correct (with cPanel prefix)

### 7.2 Test Homepage

1. Visit: `https://yourdomain.com/`
2. Homepage should load with:
   - Hero section with background image
   - Navigation bar
   - All sections visible
   - Footer at bottom

### 7.3 Test Admin Login

1. Visit: `https://yourdomain.com/admin-login`
2. Login with:
   - **Username:** `azzeman_admin`
   - **Password:** `SecurePass!2024`
3. Should redirect to admin dashboard

**⚠️ IMPORTANT:** Change the admin password immediately after first login!

### 7.4 Test Chatbot

1. On homepage, click the chatbot button (bottom right)
2. Type a message
3. Should receive a response (if API key is configured)

If chatbot doesn't work:
- Check `GEMINI_API_KEY` in `config.php`
- Verify API key is valid
- Check error logs: `logs/php_errors.log`

### 7.5 Test Booking Forms

1. Click **"Book Your Stay"** button
2. Fill out the form and submit
3. Should see success message
4. Check admin dashboard for new booking
5. Check email inbox (including spam) for confirmation

### 7.6 Test Gallery

1. Visit: `https://yourdomain.com/gallery`
2. Images should display in masonry layout
3. Click an image to open lightbox

## 🔐 Step 8: Security Hardening

### 8.1 Protect config.php

The `.htaccess` file should already protect `config.php`, but verify:

```apache
<FilesMatch "^(config\.php|\.env)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 8.2 Change Default Admin Password

1. Log into admin dashboard
2. Go to **"Profile"** tab
3. Change password to a strong, unique password
4. Save changes

### 8.3 Verify Error Reporting is Off

In `config.php`, ensure:
```php
define('APP_DEBUG', false);
error_reporting(0);
ini_set('display_errors', 0);
```

### 8.4 Set Up SSL Certificate

1. In cPanel, go to **"SSL/TLS Status"**
2. Install SSL certificate (Let's Encrypt is free)
3. Force HTTPS redirect (optional, in `.htaccess`):

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 📧 Step 9: Email Configuration (Optional but Recommended)

### 9.1 Test Email Sending

1. Submit a test booking
2. Check `ADMIN_EMAIL` inbox for confirmation
3. Check spam folder if not in inbox

### 9.2 Troubleshoot Email Issues

If emails aren't sending:

1. **Check SMTP credentials** in `config.php`
2. **For Gmail:** Ensure App Password is used (not regular password)
3. **Check error logs:** `logs/php_errors.log`
4. **Test with PHPMailer:** Some hosts require PHPMailer library

**To install PHPMailer (if needed):**
1. Download PHPMailer from https://github.com/PHPMailer/PHPMailer
2. Extract to `vendor/PHPMailer/` directory
3. The EmailService will automatically use it

## 🐛 Step 10: Troubleshooting Common Issues

### Issue: "Page not found" or 404 errors

**Solution:**
1. Verify `.htaccess` file exists in `public_html/`
2. Check `mod_rewrite` is enabled
3. Verify `index.php` exists in `public_html/`
4. Check Apache error logs in cPanel

### Issue: Database connection error

**Solution:**
1. Verify database credentials in `config.php`
2. Check database name includes cPanel prefix (e.g., `cpaneluser_azzeman_hotel`)
3. Verify database user has ALL PRIVILEGES
4. Test connection in phpMyAdmin

### Issue: Images not loading

**Solution:**
1. Check `assets/images/` folder exists and has correct permissions (755)
2. Verify image paths in database (check `site_images` table)
3. Check browser console for 404 errors
4. Ensure `APP_URL` is correct in `config.php`

### Issue: File uploads not working

**Solution:**
1. Check `assets/uploads/` folder exists
2. Verify folder permissions are `755`
3. Check PHP `upload_max_filesize` in cPanel PHP settings
4. Verify `UPLOAD_MAX_SIZE` in `config.php` matches PHP limits

### Issue: Chatbot not responding

**Solution:**
1. Verify `GEMINI_API_KEY` is set in `config.php`
2. Check API key is valid at https://aistudio.google.com/app/apikey
3. Review error logs: `logs/php_errors.log`
4. Test API endpoint: `https://yourdomain.com/tests/test_chatbot.php`

### Issue: Admin login not working

**Solution:**
1. Verify admin user exists in database:
   ```sql
   SELECT * FROM admin_users WHERE username = 'azzeman_admin';
   ```
2. If user doesn't exist, run this SQL:
   ```sql
   INSERT INTO admin_users (username, password_hash) 
   VALUES ('azzeman_admin', '$2y$10$wsOzIXnXDNs0XNSpQXfD4O/5U6lQdXbvcJqt96leaAV09sISPXGgW');
   ```
   (Password: `SecurePass!2024`)
3. Clear browser cookies and try again

### Issue: CSS/JS not loading

**Solution:**
1. Check `assets/` folder exists and has correct permissions
2. Verify `APP_URL` in `config.php` matches your domain
3. Check browser console for 404 errors
4. Clear browser cache (Ctrl+F5)

## 📝 Step 11: Post-Deployment Checklist

After deployment, verify:

- [ ] Homepage loads correctly
- [ ] Navigation works (smooth scroll to sections)
- [ ] Gallery displays images correctly
- [ ] Room booking form submits successfully
- [ ] Spa booking form submits successfully
- [ ] Meeting booking form submits successfully
- [ ] Admin login works
- [ ] Admin dashboard displays bookings
- [ ] Can update booking status in admin
- [ ] Can delete bookings in admin
- [ ] Gallery management works (upload/delete images)
- [ ] Site images can be updated in admin
- [ ] Virtual tour can be configured in admin
- [ ] Profile can be updated in admin
- [ ] Chatbot responds (if API key configured)
- [ ] Email notifications are sent (check spam folder)
- [ ] Favicon displays in browser tab
- [ ] All images load correctly
- [ ] Mobile responsive design works
- [ ] No console errors in browser

## 🔄 Step 12: Regular Maintenance

### 12.1 Backup Database

**Weekly backups recommended:**

1. In cPanel, go to **"Backup"**
2. Click **"Download a MySQL Database Backup"**
3. Select your database
4. Download and store securely

Or use phpMyAdmin:
1. Select your database
2. Click **"Export"** tab
3. Choose **"Quick"** or **"Custom"** method
4. Click **"Go"** to download

### 12.2 Monitor Error Logs

1. Check `logs/php_errors.log` regularly
2. Review cPanel error logs
3. Fix any recurring errors

### 12.3 Update Admin Password

Change admin password every 3-6 months for security.

## 📞 Support & Resources

### Error Logs Location

- Application logs: `logs/php_errors.log`
- Apache logs: cPanel → **"Errors"** or **"Error Log"**
- PHP errors: cPanel → **"Select PHP Version"** → **"Error Reporting"**

### Useful cPanel Tools

- **File Manager**: Manage files and permissions
- **phpMyAdmin**: Database management
- **Error Log**: View Apache/PHP errors
- **Select PHP Version**: Check PHP version and extensions
- **Cron Jobs**: Set up automated tasks (if needed)

### Testing Endpoints

- Health check: `https://yourdomain.com/api/health`
- Database check: `https://yourdomain.com/api/health/db`
- Environment test: `https://yourdomain.com/tests/test_env.php`
- Chatbot test: `https://yourdomain.com/tests/test_chatbot.php`

## 🎉 Deployment Complete!

Your Azzeman Hotel website should now be live and fully functional on cPanel!

**Default Admin Credentials:**
- URL: `https://yourdomain.com/admin-login`
- Username: `azzeman_admin`
- Password: `SecurePass!2024`

**⚠️ Remember to change the admin password immediately after first login!**

---

## Quick Reference: Configuration Values

When setting up `config.php`, you'll need:

1. **Database:**
   - Host: `localhost`
   - Name: `azzemanhotel_sinq`
   - User: `azzemanhotel_sinqtech`
   - Password: `DWr5zpxCsdn!3xHl`

2. **Application:**
   - URL: `https://azzemanhotel.com`
   - Environment: `production`
   - Debug: `false`

3. **Gemini API:**
   - Key: (configured in config.php)

4. **Email:**
   - SMTP Host: (Gmail & Custom SMTP)
   - SMTP Port: `587` (TLS) / `465` (SSL)
   - SMTP User: `azzemanhotel17@gmail.com` / `admin@azzemanhotel.com`
   - SMTP Pass: (configured in config.php)
   - From Email: `azzemanhotel17@gmail.com` / `admin@azzemanhotel.com`
   - Admin Email: `reservation@azzemanhotel.com`

---

**Need Help?** Check the error logs first, then review the troubleshooting section above.

---

## 📌 Important Notes for cPanel

### File Structure

**Current Structure (for cPanel):**
- `index.php` is in the **root** of the project (not in `public/`)
- `.htaccess` is in the **root** of the project (not in `public/`)
- Upload **all files directly to `public_html/`** (not to `public_html/public/`)

### URL Structure

After deployment:
- Homepage: `https://azzemanhotel.com/`
- Admin Login: `https://azzemanhotel.com/admin-login`
- Gallery: `https://azzemanhotel.com/gallery`
- Gallery Category: `https://azzemanhotel.com/gallery/{category_id}`

### Configuration Priority

1. **Database credentials** - Must be correct for site to work (pre-configured as `azzemanhotel_sinq` / `azzemanhotel_sinqtech`)

### Security Reminders

- ✅ Never commit `config.php` to version control
- ✅ Change default admin password immediately
- ✅ Keep `APP_DEBUG = false` in production
- ✅ Set proper file permissions (755 for folders, 644 for files)
- ✅ Install SSL certificate for HTTPS
- ✅ Regularly backup database

---

**Ready to deploy?** Follow the steps in this guide, and your Azzeman Hotel website will be live on cPanel!

