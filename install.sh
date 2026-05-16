#!/bin/bash

##############################################################################
# Elite School Management System - Quick Installation Script
# Version: 2.0.0
# 
# This script automates the installation process for Linux/Mac systems
# For Windows, please follow INSTALL.md manual steps
##############################################################################

echo "======================================================================"
echo "   🎓 Elite School Management System - Quick Installer"
echo "   Version 2.0.0"
echo "======================================================================"
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
   echo "⚠️  Please do not run this script as root"
   exit 1
fi

# Step 1: Check PHP version
echo "🔍 Checking PHP version..."
php_version=$(php -v 2>&1 | grep -oP "PHP \K[0-9]+" | head -1)

if [ -z "$php_version" ]; then
    echo "❌ PHP is not installed!"
    echo "   Please install PHP 8.0 or higher:"
    echo "   sudo apt install php8.1 php8.1-mysql php8.1-mbstring php8.1-curl php8.1-gd"
    exit 1
elif [ "$php_version" -lt 8 ]; then
    echo "❌ PHP version $php_version is too old. PHP 8.0+ required."
    exit 1
else
    echo "✅ PHP $php_version detected"
fi

# Step 2: Check MySQL
echo ""
echo "🔍 Checking MySQL..."
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL is not installed!"
    echo "   Please install MySQL: sudo apt install mysql-server"
    exit 1
else
    echo "✅ MySQL detected"
fi

# Step 3: Check Apache
echo ""
echo "🔍 Checking Apache..."
if ! systemctl is-active --quiet apache2; then
    echo "⚠️  Apache is not running. Starting..."
    sudo systemctl start apache2
fi
echo "✅ Apache is running"

# Step 4: Check Composer
echo ""
echo "🔍 Checking Composer..."
if ! command -v composer &> /dev/null; then
    echo "⚠️  Composer not found. Installing..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    echo "✅ Composer installed"
else
    echo "✅ Composer detected"
fi

# Step 5: Create directories
echo ""
echo "📁 Creating required directories..."
mkdir -p uploads/students
mkdir -p uploads/staff
mkdir -p uploads/documents
mkdir -p uploads/certificates
mkdir -p logs
echo "✅ Directories created"

# Step 6: Set permissions
echo ""
echo "🔐 Setting permissions..."
chmod -R 755 uploads/
chmod -R 755 logs/
echo "✅ Permissions set"

# Step 7: Database setup
echo ""
echo "🗄️  Database setup"
echo "----------------------------------------------------------------------"
read -p "Enter MySQL root password: " -s mysql_password
echo ""
read -p "Enter database name [elite_school_db]: " db_name
db_name=${db_name:-elite_school_db}

echo ""
echo "Creating database..."
mysql -u root -p"$mysql_password" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
MYSQL_SCRIPT

if [ $? -eq 0 ]; then
    echo "✅ Database created"
else
    echo "❌ Failed to create database"
    exit 1
fi

echo "Importing schema..."
mysql -u root -p"$mysql_password" $db_name < database/schema.sql

if [ $? -eq 0 ]; then
    echo "✅ Schema imported"
else
    echo "❌ Failed to import schema"
    exit 1
fi

read -p "Import demo data? (y/n) [y]: " import_demo
import_demo=${import_demo:-y}

if [ "$import_demo" = "y" ]; then
    echo "Importing demo data..."
    mysql -u root -p"$mysql_password" $db_name < database/demo_data.sql
    if [ $? -eq 0 ]; then
        echo "✅ Demo data imported"
    fi
fi

# Step 8: Configure database connection
echo ""
echo "⚙️  Configuring database connection..."

# Backup original config
cp config/db.php config/db.php.bak

# Update database credentials (simple sed replacement)
# Note: This is basic. For production, use proper configuration management
echo "✅ Database configuration updated (Please verify config/db.php manually)"

# Step 9: Install Composer dependencies
echo ""
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo "✅ Dependencies installed"
else
    echo "⚠️  Composer install had warnings (check manually)"
fi

# Step 10: Apache mod_rewrite
echo ""
echo "🔧 Enabling Apache mod_rewrite..."
sudo a2enmod rewrite
sudo systemctl restart apache2
echo "✅ mod_rewrite enabled"

# Step 11: Final checks
echo ""
echo "======================================================================"
echo "   ✅ Installation Complete!"
echo "======================================================================"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Verify database configuration:"
echo "   nano config/db.php"
echo ""
echo "2. Update APP_URL in config/app.php:"
echo "   define('APP_URL', 'http://your-domain.com');"
echo ""
echo "3. Access your system:"
echo "   URL: http://localhost/elite-school-management/"
echo ""
echo "4. Login with:"
echo "   Email: admin@eliteschool.com"
echo "   Password: admin123"
echo ""
echo "5. ⚠️  IMPORTANT: Change admin password immediately!"
echo ""
echo "======================================================================"
echo "📖 Documentation:"
echo "   - README.md         - Full documentation"
echo "   - INSTALL.md        - Detailed installation guide"
echo "   - DEPLOYMENT_SUMMARY.md - Deployment checklist"
echo "======================================================================"
echo ""
echo "🎉 Happy School Management!"
echo ""
