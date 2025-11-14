# Installation Guide

Panduan instalasi lengkap untuk WhatsApp CRM System.

## Table of Contents

1. [Requirements](#requirements)
2. [Installation Methods](#installation-methods)
3. [Manual Installation](#manual-installation)
4. [Auto Installation](#auto-installation)
5. [cPanel Installation](#cpanel-installation)
6. [Configuration](#configuration)
7. [Troubleshooting](#troubleshooting)

---

## Requirements

### Server Requirements

- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher
- **Node.js**: 16.x or higher
- **Composer**: 2.x
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### PHP Extensions

```
- BCMath
- Ctype
- JSON
- Mbstring
- OpenSSL
- PDO
- PDO_MySQL
- Tokenizer
- XML
```

### Server Resources

- **RAM**: Minimum 1GB (2GB+ recommended)
- **Storage**: Minimum 5GB
- **CPU**: 1 core minimum (2+ recommended)

---

## Installation Methods

Ada 3 metode instalasi:

1. **Auto Installation** (Recommended) - menggunakan script bash
2. **Manual Installation** - step-by-step manual
3. **cPanel Installation** - untuk shared hosting

---

## Auto Installation

Metode tercepat menggunakan installation script.

### Steps:

```bash
# 1. Clone atau download repository
git clone https://github.com/your-repo/whatsapp-crm.git
cd whatsapp-crm

# 2. Berikan permission pada install script
chmod +x deployment/install.sh

# 3. Jalankan installer
sudo bash deployment/install.sh
```

Script akan:
- ✅ Check semua requirements
- ✅ Create database
- ✅ Import schema
- ✅ Install dependencies (backend, frontend, whatsapp-service)
- ✅ Generate keys
- ✅ Setup PM2 untuk WhatsApp service
- ✅ Set permissions

### Post-Installation:

```bash
# Test backend API
php -S localhost:8000 -t backend/public

# Test WhatsApp service
pm2 logs whatsapp-crm

# Get QR code
curl http://localhost:3000/qr
```

---

## Manual Installation

Untuk kontrol penuh atas installation process.

### 1. Database Setup

```bash
# Login ke MySQL
mysql -u root -p

# Create database
CREATE DATABASE whatsapp_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional)
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON whatsapp_crm.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;

# Import schema
mysql -u root -p whatsapp_crm < database/schema.sql
```

### 2. Backend Setup

```bash
cd backend

# Copy environment file
cp .env.example .env

# Edit .env file
nano .env

# Update these values:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=whatsapp_crm
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Test
php artisan serve
```

### 3. WhatsApp Service Setup

```bash
cd whatsapp-service

# Copy environment file
cp .env.example .env

# Edit .env
nano .env

# Update database credentials
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=whatsapp_crm
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Install dependencies
npm install

# Start service
npm start

# OR use PM2 for production
npm install -g pm2
pm2 start src/index.js --name whatsapp-crm
pm2 save
pm2 startup
```

### 4. Frontend Setup

```bash
cd frontend

# Copy environment file (optional)
cp .env.example .env 2>/dev/null || true

# Update API URL if needed
# VITE_API_URL=http://your-domain/api

# Install dependencies
npm install

# Development mode
npm run dev

# Production build
npm run build
```

### 5. Web Server Configuration

#### Apache

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/crm/backend/public

    <Directory /path/to/crm/backend/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/crm_error.log
    CustomLog ${APACHE_LOG_DIR}/crm_access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/crm/backend/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## cPanel Installation

Untuk shared hosting dengan cPanel.

### 1. Upload Files

```bash
# Compress project
zip -r crm.zip .

# Upload via cPanel File Manager atau FTP
# Extract ke public_html atau subdomain folder
```

### 2. Setup Database

1. Buka **cPanel > MySQL Databases**
2. Create database: `username_crm`
3. Create user dengan password
4. Add user to database dengan ALL PRIVILEGES
5. Import `database/schema.sql` via phpMyAdmin

### 3. Configure Backend

```bash
# Via cPanel File Manager
# 1. Rename backend/.env.example ke backend/.env
# 2. Edit file dan update:

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_crm
DB_USERNAME=username_dbuser
DB_PASSWORD=your_password
```

### 4. Install Dependencies

#### Option A: SSH Access

```bash
cd ~/public_html/backend
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

#### Option B: No SSH (Upload Vendor)

1. Install di local machine:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. Upload folder `vendor` via FTP
3. Generate key manually di `.env`:
   ```
   APP_KEY=base64:random_32_character_string_here
   ```

### 5. Setup .htaccess

Copy `deployment/.htaccess` ke root directory
Copy `deployment/backend.htaccess` ke `backend/public/.htaccess`

### 6. WhatsApp Service

**Note**: Shared hosting umumnya tidak support Node.js atau background processes.

**Solutions:**

- **Option A**: Use VPS untuk WhatsApp service, point ke shared hosting database
- **Option B**: Use Cloud Run / Heroku for WhatsApp service
- **Option C**: Upgrade ke VPS

**VPS Setup:**

```bash
# On VPS
cd whatsapp-service
npm install
pm2 start src/index.js --name whatsapp-crm

# Update .env DB credentials to point to shared hosting DB
DB_HOST=your-shared-hosting-ip
DB_DATABASE=username_crm
DB_USERNAME=username_dbuser
DB_PASSWORD=your_password
```

### 7. Frontend Build

```bash
# Build locally
cd frontend
npm install
npm run build

# Upload dist/ folder to public_html/frontend/
```

---

## Configuration

### Environment Variables

#### Backend (.env)

```env
APP_NAME="WhatsApp CRM"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_crm
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=...
JWT_TTL=1440

WHATSAPP_SERVICE_URL=http://localhost:3000

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

#### WhatsApp Service (.env)

```env
PORT=3000
SESSION_NAME=default

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_crm
DB_USERNAME=root
DB_PASSWORD=

BACKEND_API_URL=http://localhost:8000/api
WHATSAPP_RATE_LIMIT=30
```

### Cron Jobs

Setup cron untuk automated tasks:

```bash
# Edit crontab
crontab -e

# Add this line:
* * * * * cd /path/to/crm/backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## First Login

1. Buka browser: `http://your-domain.com`
2. Login dengan credentials default:
   - Email: `admin@example.com`
   - Password: `admin123`
3. **PENTING**: Segera ubah password!

---

## Scan WhatsApp QR Code

1. Visit: `http://your-domain:3000/qr`
2. Scan QR code dengan WhatsApp di HP
3. Wait untuk status "connected"
4. Check status: `http://your-domain:3000/status`

---

## Troubleshooting

### Database Connection Failed

```bash
# Test koneksi
mysql -h localhost -u username -p database_name

# Check credentials di .env
# Pastikan user punya privileges
```

### 500 Internal Server Error

```bash
# Check logs
tail -f backend/storage/logs/laravel.log

# Check permissions
chmod -R 755 backend/storage
chmod -R 755 backend/bootstrap/cache
chown -R www-data:www-data backend/storage
```

### WhatsApp Service Not Starting

```bash
# Check logs
pm2 logs whatsapp-crm

# Check database connection
# Check port 3000 not in use
netstat -tuln | grep 3000

# Restart service
pm2 restart whatsapp-crm
```

### QR Code Not Showing

```bash
# Check Chrome/Chromium installed
which google-chrome
which chromium-browser

# Install if missing
sudo apt-get install chromium-browser

# Check .wwebjs_auth permissions
chmod -R 755 whatsapp-service/.wwebjs_auth
```

### JWT Token Error

```bash
# Regenerate secret
cd backend
php artisan jwt:secret --force

# Clear cache
php artisan config:clear
php artisan cache:clear
```

---

## Production Checklist

Before going live:

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false` in backend/.env
- [ ] Enable HTTPS (SSL certificate)
- [ ] Setup regular database backups
- [ ] Configure firewall (UFW/iptables)
- [ ] Setup monitoring (PM2, New Relic, etc.)
- [ ] Test email notifications
- [ ] Test WhatsApp connectivity
- [ ] Configure CORS properly
- [ ] Set up log rotation
- [ ] Document custom configurations
- [ ] Test disaster recovery plan

---

## Support

For issues and questions:
- Documentation: `/docs`
- GitHub Issues: [link]
- Email: support@example.com

---

**Next Steps**: [Configuration Guide](CONFIGURATION.md) | [API Documentation](API.md) | [User Guide](USER_GUIDE.md)
