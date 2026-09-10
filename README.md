<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black" alt="React">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://github.com/fnfarras/taskflow/actions/workflows/tests.yml/badge.svg" alt="Tests">
</p>

<h1 align="center">TaskFlow</h1>
<p align="center">
  Aplikasi manajemen project dan task berbasis web — full-stack Laravel API + React SPA.
  <br/>
  <a href="https://YOUR-FRONTEND.vercel.app"><strong>Live Demo »</strong></a>
  &nbsp;·&nbsp;
  <a href="taskflow-api/DEPLOY.md">Deployment Guide</a>
</p>

---

## 📋 Deskripsi

**TaskFlow** adalah aplikasi manajemen project dan task bergaya Kanban yang memungkinkan tim untuk:

- 📁 **Mengelola project** — buat, edit, dan hapus project
- 👥 **Kolaborasi tim** — invite member via email, role-based access (owner / member)  
- ✅ **Task tracking** — buat task dengan priority, assignee, dan due date
- 📊 **Kanban board** — visualisasi task dalam 3 kolom: **To Do**, **In Progress**, **Done**
- 🔐 **Auth aman** — token-based auth via Laravel Sanctum dengan rate limiting

---

## ✨ Fitur Utama

| Fitur | Keterangan |
|---|---|
| Register & Login | Token-based auth dengan Laravel Sanctum |
| Dashboard Project | Grid card semua project yang dimiliki/diikuti user |
| Kanban Board | 3 kolom per project, update status via modal |
| Task Management | Create, edit, delete task dengan priority & due date |
| Assignee | Assign task ke member project |
| Member Management | Owner dapat invite member baru via email |
| Role-based Access | Owner punya akses penuh; member hanya bisa baca & edit task |
| API Tests | 113 test cases, 268 assertions (Pest) |

---

## 🛠️ Tech Stack

### Backend (`taskflow-api/`)

| Teknologi | Versi | Fungsi |
|---|---|---|
| **Laravel** | 12 | PHP framework |
| **Laravel Sanctum** | 4 | API token authentication |
| **Spatie Laravel Permission** | — | Role & permission management |
| **Pest** | 3 | Testing framework |
| **Laravel Pint** | — | Code style (PSR-12) |
| **SQLite** | — | Database (local), MySQL (production) |

Arsitektur: **Controller → Service → Model** dengan Form Request, API Resource, dan Policy.
> Repository layer sengaja tidak digunakan — abstraksi ini tidak diperlukan untuk aplikasi CRUD seukuran TaskFlow dan justru menambah complexity tanpa manfaat nyata.

### Frontend (`taskflow-web/`)

| Teknologi | Versi | Fungsi |
|---|---|---|
| **React** | 19 | UI framework |
| **Vite** | 8 | Build tool & dev server |
| **Tailwind CSS** | v4 | Utility-first CSS |
| **React Router** | 7 | Client-side routing |
| **Zustand** | — | Global state (auth token) |
| **TanStack React Query** | — | Server state, caching, auto-refetch |
| **React Hook Form + Zod** | — | Form validation |
| **Axios** | — | HTTP client dengan interceptors |

---

## 🗃️ ERD (Entity Relationship Diagram)

```
┌──────────────────┐         ┌────────────────────────┐
│      users       │         │     project_members     │
├──────────────────┤   1   * ├────────────────────────┤
│ id               │─────────│ id                     │
│ name             │         │ project_id  (FK)       │
│ email (unique)   │         │ user_id     (FK)       │
│ password         │         │ role (owner|member)    │
│ created_at       │         │ created_at             │
│ updated_at       │         │ updated_at             │
└──────────────────┘         └────────────────────────┘
        │                                │
        │ 1                           *  │
        │                                │
        ▼                                ▼
┌──────────────────┐         ┌────────────────────────┐
│     projects     │   1   * │        tasks           │
├──────────────────┤─────────├────────────────────────┤
│ id               │         │ id                     │
│ name             │         │ project_id  (FK)       │
│ description      │         │ assignee_id (FK→users) │
│ owner_id  (FK)   │         │ title                  │
│ created_at       │         │ description            │
│ updated_at       │         │ status (todo|in_progress|done) │
└──────────────────┘         │ priority (low|medium|high)     │
                             │ due_date               │
        ┌────────────────────│ deleted_at (soft)      │
        │                    │ created_at             │
        │  1              *  │ updated_at             │
        │                    └────────────────────────┘
        ▼                              │
┌──────────────────┐                  │
│  task_comments   │   *              │
├──────────────────│──────────────────┘
│ id               │
│ task_id   (FK)   │
│ user_id   (FK)   │
│ content          │
│ created_at       │
│ updated_at       │
└──────────────────┘
```

---

## 🚀 Instalasi & Menjalankan Lokal

### Prasyarat

- **PHP** >= 8.2 + Composer
- **Node.js** >= 18 + npm
- **SQLite** (sudah bundled di PHP)

### 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/taskflow.git
cd taskflow
```

### 2. Setup Backend

```bash
cd taskflow-api

# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Buat file SQLite dan jalankan migration
touch database/database.sqlite
php artisan migrate

# (Opsional) Isi data dummy
php artisan db:seed

# Jalankan dev server
php artisan serve
# → API tersedia di http://localhost:8000
```

### 3. Setup Frontend

```bash
cd ../taskflow-web

# Install Node dependencies
npm install

# Setup environment
cp .env.example .env
# Edit .env — pastikan VITE_API_BASE_URL=http://localhost:8000/api/v1

# Jalankan dev server
npm run dev
# → App tersedia di http://localhost:5173
```

### 4. Jalankan Tests Backend

```bash
cd taskflow-api
php artisan test
# Hasil: 113 passed (268 assertions)
```

---

## 📁 Struktur Project

```
taskflow/
├── .github/
│   └── workflows/
│       └── tests.yml          # GitHub Actions CI
├── taskflow-api/              # Backend Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Services/          # Business logic layer
│   │   ├── Models/
│   │   └── Policies/          # Authorization
│   ├── tests/
│   │   └── Feature/
│   │       ├── Auth/          # 21 tests
│   │       ├── Project/       # 44 tests
│   │       └── Task/          # 47 tests + 1 example
│   ├── railway.json           # Railway deployment config
│   └── DEPLOY.md              # Panduan deploy
└── taskflow-web/              # Frontend React
    ├── src/
    │   ├── features/
    │   │   ├── auth/          # Login, Register
    │   │   ├── projects/      # Dashboard, ProjectDetail
    │   │   └── tasks/         # Kanban, TaskCard, TaskModal
    │   ├── components/ui/     # Input, Button, Modal, Badge, Avatar, ...
    │   ├── hooks/             # useProjects, useProject, useTasks
    │   ├── services/api/      # Axios client + API functions
    │   └── store/             # Zustand auth store
    └── vercel.json            # Vercel deployment config
```

---

## 🌐 API Endpoints

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `POST` | `/api/v1/register` | Register user baru | ❌ |
| `POST` | `/api/v1/login` | Login, return token | ❌ |
| `POST` | `/api/v1/logout` | Revoke token | ✅ |
| `GET` | `/api/v1/me` | Data user aktif | ✅ |
| `GET` | `/api/v1/projects` | List project user | ✅ |
| `POST` | `/api/v1/projects` | Buat project baru | ✅ |
| `GET` | `/api/v1/projects/:id` | Detail project | ✅ owner/member |
| `PUT` | `/api/v1/projects/:id` | Update project | ✅ owner |
| `DELETE` | `/api/v1/projects/:id` | Hapus project | ✅ owner |
| `POST` | `/api/v1/projects/:id/members` | Tambah member | ✅ owner |
| `GET` | `/api/v1/projects/:id/tasks` | List task (+ filter) | ✅ owner/member |
| `POST` | `/api/v1/projects/:id/tasks` | Buat task baru | ✅ owner/member |
| `GET` | `/api/v1/tasks/:id` | Detail task | ✅ owner/member |
| `PUT` | `/api/v1/tasks/:id` | Update task | ✅ owner/member |
| `DELETE` | `/api/v1/tasks/:id` | Soft delete task | ✅ owner/member |

---

## 🚢 Deployment

| Layanan | Platform | Panduan |
|---|---|---|
| Backend API | [Railway](https://railway.app) | [taskflow-api/DEPLOY.md](taskflow-api/DEPLOY.md) |
| Frontend SPA | [Vercel](https://vercel.com) | Set `VITE_API_BASE_URL` di Vercel Environment Variables |

**Live Demo:** [https://YOUR-FRONTEND.vercel.app](https://YOUR-FRONTEND.vercel.app) *(isi setelah deploy)*

---

## 📄 Lisensi

MIT © 2024 — dibuat sebagai project portfolio full-stack.
