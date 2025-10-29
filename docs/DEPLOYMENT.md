# Production Deployment Guide

This guide covers deployment and production configuration for the Sistem Approval Dokumen.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Environment Configuration](#environment-configuration)
3. [Database Setup](#database-setup)
4. [Docker Deployment](#docker-deployment)
5. [Manual Deployment](#manual-deployment)
6. [Security Checklist](#security-checklist)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring & Maintenance](#monitoring--maintenance)

## Prerequisites

### System Requirements
- **Server**: Linux (Ubuntu 20.04+ or similar)
- **PHP**: 8.2 or higher
- **Node.js**: 20.x or higher
- **Database**: SQLite, MySQL 8.0+, or PostgreSQL 13+
- **Web Server**: Nginx or Apache
- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 10GB available disk space
- **SSL Certificate**: Required for production (Let's Encrypt recommended)

### Required Software
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
  php8.2-sqlite3 php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl \
  php8.2-xml php8.2-bcmath -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Install Nginx
sudo apt install nginx -y
```

## Environment Configuration

### Backend (.env)

1. Copy the example environment file:
```bash
cd backend
cp .env.example .env
```

2. Generate application key:
```bash
php artisan key:generate
```

3. Configure essential variables:
```env
# Application
APP_NAME="Sistem Approval Dokumen"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (MySQL example)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=approval_system
DB_USERNAME=approval_user
DB_PASSWORD=secure_password_here

# Session & Security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_DOMAIN=your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

# File Storage
FILESYSTEM_DISK=public

# Queue
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=database

# Mail (configure with your SMTP provider)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Frontend URL
FRONTEND_URL=https://your-frontend-domain.com

# Sanctum
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
```

### Frontend (.env)

```bash
cd frontend
cp .env.example .env
```

Configure:
```env
NUXT_PUBLIC_API_BASE=https://your-backend-domain.com/api
NUXT_PUBLIC_DOMAIN=https://your-frontend-domain.com
NUXT_APP_ENV=production
```

## Database Setup

### MySQL/PostgreSQL Setup

1. Create database and user:
```sql
-- MySQL
CREATE DATABASE approval_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'approval_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON approval_system.* TO 'approval_user'@'localhost';
FLUSH PRIVILEGES;

-- PostgreSQL
CREATE DATABASE approval_system;
CREATE USER approval_user WITH ENCRYPTED PASSWORD 'secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE approval_system TO approval_user;
```

2. Run migrations:
```bash
cd backend
php artisan migrate --force
```

3. Seed initial data:
```bash
php artisan db:seed --force
```

### SQLite Setup (Not recommended for production)

```bash
cd backend
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
```

## Docker Deployment

### Using Docker Compose

1. Clone the repository:
```bash
git clone https://github.com/atiohaidar/sistem-approval-dokumen.git
cd sistem-approval-dokumen
```

2. Configure environment variables (see above)

3. Build and start containers:
```bash
docker-compose up -d --build
```

4. Run migrations inside container:
```bash
docker-compose exec backend php artisan migrate --force
docker-compose exec backend php artisan db:seed --force
docker-compose exec backend php artisan storage:link
```

5. Set proper permissions:
```bash
docker-compose exec backend chown -R www-data:www-data /var/www/backend/storage
docker-compose exec backend chmod -R 755 /var/www/backend/storage
```

### Services

- Backend API: http://localhost:8000
- Frontend: http://localhost:3000
- Health Check: http://localhost:8000/api/health

## Manual Deployment

### Backend Deployment

1. Install dependencies:
```bash
cd backend
composer install --no-dev --optimize-autoloader
```

2. Optimize Laravel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

3. Set permissions:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

4. Create symbolic link for storage:
```bash
php artisan storage:link
```

5. Configure Nginx:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/approval-system/backend/public;
    index index.php;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

6. Enable site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/approval-system /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Frontend Deployment

1. Install dependencies and build:
```bash
cd frontend
npm ci
npm run build
```

2. Start with PM2:
```bash
npm install -g pm2
pm2 start .output/server/index.mjs --name "approval-frontend"
pm2 save
pm2 startup
```

3. Configure reverse proxy (Nginx):
```nginx
server {
    listen 80;
    server_name frontend.your-domain.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (already configured by certbot)
sudo certbot renew --dry-run
```

## Security Checklist

### Application Security
- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secured
- [ ] HTTPS enabled with valid SSL certificate
- [ ] Session cookies secured (`SESSION_SECURE_COOKIE=true`)
- [ ] CORS properly configured
- [ ] Rate limiting enabled
- [ ] File upload validation active
- [ ] Input sanitization middleware enabled
- [ ] Security headers configured

### Server Security
- [ ] Firewall configured (UFW/iptables)
- [ ] SSH key authentication only
- [ ] Non-root user for application
- [ ] Fail2ban installed and configured
- [ ] Regular security updates scheduled
- [ ] Database access restricted
- [ ] File permissions properly set (755 for directories, 644 for files)
- [ ] `.env` files protected (not web-accessible)

### Monitoring
- [ ] Error logging configured
- [ ] Access logs enabled
- [ ] Health check endpoint accessible
- [ ] Disk space monitoring
- [ ] Backup strategy implemented

## Performance Optimization

### Backend Optimization

1. **Enable OPcache** (php.ini):
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

2. **Database Optimization**:
```bash
# Run migrations to add indexes
php artisan migrate --force

# Optimize database queries
php artisan optimize:clear
```

3. **Queue Workers**:
```bash
# Start queue worker
php artisan queue:work --tries=3 --timeout=90 --daemon

# With Supervisor (recommended)
sudo apt install supervisor -y
```

Supervisor config (`/etc/supervisor/conf.d/approval-worker.conf`):
```ini
[program:approval-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/approval-system/backend/artisan queue:work --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/approval-system/backend/storage/logs/worker.log
```

### Frontend Optimization

1. **Enable compression** in Nginx:
```nginx
gzip on;
gzip_vary on;
gzip_min_length 10240;
gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;
```

2. **Static asset caching**:
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## Monitoring & Maintenance

### Health Check

Access health endpoint:
```bash
curl https://your-domain.com/api/health
```

Expected response:
```json
{
  "status": "healthy",
  "timestamp": "2025-10-29T02:00:00.000000Z",
  "checks": {
    "database": "ok",
    "storage": "ok",
    "queue": "ok"
  },
  "version": "1.0.0"
}
```

### Logs

Monitor application logs:
```bash
# Laravel logs
tail -f backend/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### Backup Strategy

1. **Database Backup**:
```bash
# MySQL
mysqldump -u approval_user -p approval_system > backup_$(date +%Y%m%d).sql

# PostgreSQL
pg_dump -U approval_user approval_system > backup_$(date +%Y%m%d).sql
```

2. **File Backup**:
```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d).tar.gz backend/storage/app/public/documents
```

3. **Automated Backup Script**:
```bash
#!/bin/bash
# Save as /usr/local/bin/backup-approval-system.sh

BACKUP_DIR="/var/backups/approval-system"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u approval_user -p'your_password' approval_system | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup files
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C /var/www/approval-system/backend storage/app/public

# Remove backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Add to crontab:
```bash
# Run daily at 2 AM
0 2 * * * /usr/local/bin/backup-approval-system.sh >> /var/log/backup.log 2>&1
```

### Maintenance Mode

Enable maintenance mode during updates:
```bash
php artisan down --message="System maintenance in progress"

# Perform updates...

php artisan up
```

### Updates

```bash
# Pull latest code
git pull origin main

# Backend updates
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Frontend updates
cd ../frontend
npm ci
npm run build
pm2 restart approval-frontend

# Clear caches
cd ../backend
php artisan cache:clear
php artisan optimize:clear
```

## Troubleshooting

### Common Issues

1. **Permission Errors**:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

2. **Storage Link Missing**:
```bash
php artisan storage:link
```

3. **Queue Not Processing**:
```bash
# Check queue worker status
php artisan queue:work --once

# Restart supervisor
sudo supervisorctl restart approval-worker:*
```

4. **High Memory Usage**:
```bash
# Clear all caches
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Support

For issues and questions:
- Create an issue on GitHub
- Check the logs for detailed error messages
- Review the security and performance settings

---

**Last Updated**: 2025-10-29
**Version**: 1.0.0
