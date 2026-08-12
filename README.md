# Project Percobaan — Laravel Blog

Aplikasi **blog management system** yang dibangun dengan **Laravel 12** (PHP 8.2), mengikuti kursus [Laravel from Scratch](https://laraveldaily.com/course/laravel-from-scratch) oleh Laravel Daily. Project ini belajar dari nol: routing, Blade layouts, migrasi database, Eloquent ORM, autentikasi, hingga CRUD admin lengkap.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Memulai Cepat](#memulai-cepat)
- [Dokumentasi Lengkap](#dokumentasi-lengkap)
- [Akun Admin Default](#akun-admin-default)
- [Lisensi](#lisensi)

---

## Fitur Utama

| Area | Fitur |
|------|-------|
| **Publik** | Beranda daftar post terbaru, sidebar kategori, filter post per kategori, halaman detail post, halaman Contact & About |
| **Autentikasi** | Login, register, logout, verifikasi email, reset & konfirmasi password, kelola profil |
| **Admin** | CRUD kategori (index/create/edit/delete), CRUD post dengan eager loading, validasi form + pesan error |
| **Keamanan** | Route admin dilindungi middleware `auth` & `verified`, CSRF protection, mass-assignment protection (`$fillable`) |

---

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 12.65 (PHP 8.2.12) |
| Database | MySQL (development), SQLite (testing) |
| Autentikasi | Laravel Breeze 2.x (Blade) |
| Styling | Tailwind CSS (CDN di layout blog, Vite di layout Breeze) |
| Build tool | Vite 6 + laravel-vite-plugin |

---

## Memulai Cepat

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
# sesuaikan kredensial DB di .env
php artisan migrate
php artisan db:seed
php artisan serve
```

Buka `http://127.0.0.1:8000`. Penjelasan langkah demi langkah ada di [docs/installation.md](docs/installation.md).

---

## Dokumentasi Lengkap

Dokumentasi project ini dipecah menjadi beberapa file agar mudah dinavigasi:

| File | Isi |
|------|-----|
| [docs/installation.md](docs/installation.md) | Instalasi detail, konfigurasi environment, migrasi & seeding, menjalankan server, troubleshooting umum |
| [docs/architecture.md](docs/architecture.md) | Arsitektur MVC, skema database lengkap, seluruh rute, penjelasan tiap controller, model & relasi, struktur views |
| [docs/features.md](docs/features.md) | Penjelasan detail setiap fitur: halaman publik, autentikasi, admin CRUD, validasi, keamanan |
| [docs/testing.md](docs/testing.md) | Cara menjalankan test, struktur test, dan mengapa data development aman saat test |

---

## Akun Admin Default

Setelah menjalankan `php artisan db:seed`, tersedia akun:

| Field | Nilai |
|-------|-------|
| Email | `admin@example.com` |
| Password | `password` |

Gunakan untuk login di `/login` lalu akses panel admin di `/admin/categories` dan `/admin/posts`.

---

## Lisensi

Project pembelajaran mengikuti kursus Laravel Daily — [Laravel from Scratch](https://laraveldaily.com/course/laravel-from-scratch). Tidak untuk tujuan komersial.
