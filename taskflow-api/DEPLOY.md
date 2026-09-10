# TaskFlow API — Railway Deployment Guide

Panduan deploy backend Laravel ke **Railway** (https://railway.app).

---

## 1. Prasyarat

- Akun Railway (https://railway.app)
- GitHub repo sudah di-push
- PHP 8.2+ dan Composer terinstall di lokal

---

## 2. Setup di Railway Dashboard

### 2.1 Buat Project Baru

1. Login ke Railway → **New Project**
2. Pilih **Deploy from GitHub repo**
3. Pilih repo `taskflow` → pilih folder `taskflow-api` sebagai **Root Directory**

### 2.2 Tambahkan Database

1. Di dashboard project → **Add Service** → **Database** → **MySQL** (atau PostgreSQL)
2. Railway otomatis men-generate `DATABASE_URL` dan `MYSQL_*` variables

---

## 3. Environment Variables

Buka tab **Variables** di Railway service, tambahkan semua variabel berikut:

```env
# Application
APP_NAME=TaskFlow
APP_ENV=production
APP_KEY=                          # Diisi dari: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://YOUR-RAILWAY-DOMAIN.up.railway.app

# Database (Railway auto-inject ini jika pakai MySQL addon)
DB_CONNECTION=mysql
DB_HOST=${MYSQLHOST}
DB_PORT=${MYSQLPORT}
DB_DATABASE=${MYSQLDATABASE}
DB_USERNAME=${MYSQLUSER}
DB_PASSWORD=${MYSQLPASSWORD}

# Sanctum — daftar domain frontend yang boleh kirim request berkredensial
SANCTUM_STATEFUL_DOMAINS=your-frontend.vercel.app,localhost:5173

# CORS — origin yang diizinkan
FRONTEND_URL=https://your-frontend.vercel.app

# Session & Cache (pakai database atau redis)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=error

# Mail (opsional, gunakan Mailtrap/Resend untuk production)
MAIL_MAILER=log
```

> [!IMPORTANT]
> **APP_KEY** wajib diisi. Jalankan `php artisan key:generate --show` di lokal dan copy hasilnya.

---

## 4. Nixpacks / Build Config

Railway menggunakan **Nixpacks** untuk auto-detect PHP. Tambahkan file `railway.json` di root `taskflow-api/`:

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=$PORT",
    "healthcheckPath": "/api/v1/health",
    "healthcheckTimeout": 300,
    "restartPolicyType": "ON_FAILURE"
  }
}
```

---

## 5. CORS Configuration

Edit `config/cors.php` — pastikan `allowed_origins` mengizinkan domain Vercel:

```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
```

---

## 6. Checklist Deploy

- [ ] `APP_KEY` sudah diisi
- [ ] `DB_*` sudah terhubung ke Railway MySQL
- [ ] `SANCTUM_STATEFUL_DOMAINS` sudah berisi domain Vercel
- [ ] `FRONTEND_URL` sudah diisi dengan URL Vercel
- [ ] Migration berjalan sukses (cek logs Railway)
- [ ] Test endpoint: `GET https://YOUR-DOMAIN.up.railway.app/api/v1/me` → harus return 401
