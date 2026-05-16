# 🚀 Elite School Management - Installation Guide

সম্পূর্ণ ইনস্টলেশন প্রক্রিয়া (ধাপে ধাপে)

---

## 📋 সিস্টেম প্রয়োজনীয়তা

### ন্যূনতম প্রয়োজনীয়তা

```plaintext
✅ PHP 8.0 বা উচ্চতর
✅ MySQL 5.7+ অথবা MariaDB 10.3+
✅ Apache 2.4+ (mod_rewrite সক্রিয়)
✅ 512MB RAM (সুপারিশ: 1GB+)
✅ 500MB ডিস্ক স্পেস
✅ PHP Extensions:
   - PDO
   - PDO_MySQL
   - JSON
   - mbstring
   - cURL
   - GD
   - OpenSSL
```

### PHP Configuration সুপারিশ

```ini
; php.ini settings
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
memory_limit = 256M
display_errors = Off
log_errors = On
```

---

## 🔧 ধাপ ১: সফটওয়্যার ইনস্টলেশন

### Windows (XAMPP)

1. **XAMPP ডাউনলোড এবং ইনস্টল করুন**
   ```
   https://www.apachefriends.org/download.html
   - PHP 8.0+ version নির্বাচন করুন
   ```

2. **XAMPP Control Panel খুলুন**
   - Apache Start করুন
   - MySQL Start করুন

3. **Browser এ যান**
   ```
   http://localhost/phpmyadmin
   ```

### Linux (Ubuntu/Debian)

```bash
# Apache, PHP, MySQL ইনস্টল করুন
sudo apt update
sudo apt install apache2 php8.1 php8.1-mysql php8.1-mbstring php8.1-curl php8.1-gd php8.1-xml mysql-server

# Apache mod_rewrite সক্রিয় করুন
sudo a2enmod rewrite

# Apache পুনরায় চালু করুন
sudo systemctl restart apache2
```

---

## 📦 ধাপ ২: প্রজেক্ট ফাইল সেটআপ

### Windows (XAMPP)

```bash
# XAMPP htdocs ফোল্ডারে প্রজেক্ট ফাইল কপি করুন
C:\xampp\htdocs\elite-school-management\

# অথবা ZIP extract করুন htdocs এ
```

### Linux

```bash
# Apache document root এ প্রজেক্ট কপি করুন
sudo cp -r elite-school-management /var/www/html/

# Permissions সেট করুন
sudo chown -R www-data:www-data /var/www/html/elite-school-management
sudo chmod -R 755 /var/www/html/elite-school-management
```

---

## 🗄️ ধাপ ৩: Database তৈরি

### পদ্ধতি ১: phpMyAdmin ব্যবহার করে

1. **phpMyAdmin খুলুন**
   ```
   http://localhost/phpmyadmin
   ```

2. **নতুন Database তৈরি করুন**
   - 'New' ক্লিক করুন
   - Database name: `elite_school_db`
   - Collation: `utf8mb4_unicode_ci`
   - 'Create' ক্লিক করুন

3. **Schema Import করুন**
   - `elite_school_db` ডাটাবেস নির্বাচন করুন
   - 'Import' ট্যাবে যান
   - 'Choose File' ক্লিক করুন
   - `database/schema.sql` ফাইল নির্বাচন করুন
   - 'Go' ক্লিক করুন
   - ✅ Success message দেখুন

4. **Demo Data Import করুন**
   - একই পদ্ধতিতে `database/demo_data.sql` import করুন

### পদ্ধতি ২: Command Line ব্যবহার করে

```bash
# MySQL এ লগইন করুন
mysql -u root -p

# Database তৈরি করুন
CREATE DATABASE elite_school_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Schema import করুন
mysql -u root -p elite_school_db < database/schema.sql

# Demo data import করুন
mysql -u root -p elite_school_db < database/demo_data.sql
```

### ✅ Database সঠিক import হয়েছে কি যাচাই করুন

```sql
USE elite_school_db;
SHOW TABLES;
-- Output: 35+ tables দেখতে পাবেন

SELECT COUNT(*) FROM users;
-- Output: 6 users থাকবে
```

---

## ⚙️ ধাপ ৪: Configuration সেটআপ

### 1. Database Configuration

**ফাইল:** `config/db.php`

```php
<?php
// আপনার database credentials দিয়ে পরিবর্তন করুন
define('DB_HOST', 'localhost');
define('DB_NAME', 'elite_school_db');
define('DB_USER', 'root');           // আপনার MySQL username
define('DB_PASS', '');               // আপনার MySQL password
define('DB_CHARSET', 'utf8mb4');
```

**⚠️ গুরুত্বপূর্ণ:**
- XAMPP এ সাধারণত password খালি থাকে
- Linux/Production এ strong password ব্যবহার করুন

### 2. Application Configuration

**ফাইল:** `config/app.php`

```php
<?php
// Application URL সেট করুন
define('APP_URL', 'http://localhost/elite-school-management');

// Production এ:
// define('APP_URL', 'https://yourdomain.com');
```

### 3. Directory Permissions (Linux only)

```bash
# Upload এবং log folders এ write permission দিন
sudo chmod -R 755 uploads/
sudo chmod -R 755 logs/

# Apache user কে owner বানান
sudo chown -R www-data:www-data uploads/
sudo chown -R www-data:www-data logs/
```

---

## 🎯 ধাপ ৫: Composer Dependencies

```bash
# প্রজেক্ট directory তে যান
cd /path/to/elite-school-management

# Composer install করুন (প্রথমবার)
composer install

# Dependencies update করুন
composer update
```

**⚠️ Composer না থাকলে:**

### Windows
```
https://getcomposer.org/download/
থেকে Composer-Setup.exe ডাউনলোড এবং ইনস্টল করুন
```

### Linux
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 🌐 ধাপ ৬: Virtual Host সেটআপ (Optional)

### Apache Virtual Host (Linux)

**ফাইল:** `/etc/apache2/sites-available/eliteschool.conf`

```apache
<VirtualHost *:80>
    ServerName eliteschool.local
    DocumentRoot /var/www/html/elite-school-management
    
    <Directory /var/www/html/elite-school-management>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/eliteschool_error.log
    CustomLog ${APACHE_LOG_DIR}/eliteschool_access.log combined
</VirtualHost>
```

```bash
# Site সক্রিয় করুন
sudo a2ensite eliteschool.conf
sudo systemctl restart apache2

# Hosts file edit করুন
sudo nano /etc/hosts

# এই লাইন যোগ করুন
127.0.0.1    eliteschool.local

# Save এবং exit
```

এখন access করতে পারবেন: `http://eliteschool.local`

---

## 🔐 ধাপ ৭: Security Hardening

### Production Environment এ অবশ্যই করণীয়:

1. **পাসওয়ার্ড পরিবর্তন করুন**
   ```sql
   -- phpMyAdmin অথবা MySQL CLI তে
   UPDATE users SET password = '$2y$12$NEW_HASHED_PASSWORD' WHERE email = 'admin@eliteschool.com';
   ```

2. **Error Display বন্ধ করুন**
   ```php
   // config/app.php তে
   error_reporting(0);
   ini_set('display_errors', '0');
   ```

3. **HTTPS সক্রিয় করুন**
   - SSL Certificate install করুন
   - .htaccess এ force HTTPS uncomment করুন

4. **Database User সীমাবদ্ধ করুন**
   ```sql
   CREATE USER 'esm_user'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON elite_school_db.* TO 'esm_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

---

## ✅ ধাপ ৮: সিস্টেম টেস্ট

### 1. Homepage Access

```
URL: http://localhost/elite-school-management/
Expected: Redirect to Login page
```

### 2. Login Test

```
Email: admin@eliteschool.com
Password: admin123

Expected Result:
✅ Success message দেখুন
✅ Dashboard এ redirect হবে
```

### 3. Database Connection Test

```php
// test.php ফাইল তৈরি করুন root directory তে

<?php
require_once 'config/db.php';

try {
    $db = Database::getInstance();
    echo "✅ Database connection successful!<br>";
    
    $result = Database::fetchOne('SELECT COUNT(*) as count FROM users');
    echo "✅ Found {$result['count']} users in database<br>";
    
    echo "✅ System is ready!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

```
Access: http://localhost/elite-school-management/test.php
Expected: ✅ Success messages
```

**টেস্টের পরে test.php মুছে ফেলুন!**

---

## 🛠️ ট্রাবলশুটিং

### সমস্যা ১: "Database connection failed"

**সমাধান:**
```bash
# MySQL চালু আছে কি চেক করুন
# Windows (XAMPP): Control Panel এ MySQL running দেখুন
# Linux:
sudo systemctl status mysql

# config/db.php এর credentials চেক করুন
```

### সমস্যা ২: "404 Not Found" সব page এ

**সমাধান:**
```bash
# Apache mod_rewrite চালু করুন
# Linux:
sudo a2enmod rewrite
sudo systemctl restart apache2

# XAMPP:
httpd.conf এ AllowOverride All সেট করুন
```

### সমস্যা ৩: File upload কাজ করছে না

**সমাধান:**
```bash
# Directory permissions চেক করুন
# Linux:
sudo chmod -R 755 uploads/
sudo chown -R www-data:www-data uploads/

# Windows: uploads/ folder এ write permission আছে কি দেখুন
```

### সমস্যা ৪: CSS/JS load হচ্ছে না

**সমাধান:**
```php
// config/app.php এ APP_URL সঠিক আছে কি চেক করুন
define('APP_URL', 'http://localhost/elite-school-management');
```

### সমস্যা ৫: Login পরে blank page

**সমাধান:**
```bash
# PHP error log চেক করুন
# XAMPP: C:\xampp\php\logs\php_error_log
# Linux: /var/log/apache2/error.log

# Session directory writable কি দেখুন
# Linux:
sudo chmod 755 /var/lib/php/sessions
```

---

## 📞 সাহায্য প্রয়োজন?

### লগ ফাইল চেক করুন:
```bash
# Application logs
logs/php_errors.log
logs/db.log

# Apache logs (Linux)
/var/log/apache2/error.log
/var/log/apache2/access.log

# XAMPP logs (Windows)
C:\xampp\apache\logs\error.log
```

### Test Mode সক্রিয় করুন:
```php
// config/app.php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

---

## ✅ ইনস্টলেশন সম্পন্ন!

এখন আপনি এই features ব্যবহার করতে পারবেন:

✅ Student Management  
✅ Attendance System  
✅ Examination & Grading  
✅ Fee Collection  
✅ Online Payment (Configuration প্রয়োজন)  
✅ SMS/Email Notification (Configuration প্রয়োজন)  
✅ PDF Reports  
✅ Certificate Generation  
✅ Library Management  
✅ HR & Payroll  

---

## 🔜 পরবর্তী পদক্ষেপ

1. **Payment Gateway Configure করুন** (Optional)
   - বিস্তারিত: `config/payment_config.php`
   - bKash, SSLCommerz, Nagad credentials যোগ করুন

2. **SMS/Email Configure করুন** (Optional)
   - বিস্তারিত: `config/notification_config.php`
   - Twilio/SSL Wireless credentials যোগ করুন

3. **School Information আপডেট করুন**
   - Settings → System Settings
   - School name, address, logo etc.

4. **Academic Year সেট করুন**
   - Academic → Academic Years
   - Current year activate করুন

5. **Classes এবং Sections তৈরি করুন**
   - Academic → Classes
   - Sections যোগ করুন

---

**🎉 শুভকামনা! আপনার স্কুল ম্যানেজমেন্ট সিস্টেম প্রস্তুত!**

📧 Support: support@eliteschool.com  
🌐 Documentation: README.md
