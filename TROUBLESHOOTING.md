# Troubleshooting Guide

## "Page not found" Error

If you see "Page not found" when accessing `http://localhost/azzemanhotel_PHP/public/`:

### Step 1: Check Debug Information
Visit the debug page:
```
http://localhost/azzemanhotel_PHP/public/debug.php
```

This will show:
- The actual path being parsed
- File existence checks
- Apache module status

### Step 2: Enable Debug Mode
Edit `config.sample.php` and uncomment these lines:
```php
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_URL', 'http://localhost/azzemanhotel_PHP/public');
```

Then refresh the page to see the actual error.

### Step 3: Check Common Issues

#### Issue: Path Parsing
**Symptom:** "Page not found" even though files exist

**Solution:** The routing has been fixed to handle subdirectories. Make sure you're accessing:
```
http://localhost/azzemanhotel_PHP/public/
```
(Note the trailing slash or no trailing slash both work)

#### Issue: Database Connection
**Symptom:** Error about database connection

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Run the setup script: `http://localhost/azzemanhotel_PHP/setup_xampp.php`
3. Verify database `azzeman_hotel` exists in phpMyAdmin

#### Issue: Missing Files
**Symptom:** "Class not found" or "File not found"

**Solution:**
1. Check all files are in the correct directories
2. Verify `config.php` exists (it loads from `config.sample.php`)
3. Check file permissions

#### Issue: Apache mod_rewrite
**Symptom:** 404 errors on all routes

**Solution:**
1. Check if mod_rewrite is enabled in XAMPP
2. Edit `C:\xampp\apache\conf\httpd.conf`
3. Find: `#LoadModule rewrite_module modules/mod_rewrite.so`
4. Remove the `#` to uncomment it
5. Restart Apache

#### Issue: .htaccess Not Working
**Symptom:** Direct file access works but routing doesn't

**Solution:**
1. Check `.htaccess` file exists in `public/` directory
2. Verify Apache allows .htaccess overrides
3. In `httpd.conf`, find your `<Directory>` block and ensure:
   ```apache
   AllowOverride All
   ```

### Step 4: Check Error Logs

**PHP Error Log:**
```
C:\xampp\htdocs\azzeman-hotel3\azzemanhotel_PHP\logs\php_errors.log
```

**Apache Error Log:**
```
C:\xampp\apache\logs\error.log
```

### Step 5: Test Individual Components

**Test Database:**
```
http://localhost/azzemanhotel_PHP/public/test.php
```

**Test Direct File Access:**
```
http://localhost/azzemanhotel_PHP/public/test.php
```
(Should work even if routing doesn't)

**Test Configuration:**
Create a simple `info.php` in `public/`:
```php
<?php
require_once __DIR__ . '/../config.php';
echo "Config loaded!<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "APP_DEBUG: " . (APP_DEBUG ? 'ON' : 'OFF');
```

## Still Not Working?

1. **Check XAMPP is running:**
   - Apache: ✅ Green
   - MySQL: ✅ Green

2. **Check URL format:**
   - ✅ `http://localhost/azzemanhotel_PHP/public/`
   - ✅ `http://localhost/azzemanhotel_PHP/public`
   - ❌ `http://localhost/azzemanhotel_PHP/public/index.php` (should still work but routing won't)

3. **Clear browser cache:**
   - Sometimes cached 404s persist

4. **Check file permissions:**
   - On Windows, files should be readable by default
   - If issues, right-click folder → Properties → Security → Check permissions

5. **Try accessing directly:**
   ```
   http://localhost/azzemanhotel_PHP/public/index.php
   ```
   If this works, the issue is with `.htaccess` or routing.

## Quick Fix Checklist

- [ ] Apache is running in XAMPP
- [ ] MySQL is running in XAMPP
- [ ] Database `azzeman_hotel` exists
- [ ] `config.sample.php` has correct database credentials
- [ ] `APP_DEBUG` is set to `true` for testing
- [ ] `.htaccess` file exists in `public/` directory
- [ ] mod_rewrite is enabled in Apache
- [ ] No PHP syntax errors (check error log)

