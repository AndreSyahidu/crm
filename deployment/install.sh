#!/bin/bash

echo "==============================================="
echo "WhatsApp CRM - Installation Script"
echo "==============================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

echo -e "${GREEN}Step 1: Checking requirements...${NC}"
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.1 or higher${NC}"
    exit 1
fi

PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo "PHP Version: $PHP_VERSION"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo -e "${YELLOW}MySQL client not found. Make sure MySQL server is installed${NC}"
fi

# Check Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}Node.js is not installed. Please install Node.js 16 or higher${NC}"
    exit 1
fi

NODE_VERSION=$(node -v)
echo "Node.js Version: $NODE_VERSION"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer is not installed. Installing...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

echo ""
echo -e "${GREEN}Step 2: Setting up database...${NC}"
echo ""

read -p "MySQL Host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "MySQL Port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "MySQL Database Name [whatsapp_crm]: " DB_NAME
DB_NAME=${DB_NAME:-whatsapp_crm}

read -p "MySQL Username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "MySQL Password: " DB_PASS
echo ""

# Create database
echo "Creating database..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}Database created successfully${NC}"
else
    echo -e "${RED}Failed to create database${NC}"
    exit 1
fi

# Import schema
echo "Importing database schema..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}Schema imported successfully${NC}"
else
    echo -e "${RED}Failed to import schema${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}Step 3: Installing backend dependencies...${NC}"
echo ""

cd backend
cp .env.example .env

# Update .env file
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

composer install --no-dev --optimize-autoloader

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

echo ""
echo -e "${GREEN}Step 4: Installing WhatsApp service dependencies...${NC}"
echo ""

cd ../whatsapp-service
cp .env.example .env

# Update .env file
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

npm install --production

echo ""
echo -e "${GREEN}Step 5: Installing frontend dependencies...${NC}"
echo ""

cd ../frontend
cp .env.example .env 2>/dev/null || true
npm install
npm run build

echo ""
echo -e "${GREEN}Step 6: Setting up PM2 for WhatsApp service...${NC}"
echo ""

npm install -g pm2

cd ../whatsapp-service
pm2 start src/index.js --name whatsapp-crm
pm2 save
pm2 startup

echo ""
echo -e "${GREEN}Step 7: Setting permissions...${NC}"
echo ""

cd ..
chmod -R 755 backend/storage
chmod -R 755 backend/bootstrap/cache
chown -R www-data:www-data backend/storage
chown -R www-data:www-data backend/bootstrap/cache

echo ""
echo -e "${GREEN}===============================================${NC}"
echo -e "${GREEN}Installation Complete!${NC}"
echo -e "${GREEN}===============================================${NC}"
echo ""
echo "Next steps:"
echo ""
echo "1. Backend API: http://your-domain/api"
echo "2. WhatsApp Service: http://localhost:3000"
echo "3. Scan QR code: http://localhost:3000/qr"
echo ""
echo "Default login credentials:"
echo "Email: admin@example.com"
echo "Password: admin123"
echo ""
echo -e "${YELLOW}Important: Change the default password after first login!${NC}"
echo ""
echo "For detailed documentation, see: docs/INSTALLATION.md"
echo ""
