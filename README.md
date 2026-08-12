# Project Percobaan — Laravel Blog

Sebuah aplikasi **blog management system** sederhana yang dibangun dengan **Laravel 12**, mengikuti kursus [Laravel from Scratch (Laravel Daily)](https://laraveldaily.com/course/laravel-from-scratch).

## Fitur

**Halaman Publik**
- Halaman beranda dengan daftar post terbaru (`/`)
- Sidebar kategori; filter post per kategori via `?category_id=`
- Halaman detail post dengan route model binding (`/posts/{post}`)
- Halaman statis Contact (`/contact`) dan About (`/about`)

**Autentikasi (Laravel Breeze)**
- Login, register, logout
- Verifikasi email, reset & konfirmasi password
- Profil user (update info, ganti password, hapus akun)

**Admin Panel** (dilindungi middleware `auth` + `verified`)
- CRUD Kategori (`/admin/categories`)
- CRUD Post (`/admin/posts`) dengan eager loading relasi kategori
- Validasi form + pesan error di semua form

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 12 (PHP 8.2) |
| Database | MySQL (default), SQLite untuk testing |
| Auth | Laravel Breeze (Blade) |
| Styling | Tailwind CSS (CDN untuk halaman blog, Vite untuk Breeze) |
| Build | Vite |

## Instalasi

```bash
# 1. Install dependency PHP
composer install

# 2. Install dependency JS & build aset
npm install
npm run build

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi .env (database MySQL)
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=laravel
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Migrasi + seed data awal (kategori & post contoh + user admin)
php artisan migrate
php artisan db:seed

# 6. Jalankan server
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun Admin (hasil seeder)

| Field | Nilai |
|-------|-------|
| Email | `admin@example.com` |
| Password | `password` |

## Rute Utama

| Method | URI | Nama | Keterangan |
|--------|-----|------|------------|
| GET | `/` | `home` | Beranda blog |
| GET | `/posts/{post}` | `posts.show` | Detail post |
| GET | `/login` | `login` | Login |
| GET | `/register` | `register` | Register |
| GET | `/admin/categories` | `admin.categories.index` | CRUD kategori (auth) |
| GET | `/admin/posts` | `admin.posts.index` | CRUD post (auth) |
| GET | `/dashboard` | `dashboard` | Dashboard user (auth) |

## Menjalankan Test

```bash
php artisan test
```

Test memakai database SQLite terpisah (`database/testing.sqlite`) sehingga data database development (MySQL) tidak terganggu.

## Struktur Folder

```
app/
  Http/Controllers/
    HomeController.php          # Halaman publik (index + show post)
    Admin/
      CategoryController.php    # CRUD kategori
      PostController.php        # CRUD post
  Models/
    Category.php                # relasi hasMany(Post)
    Post.php                    # relasi belongsTo(Category)
database/
  migrations/                   # users, cache, jobs, categories, posts
  seeders/DatabaseSeeder.php    # user admin + 8 kategori + 2 post contoh
resources/views/
  layouts/blog.blade.php        # layout halaman publik
  home.blade.php                # beranda
  posts/show.blade.php          # detail post
  admin/categories/             # views CRUD kategori
  admin/posts/                  # views CRUD post
routes/web.php                  # semua rute
tests/Feature/AdminCrudTest.php # test CRUD + validasi + auth
```

## Lisensi

Project ini dibuat untuk pembelajaran mengikuti kursus Laravel Daily — [Laravel from Scratch](https://laraveldaily.com/course/laravel-from-scratch).
