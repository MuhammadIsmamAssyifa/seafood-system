# Deployment & Production Guide

Panduan teknis pemasangan, konfigurasi server, dan peluncuran (*go-live*) backend Seafood Restaurant Management System (RMS).

---

## 1. Kebutuhan Sistem (System Requirements)

- **PHP**: Versi 8.2 atau 8.3
  - Ekstensi wajib: `pdo_pgsql`, `pgsql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`
- **Database**: PostgreSQL versi 14, 15, atau 16
- **Web Server**: Nginx, Apache, atau Laragon
- **Node.js**: Versi 18+ LTS (jika membangun asset frontend)
- **Composer**: Versi 2.x

---

## 2. Langkah Instalasi Awal

### 2.1 Clone & Setup Dependencies
```bash
git clone <repository_url> seafood-system
cd seafood-system

composer install --no-dev --optimize-autoloader
```

### 2.2 Konfigurasi Environment File
Salin template environment:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan parameter database PostgreSQL pada `.env`:
```env
APP_NAME="Seafood RMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pos.seafoodresto.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=seafood_pos
DB_USERNAME=postgres
DB_PASSWORD=secret_password

BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=ap1
PUSHER_KDS_CHANNEL=pos-seafood-kds-production

SANCTUM_EXPIRATION=1440
```

---

## 3. Database Migration & Initial Seeding

Jalankan migrasi skema database beserta data awal (roles, permissions, dan katalog awal):

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=PermissionSeeder --force
```

---

## 4. Optimasi Performa Production (Mandatory)

Sebelum menerima traffic transaksi restoran, jalankan kompilasi cache framework:

```bash
# Cache konfigurasi aplikasi
php artisan config:cache

# Cache rute API
php artisan route:cache

# Optimasi autoloader Composer
composer dump-autoload --optimize --no-dev
```

Untuk membersihkan cache saat update deployment:
```bash
php artisan optimize:clear
```

---

## 5. Konfigurasi Nginx (Contoh Virtual Host)

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name pos.seafoodresto.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name pos.seafoodresto.com;
    root /var/www/seafood-system/public;

    ssl_certificate /etc/letsencrypt/live/pos.seafoodresto.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pos.seafoodresto.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 6. Background Queue Worker (Supervisor)

Jika menggunakan pemrosesan asynchronous untuk event atau email, pasang Supervisor pada `/etc/supervisor/conf.d/seafood-worker.conf`:

```ini
[program:seafood-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/seafood-system/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/seafood-system/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 7. Verifikasi Kesiapan Deployment (Health Check)

Jalankan perintah pengujian internal sebelum sistem dibuka untuk kasir dan dapur:

```bash
# Verifikasi endpoint health check Laravel
curl -I https://pos.seafoodresto.com/up

# Verifikasi test suite lokal
php artisan test
```
Semua 41 tests harus berstatus **PASS**.

