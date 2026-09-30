# Quick Start - XAMPP

## 🚀 Get Running in 5 Minutes

### Step 1: Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**

### Step 2: Run Setup Script
Open in browser:
```
http://localhost/azzemanhotel_PHP/setup_xampp.php
```

The script will:
- ✅ Check system requirements
- ✅ Create database `azzeman_hotel`
- ✅ Import all tables and seed data
- ✅ Test the connection

### Step 3: Access the Application

**Homepage:**
```
http://localhost/azzemanhotel_PHP/public/
```

**Admin Login:**
```
http://localhost/azzemanhotel_PHP/public/admin-login
```

**Default Credentials:**
- Username: `azzeman_admin`
- Password: `SecurePass!2024`

### Step 4: Test Everything Works

**Quick Test Page:**
```
http://localhost/azzemanhotel_PHP/public/test.php
```

This will show:
- ✅ Configuration status
- ✅ Database connection
- ✅ Session support
- ✅ File permissions

## 📝 Manual Database Setup (if setup script fails)

1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Click **New** → Database name: `azzeman_hotel` → Create
3. Select `azzeman_hotel` database
4. Click **Import** tab
5. Choose file: `azzemanhotel_mysql.sql`
6. Click **Go**

## ⚙️ Configuration

The `config.sample.php` is already set for XAMPP:
- Database: `azzeman_hotel`
- User: `root`
- Password: (empty)

For development mode, edit `config.sample.php` and uncomment:
```php
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_URL', 'http://localhost/azzemanhotel_PHP/public');
```

## 🐛 Troubleshooting

### "Database connection failed"
- ✅ Check MySQL is running in XAMPP
- ✅ Verify database name is `azzeman_hotel`
- ✅ Check user is `root` with empty password

### "404 Not Found"
- ✅ Make sure you're accessing: `http://localhost/azzemanhotel_PHP/public/`
- ✅ Check Apache is running
- ✅ Verify `.htaccess` file exists in `public/` folder

### "Class not found"
- ✅ Check all files are in correct directories
- ✅ Verify `config.php` exists (it loads from `config.sample.php`)

### "Permission denied"
- ✅ On Windows, directories should be writable by default
- ✅ If issues, check `logs/` and `assets/uploads/` permissions

## ✅ Success Checklist

- [ ] Setup script runs without errors
- [ ] Database created and tables imported
- [ ] Homepage loads: `http://localhost/azzemanhotel_PHP/public/`
- [ ] Admin login works: `http://localhost/azzemanhotel_PHP/public/admin-login`
- [ ] Can create a booking
- [ ] Can view bookings in admin dashboard

## 🎯 Next Steps

Once everything works on XAMPP:
1. Test all features thoroughly
2. Update configuration for production
3. Deploy to cPanel following `README.md`

