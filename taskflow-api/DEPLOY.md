# TaskFlow — Production Deployment Guide

Panduan lengkap deployment TaskFlow ke cloud:
1. **Backend (Laravel API) + Database (PostgreSQL)** di [Railway](https://railway.app)
2. **Frontend (React SPA)** di [Vercel](https://vercel.com)

---

## BAGIAN 1: Deploy Backend & PostgreSQL di Railway

### 1.1 Buat Project & Tambahkan PostgreSQL
1. Buka [railway.app](https://railway.app) dan login menggunakan akun GitHub Anda.
2. Klik **+ New Project** → pilih **Provision PostgreSQL**.
3. Railway akan otomatis membuat database PostgreSQL. 
   *(Variabel koneksi seperti `PGHOST`, `PGPORT`, `PGUSER`, `PGPASSWORD`, `PGDATABASE`, dan `DATABASE_URL` otomatis dibuat oleh Railway).*

### 1.2 Tambahkan Service Laravel API
1. Di dalam project yang sama di Railway, klik **+ Create** (atau **Add Service**) → pilih **GitHub Repo**.
2. Pilih repository: `fnfarras/taskflow`.
3. Klik service repo yang baru dibuat, masuk ke tab **Settings**:
   - Cari bagian **Root Directory** → ubah menjadi `/taskflow-api` (lalu save).
   - Di bagian **Networking** → klik **Generate Domain** untuk mendapatkan URL publik (misal: `taskflow-api-production.up.railway.app`).

### 1.3 Konfigurasi Environment Variables di Railway
Masuk ke tab **Variables** pada service `taskflow-api`, klik **Raw Editor** (atau tambahkan satu per satu) dan masukkan:

```env
# Application
APP_NAME=TaskFlow
APP_ENV=production
APP_KEY=base64:HMVDNrzsqGtFWG1D2WtoHynsOgRUfmLjF/X60lIo2ek=
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}

# Database (PostgreSQL terhubung otomatis ke service Postgres di project yang sama)
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# CORS & Sanctum — diisi domain Vercel frontend Anda (setelah deploy Bagian 2)
FRONTEND_URL=https://YOUR-APP.vercel.app
SANCTUM_STATEFUL_DOMAINS=YOUR-APP.vercel.app

# Cache & Session
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=error
```

> [!TIP]
> Jika Railway tidak auto-reference nama `Postgres`, Anda bisa memilih **Add Reference** saat mengetik variabel database, atau gunakan nilai langsung dari tab **Connect** service PostgreSQL.

### 1.4 Seed Data Demo di Railway (Opsional)
Setelah deploy sukses dan migrasi selesai, Anda bisa mengisi data demo realistis:
1. Klik service `taskflow-api` → buka tab **Exec** (terminal web).
2. Jalankan:
   ```bash
   php artisan db:seed --force
   ```

---

## BAGIAN 2: Deploy Frontend di Vercel

### 2.1 Import Project ke Vercel
1. Buka [vercel.com](https://vercel.com) dan login dengan akun GitHub.
2. Klik **Add New...** → **Project**.
3. Cari repository `taskflow` → klik **Import**.

### 2.2 Konfigurasi Build & Environment Variables
Di layar konfigurasi Vercel:
1. **Framework Preset**: Pilih **Vite**.
2. **Root Directory**: Klik **Edit** dan pilih folder **`taskflow-web`**.
3. **Environment Variables**:
   Tambahkan variabel berikut:
   - **Key**: `VITE_API_BASE_URL`
   - **Value**: `https://NAMA-DOMAIN-RAILWAY-ANDA.up.railway.app/api/v1`  
     *(Ganti dengan domain Railway backend yang didapat dari langkah 1.2).*
4. Klik tombol **Deploy**.

---

## BAGIAN 3: Menghubungkan Keduanya (Final Sync)

1. Salin domain Vercel Anda (misal: `https://taskflow-fnfarras.vercel.app`).
2. Kembali ke Railway → tab **Variables** service `taskflow-api`.
3. Perbarui 2 variabel berikut agar tidak terkena blokir CORS:
   - `FRONTEND_URL` = `https://taskflow-fnfarras.vercel.app`
   - `SANCTUM_STATEFUL_DOMAINS` = `taskflow-fnfarras.vercel.app`
4. Railway akan otomatis me-redeploy dalam hitungan detik.

---

## Verifikasi Deployment Selesai

1. Buka domain frontend Vercel Anda di browser.
2. Coba login dengan akun demo:
   - **Email**: `alice@taskflow.dev`
   - **Password**: `password`
3. Selamat! Aplikasi TaskFlow Anda sudah live di cloud secara profesional!
