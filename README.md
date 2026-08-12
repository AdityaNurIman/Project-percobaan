# Project Percobaan — Laravel Blog

Aplikasi **blog management system** yang dibangun dengan **Laravel 12** (PHP 8.2), mengikuti kursus [Laravel from Scratch](https://laraveldaily.com/course/laravel-from-scratch) oleh Laravel Daily. Project ini dipelajari dari nol: routing, Blade layouts, migrasi database, Eloquent ORM, autentikasi, hingga CRUD admin lengkap.

---

## Daftar Isi

1. [Fitur Utama](#1-fitur-utama)
2. [Tech Stack](#2-tech-stack)
3. [Persyaratan](#3-persyaratan)
4. [Instalasi Lengkap](#4-instalasi-lengkap)
5. [Skema Database](#5-skema-database)
6. [Arsitektur & Struktur](#6-arsitektur--struktur)
7. [Rute Lengkap](#7-rute-lengkap)
8. [Controller & Model](#8-controller--model)
9. [Struktur Views](#9-struktur-views)
10. [Fitur Publik](#10-fitur-publik)
11. [Autentikasi](#11-autentikasi)
12. [Panel Admin & CRUD](#12-panel-admin--crud)
13. [Validasi Form](#13-validasi-form)
14. [Keamanan](#14-keamanan)
15. [Testing](#15-testing)
16. [Troubleshooting](#16-troubleshooting)
17. [Akun Admin Default](#17-akun-admin-default)

---

## 1. Fitur Utama

| Area | Fitur |
|------|-------|
| **Publik** | Beranda daftar post terbaru, sidebar kategori, filter post per kategori, halaman detail post (route model binding), halaman Contact & About |
| **Autentikasi** | Login, register, logout, verifikasi email, reset & konfirmasi password, kelola profil |
| **Admin** | CRUD kategori (index/create/edit/delete), CRUD post dengan eager loading, validasi form + pesan error |
| **Keamanan** | Route admin dilindungi middleware `auth` & `verified`, CSRF protection, mass-assignment protection (`$fillable`) |

---

## 2. Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 12.65 (PHP 8.2.12) |
| Database | MySQL (development), SQLite (testing) |
| Autentikasi | Laravel Breeze 2.x (Blade) |
| Styling | Tailwind CSS (CDN di layout blog, Vite di layout Breeze) |
| Build tool | Vite 6 + laravel-vite-plugin |

---

## 3. Persyaratan

| Tool | Versi Minimum | Cek |
|------|--------------|-----|
| PHP | 8.2 | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |
| MySQL | 8.x | `mysql --version` |
| Git | 2.x | `git --version` |

Ekstensi PHP yang harus aktif di `php.ini`: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `fileinfo`, `bcmath`, `sqlite3` (untuk testing).

---

## 4. Instalasi Lengkap

### 4.1 Clone & Install Dependency

```bash
git clone https://github.com/AdityaNurIman/Project-percobaan.git
cd Project-percobaan
composer install        # dependency PHP ke vendor/
npm install             # dependency JS
npm run build           # kompilasi CSS/JS ke public/build
```

> **Catatan:** Halaman publik memakai Tailwind CDN, sedangkan halaman Breeze (auth/admin) memakai aset Vite. `npm run build` wajib dijalankan agar halaman auth & admin tidak error 404 pada file CSS/JS.

### 4.2 Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env`:

```dotenv
APP_NAME="Project Percobaan"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Buat database di MySQL:

```sql
CREATE DATABASE laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4.3 Migrasi & Seed

```bash
php artisan migrate
php artisan migrate:status   # cek status migrasi
php artisan db:seed
```

Seeder membuat: 1 akun admin, 8 kategori contoh, dan 2 post contoh.

### 4.4 Jalankan Server

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Jika port 8000 terpakai: `php artisan serve --port=8080`.

---

## 5. Skema Database

### Tabel `categories`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto increment |
| `name` | varchar(255) | Nama kategori |
| `slug` | varchar(255) | Slug unik (untuk URL) |
| `description` | text, nullable | Deskripsi kategori |
| `created_at` / `updated_at` | timestamp | Timestamps otomatis |

Relasi: **satu kategori → banyak post** (`hasMany`).

### Tabel `posts`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto increment |
| `title` | varchar(255) | Judul post |
| `text` | text | Isi/deskripsi post |
| `category_id` | bigint (FK) | Foreign key → `categories.id` (`ON DELETE CASCADE`) |
| `created_at` / `updated_at` | timestamp | Timestamps otomatis |

Relasi: **satu post → satu kategori** (`belongsTo`).

### Tabel pendukung (Laravel default)
`users`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`.

---

## 6. Arsitektur & Struktur

### Alur Request (MVC)

```
Browser ──GET /──▶ routes/web.php ──▶ HomeController@index ──▶ Model (Eloquent) ──▶ DB
   ▲                                                                                │
   └───────────── HTML (Blade) ◀─────── resources/views/home.blade.php ◀────────────┘
```

1. Request masuk ke `public/index.php` → framework bootstrap.
2. `routes/web.php` mencocokkan URL & method dengan rute terdaftar.
3. Controller dipanggil; controller mengambil data lewat Eloquent Model.
4. Data dikirim ke Blade view untuk dirender.

### Struktur Folder

```
app/
├── Http/Controllers/
│   ├── HomeController.php          # Halaman publik (index + show post)
│   ├── Admin/
│   │   ├── CategoryController.php  # CRUD kategori
│   │   └── PostController.php      # CRUD post
│   └── Auth/ ...                   # Controller Breeze
├── Models/
│   ├── Category.php                # relasi hasMany(Post)
│   └── Post.php                    # relasi belongsTo(Category)
database/
├── migrations/                     # users, cache, jobs, categories, posts
└── seeders/DatabaseSeeder.php      # user admin + 8 kategori + 2 post
resources/views/
├── layouts/blog.blade.php          # layout halaman publik
├── home.blade.php                  # beranda
├── posts/show.blade.php            # detail post
├── admin/categories/               # index, create, edit
├── admin/posts/                    # index, create, edit
└── ...
routes/web.php                      # semua rute
tests/Feature/AdminCrudTest.php     # test CRUD + validasi + auth
```

---

## 7. Rute Lengkap

### Publik (tanpa login)

| Method | URI | Controller / Aksi | Nama |
|--------|-----|-------------------|------|
| GET | `/` | `HomeController@index` | `home` |
| GET | `/posts/{post}` | `HomeController@show` | `posts.show` |
| GET | `/contact` | `Route::view` | `contact` |
| GET | `/about` | `Route::view` | `about` |
| GET | `/dashboard` | Closure | `dashboard` |

### Autentikasi (Breeze, `routes/auth.php`)

| Method | URI | Nama |
|--------|-----|------|
| GET/POST | `/login` | `login` |
| GET/POST | `/register` | `register` |
| POST | `/logout` | `logout` |
| GET | `/forgot-password` | `password.request` |
| GET/POST | `/reset-password/{token}` | `password.reset` |
| GET | `/verify-email` | `verification.notice` |
| GET/POST | `/email/verification-notification` | `verification.send` |
| POST | `/confirm-password` | `password.confirm` |

### Admin (middleware `auth` + `verified`, prefix `/admin`, nama `admin.*`)

| Method | URI | Controller | Nama |
|--------|-----|-----------|------|
| GET | `/admin/categories` | `Admin\CategoryController@index` | `admin.categories.index` |
| GET | `/admin/categories/create` | `@create` | `admin.categories.create` |
| POST | `/admin/categories` | `@store` | `admin.categories.store` |
| GET | `/admin/categories/{category}/edit` | `@edit` | `admin.categories.edit` |
| PUT/PATCH | `/admin/categories/{category}` | `@update` | `admin.categories.update` |
| DELETE | `/admin/categories/{category}` | `@destroy` | `admin.categories.destroy` |

(Pola yang sama berlaku untuk `admin.posts.*`.)

### Profil (middleware auth)

| Method | URI | Controller | Nama |
|--------|-----|-----------|------|
| GET | `/profile` | `ProfileController@edit` | `profile.edit` |
| PATCH | `/profile` | `ProfileController@update` | `profile.update` |
| DELETE | `/profile` | `ProfileController@destroy` | `profile.destroy` |

---

## 8. Controller & Model

### `HomeController` — halaman publik blog

```php
public function index(Request $request)
{
    $categories = Category::all();

    $posts = Post::when($request->has('category_id'), function ($query) use ($request) {
        $query->where('category_id', $request->category_id);
    })->latest()->get();

    return view('home', compact('categories', 'posts'));
}

public function show(Post $post)  // Route Model Binding
{
    $categories = Category::all();
    return view('posts.show', compact('post', 'categories'));
}
```

- **Route Model Binding**: parameter `{post}` otomatis di-resolve menjadi instance `Post`; jika tidak ditemukan → 404.
- **Filter kategori**: `when()` hanya menambahkan `where('category_id', ...)` jika parameter `category_id` ada.

### `Admin\CategoryController` — CRUD kategori
`index()` ambil semua kategori + `withCount('posts')`; `store()` validasi + buat slug dari nama; `update()` validasi + perbarui; `destroy()` hapus.

### `Admin\PostController` — CRUD post
`index()` memakai `Post::with('category')` (eager loading, mencegah N+1 query); `store()`/`update()` validasi `title`, `text`, `category_id`.

### Model & Relasi

```php
// app/Models/Category.php
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// app/Models/Post.php
class Post extends Model
{
    protected $fillable = ['title', 'text', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

- `$fillable` membatasi kolom yang boleh diisi mass-assignment (proteksi keamanan).
- Relasi memungkinkan akses seperti `$post->category->name` dan `$category->posts`.

---

## 9. Struktur Views

```
resources/views/
├── layouts/
│   ├── blog.blade.php          # Layout publik (Tailwind CDN): header My Blog + About/Contact
│   ├── app.blade.php           # Layout Breeze (<x-app-layout>)
│   ├── guest.blade.php         # Layout halaman login/register
│   └── navigation.blade.php    # Navigasi admin (Dashboard, Categories, Posts)
├── home.blade.php              # Beranda: daftar post + sidebar kategori
├── posts/show.blade.php        # Detail post
├── admin/
│   ├── categories/             # index, create, edit
│   └── posts/                  # index, create, edit
├── auth/                       # login, register, forgot/reset password, verify-email
├── profile/                    # kelola profil (update, password, delete)
├── components/                 # Komponen Blade Breeze
└── dashboard.blade.php         # Dashboard user
```

Layout publik memakai `@yield('content')` / `@section('content')` klasik:

```blade
@extends('layouts.blog')

@section('content')
    <main>...</main>
@endsection
```

> **Catatan penting:** jangan lupa menutup blok `@section` dengan `@endsection`. Jika lupa, output buffer section tetap terbuka dan layout bisa terekam di posisi yang salah.

---

## 10. Fitur Publik

### Beranda (`/`)
Menampilkan **"Latest Posts"** dan daftar post terbaru (`latest()`). Setiap item menampilkan gambar placeholder, judul (link ke detail), dan cuplikan teks (`substr($post->text, 0, 50)` + `...`).

### Sidebar Kategori
Filter post via GET parameter: `/?category_id=3`. Kategori aktif ditandai latar gelap:

```blade
class="... {{ request('category_id') == $category->id ? 'bg-gray-800 text-white' : 'text-gray-600' }}"
```

### Detail Post (`/posts/{id}`)
Judul, nama kategori (link kembali ke filter), gambar, isi penuh, dan tombol **"← Back to posts"**. Post tidak ditemukan → 404.

### Halaman Statis
`/about`, `/contact`, `/article`.

---

## 11. Autentikasi

Fitur standar Breeze Blade:

| Fitur | Keterangan |
|-------|------------|
| Register | Akun baru di `/register` |
| Login | Masuk di `/login` |
| Logout | Via menu dropdown user |
| Verifikasi email | Link verifikasi dikirim setelah register (middleware `verified`) |
| Lupa password | Link reset di `/forgot-password` |
| Konfirmasi password | Permintaan ulang password untuk aksi sensitif |
| Kelola profil | Ubah nama/email, ganti password, hapus akun di `/profile` |

Setelah login, user diarahkan ke `/dashboard`.

---

## 12. Panel Admin & CRUD

### Akses
Route admin dibungkus middleware:

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('posts', AdminPostController::class);
});
```

Belum login → redirect ke `/login`. Email belum terverifikasi → halaman verifikasi.

### CRUD Kategori (`/admin/categories`)
- **Index**: tabel Nama, Deskripsi, Jumlah post (`withCount('posts')`), aksi Edit/Delete.
- **Create**: form `name` (wajib) + `description` (opsional); slug dibuat otomatis: `Str::slug($name)`.
- **Edit**: form terisi data lama, dikirim method `PUT`.
- **Delete**: konfirmasi browser; karena `ON DELETE CASCADE`, post terkait ikut terhapus.

### CRUD Post (`/admin/posts`)
- **Index**: tabel Judul, Kategori (eager loading `with('category')`), waktu dibuat (`diffForHumans()`), aksi Edit/Delete.
- **Create/Edit**: `title` (wajib), `category_id` (dropdown wajib, harus ada di tabel categories), `text` (textarea wajib).

---

## 13. Validasi Form

Semua form admin memakai validasi server-side:

```php
$data = $request->validate([
    'title' => ['required', 'string', 'max:255'],
    'text' => ['required', 'string'],
    'category_id' => ['required', 'exists:categories,id'],
]);
```

| Field | Aturan | Gagal → |
|-------|--------|---------|
| `name` (kategori) | `required`, `string`, `max:255` | Pesan error di bawah field |
| `description` | `nullable`, `string` | — |
| `title` (post) | `required`, `string`, `max:255` | Pesan error di bawah field |
| `text` (post) | `required`, `string` | Pesan error di bawah field |
| `category_id` | `required`, `exists:categories,id` | Pesan error di bawah field |

Error ditampilkan dengan `@error` dan nilai lama dipertahankan via `old()`:

```blade
<input id="name" name="name" value="{{ old('name') }}" required>
@error('name')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
```

Validasi gagal → redirect balik dengan error di session.

---

## 14. Keamanan

| Aspek | Detail |
|-------|--------|
| CSRF | Semua form memakai `@csrf`; token divalidasi `VerifyCsrfToken` |
| Mass assignment | Hanya kolom di `$fillable` yang bisa diisi via `create()`/`update()` |
| SQL injection | Eloquent/Query Builder memakai parameter binding |
| XSS | Output Blade otomatis di-escape (`{{ }}`) |
| Auth protection | Middleware `auth` + `verified` di semua route admin & profil |
| Konfirmasi delete | `confirm()` di sisi browser sebelum submit delete |

---

## 15. Testing

```bash
php artisan test                              # semua test
php artisan test --filter=AdminCrudTest       # satu file
php artisan test --filter=test_category_crud  # satu method
```

### Kenapa test tidak menghapus data development?
Trait `RefreshDatabase` mengosongkan & memigrasi ulang database tiap test. Karena itu `phpunit.xml` mengarahkan test ke **SQLite terpisah**:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value="database/testing.sqlite"/>
```

DB development (MySQL `laravel`) tidak pernah tersentuh. Verifikasi:

```bash
php artisan tinker --execute="echo App\Models\User::count().' users / '.App\Models\Category::count().' categories / '.App\Models\Post::count().' posts';"
```

### Test yang tersedia
- `AdminCrudTest`: CRUD kategori, CRUD post, validasi error, proteksi auth.
- `Auth/*`: seluruh alur autentikasi Breeze (login, register, reset password, dll).

> **Tips:** Di `test_post_crud`, database test bersih sehingga kategori harus dibuat sendiri di dalam test (`Category::create(...)`) sebelum membuat post.

---

## 16. Troubleshooting

### Halaman auth/admin tidak punya CSS (404 aset Vite)
`npm run build` belum dijalankan, atau ada file `public/hot` sisa:

```bash
npm run build
rm -f public/hot   # hapus manual di explorer jika tidak ada rm
```

### Error `Base table or view already exists: Table 'categories' already exists`
Tabel sudah dibuat manual, tapi record di tabel `migrations` tidak ada. Tandai migrasi sudah dijalankan (sesuaikan nama file):

```php
php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '2026_08_12_004535_create_categories_table', 'batch' => 1]);"
```

### Error `Undefined variable $posts`
Route `/` harus mengirim variabel `posts` ke view `home`. Hati-hati dengan **route duplikat**: di Laravel, route dengan URI+method sama — yang didaftarkan belakangan menang dan menimpa yang sebelumnya.

### Error `Add [name] to fillable property`
Kolom yang diisi via `create()`/`update()` belum ada di properti `$fillable` model.

### Error `php artisan test` menghapus data development
Pastikan `phpunit.xml` memakai `DB_CONNECTION=sqlite` + `DB_DATABASE=database/testing.sqlite` (sudah default di project ini).

### Error `The "intl" PHP extension is required`
Aktifkan ekstensi `intl` di `php.ini`, restart server.

### Port 8000 sudah terpakai
```bash
php artisan serve --port=8080
```

---

## 17. Akun Admin Default

Setelah `php artisan db:seed`:

| Field | Nilai |
|-------|-------|
| Email | `admin@example.com` |
| Password | `password` |

Login di `/login`, lalu akses panel admin di `/admin/categories` dan `/admin/posts`.

---

## Lisensi

Project pembelajaran mengikuti kursus Laravel Daily — [Laravel from Scratch](https://laraveldaily.com/course/laravel-from-scratch). Tidak untuk tujuan komersial.
