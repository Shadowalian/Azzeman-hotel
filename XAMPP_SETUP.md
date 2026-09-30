# XAMPP Setup Guide

## Quick Start

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache**
   - Start **MySQL**

2. **Run Setup Script**
   - Open browser: `http://localhost/azzemanhotel_PHP/setup_xampp.php`
   - Follow the on-screen instructions
   - The script will:
     - Check system requirements
     - Create the database
     - Import the schema
     - Test the connection

3. **Access the Application**
   - Homepage: `http://localhost/azzemanhotel_PHP/public/`
   - Admin Login: `http://localhost/azzemanhotel_PHP/public/admin-login`
   - Default credentials:
     - Username: `azzeman_admin`
     - Password: `SecurePass!2024`

## Manual Database Setup (Alternative)

If the setup script doesn't work, you can set up the database manually:

1. **Open phpMyAdmin**
   - Go to: `http://localhost/phpmyadmin`

2. **Create Database**
   - Click "New" in the left sidebar
   - Database name: `azzeman_hotel`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import Schema**
   - Select the `azzeman_hotel` database
   - Click "Import" tab
   - Choose file: `azzemanhotel_mysql.sql`
   - Click "Go"

4. **Verify**
   - You should see 9 tables created
   - Check that `admin_users` table has 1 row

## Configuration for Development

Update `config.sample.php` for XAMPP:

```php
// For XAMPP development, uncomment these:
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_URL', 'http://localhost/azzemanhotel_PHP/public');
```

## Troubleshooting

### "Database connection failed"
- ✅ Check MySQL is running in XAMPP
- ✅ Verify credentials in `config.sample.php` (default: root / empty password)
- ✅ Check database name is `azzeman_hotel`

### "404 Not Found" or routing doesn't work
- ✅ Make sure you're accessing `http://localhost/azzemanhotel_PHP/public/`
- ✅ Check Apache mod_rewrite is enabled (usually enabled by default in XAMPP)
- ✅ Verify `.htaccess` file exists in `public/` directory

### "Permission denied" errors
- ✅ Check `logs/` directory is writable
- ✅ Check `assets/uploads/` directory is writable
- ✅ On Windows, these should work by default

### "Class not found" errors
- ✅ Check all files are in the correct directories
- ✅ Verify `config.php` loads `config.sample.php` correctly

## Testing

1. **Test Database Connection**
   - Visit: `http://localhost/azzemanhotel_PHP/tests/test_env.php`
   - Should show: `{"database":true,"session":true,"config":true}`

2. **Test Homepage**
   - Visit: `http://localhost/azzemanhotel_PHP/public/`
   - Should display the homepage with all sections

3. **Test Admin Login**
   - Visit: `http://localhost/azzemanhotel_PHP/public/admin-login`
   - Login with: `azzeman_admin` / `SecurePass!2024`
   - Should redirect to admin dashboard

4. **Test Booking**
   - Click "Book Now" on homepage
   - Fill out the form
   - Should submit successfully

## File Structure for XAMPP

```
C:\xampp\htdocs\azzeman-hotel3\
└── azzemanhotel_PHP/
    └── public/          ← Access via: http://localhost/azzemanhotel_PHP/public/
```

## Next Steps

Once everything works on XAMPP:
1. Test all features (bookings, admin panel, etc.)
2. Update configuration for production
3. Deploy to cPanel following the README.md guide

