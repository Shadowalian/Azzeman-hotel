# cPanel Deployment Quick Checklist

Use this checklist when deploying to cPanel. Check off each item as you complete it.

## Pre-Upload Preparation

- [ ] Remove development files (`check_admin.php`, `setup_xampp.php`, test files)
- [ ] Clear log files (`logs/*.log`)
- [ ] Verify `config.sample.php` exists
- [ ] Ensure `.htaccess` file exists in root

## File Upload

- [ ] Upload all files to `public_html/` (or subdirectory)
- [ ] Verify `index.php` is in root of `public_html/`
- [ ] Verify `.htaccess` is in root of `public_html/`
- [ ] Verify `assets/` folder uploaded correctly
- [ ] Verify `src/` folder uploaded correctly

## Database Setup

- [ ] Created database in cPanel MySQL Databases
- [ ] Created database user in cPanel
- [ ] Granted ALL PRIVILEGES to user
- [ ] Imported `azzemanhotel_mysql.sql` via phpMyAdmin
- [ ] Verified tables exist (10 tables total)
- [ ] Verified admin user exists: `azzeman_admin`

## Configuration

- [ ] Copied `config.sample.php` to `config.php`
- [ ] Updated `DB_HOST` (usually `localhost`)
- [ ] Updated `DB_NAME` (with cPanel prefix)
- [ ] Updated `DB_USER` (with cPanel prefix)
- [ ] Updated `DB_PASS` (strong password)
- [ ] Updated `APP_URL` (your domain)
- [ ] Set `APP_ENV` to `production`
- [ ] Set `APP_DEBUG` to `false`
- [ ] Added `GEMINI_API_KEY` (for chatbot)
- [ ] Configured SMTP settings (for email)

## File Permissions

- [ ] Set `assets/uploads/` to 755
- [ ] Set `logs/` to 755
- [ ] Set `config.php` to 644
- [ ] Created `assets/uploads/site-images/` folder

## Testing

- [ ] Homepage loads: `https://yourdomain.com/`
- [ ] Database test passes: `https://yourdomain.com/tests/test_env.php`
- [ ] Admin login works: `https://yourdomain.com/admin-login`
- [ ] Gallery page loads: `https://yourdomain.com/gallery`
- [ ] Booking form submits successfully
- [ ] Chatbot responds (if API key configured)
- [ ] Email notifications sent (check spam folder)
- [ ] Favicon displays in browser tab

## Security

- [ ] Changed default admin password
- [ ] Verified `config.php` is protected (not accessible via URL)
- [ ] SSL certificate installed (recommended)
- [ ] Error reporting disabled (`APP_DEBUG = false`)

## Post-Deployment

- [ ] Bookmarked admin dashboard URL
- [ ] Saved admin credentials securely
- [ ] Set up database backups (weekly recommended)
- [ ] Tested all major features
- [ ] Verified mobile responsiveness

---

**Default Admin Credentials (Change Immediately!):**
- Username: `azzeman_admin`
- Password: `SecurePass!2024`

**For detailed instructions, see [CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md)**

