# 🚀 START HERE - XAMPP Setup

## Quick Start (3 Steps)

### 1️⃣ Start XAMPP
- Open **XAMPP Control Panel**
- Start **Apache** ✅
- Start **MySQL** ✅

### 2️⃣ Run Setup
Open in browser:
```
http://localhost/azzemanhotel_PHP/setup_xampp.php
```

This will automatically:
- ✅ Check system requirements
- ✅ Create database `azzeman_hotel`
- ✅ Import all tables
- ✅ Test connection

### 3️⃣ Access Application

**Homepage:**
```
http://localhost/azzemanhotel_PHP/public/
```

**Admin Login:**
```
http://localhost/azzemanhotel_PHP/public/admin-login
```

**Credentials:**
- Username: `azzeman_admin`
- Password: `SecurePass!2024`

---

## ✅ Test Everything Works

**Quick Test:**
```
http://localhost/azzemanhotel_PHP/public/test.php
```

---

## 🐛 Problems?

### Database Connection Failed?
1. Check MySQL is running in XAMPP
2. Verify database name: `azzeman_hotel`
3. Check user: `root`, password: (empty)

### 404 Not Found?
- Make sure you're accessing: `http://localhost/azzemanhotel_PHP/public/`
- Check Apache is running

### Need Help?
- See `QUICK_START_XAMPP.md` for detailed guide
- See `README.md` for full documentation

---

## 📋 What's Next?

Once it works on XAMPP:
1. Test all features
2. Update config for production
3. Deploy to cPanel

