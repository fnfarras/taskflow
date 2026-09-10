# AGENTS.md — TaskFlow Project 
Standar dan aturan ini berlaku untuk semua agent (Claude, Gemini) yang bekerja di project TaskFlow (backend Laravel + frontend React). Tujuannya: kode yang dihasilkan setara standar profesional/perusahaan, bukan standar tugas kuliah.

# Konteks Proyek

TaskFlow adalah aplikasi task/project management (mirip Trello sederhana) dengan entitas utama: User, Project, ProjectMember (pivot dengan role owner/member), Task, TaskComment.

Backend (Laravel)
Arsitektur
Gunakan pola Controller (tipis) → Service (business logic) → Repository (query database). Jangan taruh logic bisnis langsung di Controller atau Model.
Struktur folder wajib:
  app/Http/Controllers/Api/
  app/Http/Requests/
  app/Http/Resources/
  app/Services/
  app/Repositories/
  app/Models/
  app/Exceptions/
Auth & Otorisasi
Autentikasi pakai Laravel Sanctum.
Role & permission pakai package Spatie Laravel-Permission, diterapkan per-project (bukan role global) — cek keanggotaan user di ProjectMember sebelum akses resource.
Setiap endpoint yang butuh login WAJIB dilindungi middleware auth:sanctum.
Validasi & Response
Validasi input SELALU pakai Form Request (app/Http/Requests/), jangan validasi manual di controller.
Format response API konsisten:
json
  { "success": true, "data": {}, "message": "..." }
Error handling terpusat lewat app/Exceptions/Handler.php, hasilnya juga harus format JSON konsisten di atas.
Gunakan API Resource (app/Http/Resources/) untuk transformasi output, jangan return Model mentah.
Database
Setiap migration wajib ada foreign key constraint yang relevan dan index pada kolom yang sering di-query (mis. status, assignee_id).
Gunakan factory & seeder untuk data dummy, jangan insert manual.
Testing
Gunakan Pest, bukan PHPUnit style lama.
Setiap endpoint minimal punya 1 test happy path dan 1 test failure case (validasi gagal / unauthorized).
Kualitas Kode
Jalankan Laravel Pint sebelum commit.
Ikuti konvensi REST (GET /api/v1/projects, bukan /api/getProjects). Semua route diprefix /api/v1.
Frontend (React)
Arsitektur
Struktur folder berbasis fitur, bukan berbasis tipe file:
  src/features/auth/
  src/features/projects/
  src/features/tasks/
  src/components/ui/
  src/hooks/
  src/services/api/
  src/store/
State management pakai Zustand, jangan useState berserakan untuk state yang dipakai lintas komponen.
Form & validasi pakai React Hook Form + Zod, skema validasi harus selaras dengan validasi backend.
Panggilan API lewat instance axios terpusat di src/services/api/ dengan interceptor untuk attach token otomatis.
UI/UX
Gunakan Tailwind CSS untuk styling.
Loading state pakai skeleton, bukan teks "Loading...".
Selalu tampilkan pesan error yang jelas dari response API, jangan pesan generik.
Pastikan responsive (mobile & desktop).
Kualitas Kode
Jalankan ESLint + Prettier sebelum commit.
Git & Workflow
Commit message pakai format Conventional Commits (feat:, fix:, refactor:, test:, docs:).
Buat branch per fitur (feature/auth, feature/task-crud), jangan langsung commit ke main.
Jangan pernah commit file .env — pastikan ada di .gitignore.
Proses Kerja dengan Agent
Kerjakan satu task per prompt (jangan minta banyak fitur sekaligus dalam satu instruksi).
Untuk task kompleks, gunakan Planning Mode dan review rencana sebelum eksekusi.
Setelah kode digenerate, jelaskan secara singkat apa yang diubah dan mengapa — supaya developer (saya) tetap paham keseluruhan kode dan bisa menjelaskannya saat interview kerja.