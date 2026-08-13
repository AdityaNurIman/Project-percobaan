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
18. [Tugas Praktik: Aplikasi Data Barang](#18-tugas-praktik-aplikasi-data-barang-eloquent--blade-crud)

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

## 18. Tugas Praktik: Aplikasi Data Barang (Eloquent + Blade CRUD)

Dokumentasi berikut menjelaskan **cara membangun fitur CRUD "Data Barang"** dari nol di Laravel 12: dari pembuatan model, migrasi, controller, route, hingga views Blade. Materi ini mencakup 10 langkah/tugas yang saling berkesinambungan.

---

### 18.1 Soal 1 — Membuat Model & Migration Tabel `barangs`

Perintah untuk membuat **model sekaligus migration**:

```bash
php artisan make:model Barang -m
```

| Bagian | Fungsi |
|--------|--------|
| `php artisan make:model` | Perintah artisan untuk membuat class model Eloquent |
| `Barang` | Nama model yang dibuat → `app/Models/Barang.php` |
| `-m` (atau `--migration`) | Flag opsional yang **sekaligus membuat file migration** untuk tabel `barangs` |

Tanpa `-m`, migration harus dibuat terpisah dengan `php artisan make:migration create_barangs_table`.

Isi migration `database/migrations/..._create_barangs_table.php`:

```php
Schema::create('barangs', function (Blueprint $table) {
    $table->id();
    $table->string('nama_barang');
    $table->string('kode_barang');
    $table->integer('stok');
    $table->integer('harga');
    $table->timestamps();
});
```

Isi model `app/Models/Barang.php` (daftar kolom yang boleh diisi mass-assignment):

```php
class Barang extends Model
{
    protected $fillable = ['nama_barang', 'kode_barang', 'stok', 'harga'];
}
```

Jalankan migrasi untuk membuat tabel di database:

```bash
php artisan migrate
```

Cek status migrasi: `php artisan migrate:status`.

---

### 18.2 Soal 2 — Menambah Data dengan Eloquent (Tambah Satu Barang)

Buat controller:

```bash
php artisan make:controller BarangController
```

Method `tambahBarang` menambahkan satu data menggunakan model `Barang`:

```php
use App\Models\Barang;

public function tambahBarang()
{
    Barang::create([
        'nama_barang' => 'Laptop',
        'kode_barang' => 'BRG-001',
        'stok'        => 10,
        'harga'       => 7500000,
    ]);

    return 'Data barang berhasil ditambahkan';
}
```

Route di `routes/web.php`:

```php
use App\Http\Controllers\BarangController;

Route::get('/barang/tambah', [BarangController::class, 'tambahBarang']);
```

**Alur kerja Route → Controller → Model:**

```
URL /barang/tambah ──▶ Route ──▶ BarangController@tambahBarang
                                          │
                                          ▼
                                   Model Barang (Eloquent)
                                          │
                                          ▼
                                    Database barangs
```

1. Browser meminta `/barang/tambah` (method GET).
2. Laravel mencocokkan URL dengan rute yang terdaftar di `routes/web.php`.
3. Rute memanggil method `tambahBarang` pada `BarangController`.
4. Controller memakai model `Barang` (Eloquent) untuk menulis data ke tabel `barangs` (SQL `INSERT` dihasilkan otomatis).
5. Data tersimpan; controller mengembalikan respons ke browser.

---

### 18.3 Soal 3 — Menampilkan Semua Data Barang (Blade + `@foreach`)

Method `index` mengambil semua data lalu mengirimnya ke view:

```php
public function index()
{
    $barang = Barang::all();

    return view('barang.index', compact('barang'));
}
```

View `resources/views/barang/index.blade.php` menampilkan tabel:

```blade
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Kode Barang</th>
            <th>Stok</th>
            <th>Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($barang as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->stok }}</td>
                <td>{{ $item->harga }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
```

**Penjelasan `@foreach` di Blade:**

```blade
@foreach ($barang as $item)
    {{-- blok HTML yang diulang untuk setiap elemen --}}
@endforeach
```

- `@foreach (...)` membuka perulangan; `@endforeach` menutupnya (sintaks Blade, dikompilasi menjadi `foreach` PHP biasa).
- Variabel `$item` mewakili **satu baris data** pada setiap iterasi, jadi `{{ $item->nama_barang }}` mengakses kolomnya.
- `$loop->iteration` adalah variabel bawaan Blade yang berisi nomor urut iterasi (1, 2, 3, …) — dipakai untuk kolom "No".

---

### 18.4 Soal 4 — Form Input Data Barang

View `resources/views/barang/create.blade.php`:

```blade
<form method="POST" action="{{ route('barang.store') }}">
    @csrf

    <label>Nama Barang</label>
    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}">

    <label>Kode Barang</label>
    <input type="text" name="kode_barang" value="{{ old('kode_barang') }}">

    <label>Stok</label>
    <input type="number" name="stok" value="{{ old('stok') }}">

    <label>Harga</label>
    <input type="number" name="harga" value="{{ old('harga') }}">

    <button type="submit">Simpan</button>
</form>
```

Route untuk menampilkan form dan menyimpan data:

```php
Route::get('/barang/create', [BarangController::class, 'create'])->name('barang.create');
Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
```

**Mengapa `@csrf` wajib pada form?**

- Laravel melindungi semua rute POST/PUT/PATCH/DELETE dengan middleware `VerifyCsrfToken`.
- Tanpa `@csrf`, Laravel mengembalikan error `419 Page Expired` (token tidak cocok).
- `@csrf` menyisipkan input tersembunyi berisi token unik per session. Token ini membuktikan request benar-benar berasal dari form aplikasi kita, bukan dari situs lain — mencegah serangan **CSRF (Cross-Site Request Forgery)**.

---

### 18.5 Soal 5 — Menyimpan Data dari Form (Validasi)

Method `store` dengan validasi sederhana:

```php
public function store(Request $request)
{
    $data = $request->validate([
        'nama_barang' => 'required',
        'kode_barang' => 'required',
        'stok'        => 'required|numeric',
        'harga'       => 'required|numeric',
    ]);

    Barang::create($data);

    return redirect()->route('barang.index');
}
```

**Penjelasan `$request->validate()`:**

- Aturan ditulis sebagai array `'field' => 'aturan'`; aturan dipisah tanda `|` (atau boleh array, mis. `['required', 'numeric']`).
- `required` → kolom wajib diisi.
- `numeric` → isi harus berupa angka (integer/float), bukan teks bebas.
- Jika validasi **gagal**, Laravel otomatis redirect kembali ke form dengan error di session (`$errors`) dan nilai lama (`old()`) — controller tidak dieksekusi lebih lanjut.

---

### 18.6 Soal 6 — Detail Data Barang

Method `show` dengan parameter `id`:

```php
public function show($id)
{
    $barang = Barang::findOrFail($id);

    return view('barang.show', compact('barang'));
}
```

Route:

```php
Route::get('/barang/{id}', [BarangController::class, 'show'])->name('barang.show');
```

View `barang/show.blade.php` menampilkan detail, contoh:

```blade
<h1>{{ $barang->nama_barang }}</h1>
<p>Kode : {{ $barang->kode_barang }}</p>
<p>Stok : {{ $barang->stok }}</p>
<p>Harga: {{ $barang->harga }}</p>
```

**Perbedaan `find()` vs `findOrFail()`:**

| Method | Perilaku |
|--------|----------|
| `Barang::find($id)` | Mengembalikan objek model jika ditemukan, atau **`null`** jika tidak ada. Program berlanjut (harus dicek manual, mis. `if ($barang) { ... }`) |
| `Barang::findOrFail($id)` | Mengembalikan objek model jika ditemukan, atau **melempar exception** `ModelNotFoundException` → Laravel otomatis menampilkan **404** jika tidak ada |

`findOrFail()` lebih praktis untuk halaman publik/detail karena tidak perlu menulis pengecekan `null` manual.

---

### 18.7 Soal 7 — Form Edit Data Barang

Method `edit` menampilkan form berisi data lama:

```php
public function edit($id)
{
    $barang = Barang::findOrFail($id);

    return view('barang.edit', compact('barang'));
}
```

Route:

```php
Route::get('/barang/{id}/edit', [BarangController::class, 'edit'])->name('barang.edit');
Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
```

View `barang/edit.blade.php` (method PUT):

```blade
<form method="POST" action="{{ route('barang.update', $barang->id) }}">
    @csrf
    @method('PUT')

    <label>Nama Barang</label>
    <input type="text" name="nama_barang" value="{{ old('nama_barang', $barang->nama_barang) }}">
    ...
</form>
```

**Cara menampilkan nilai lama pada input:**

```blade
value="{{ old('nama_barang', $barang->nama_barang) }}"
```

- `old('nama_barang', ...)` → mengembalikan nilai yang dikirim sebelumnya (ketika validasi gagal), atau nilai **default** (argumen kedua) jika tidak ada.
- Jadi urutannya: **jika ada error validasi → tampilkan input lama user**; **jika tidak → tampilkan data dari database** (`$barang->nama_barang`).
- Tanpa `$barang->nama_barang` sebagai fallback, field edit akan kosong setiap halaman dimuat ulang.

---

### 18.8 Soal 8 — Update Data Barang (dengan Flash Message)

Method `update`:

```php
public function update(Request $request, $id)
{
    $barang = Barang::findOrFail($id);

    $data = $request->validate([
        'nama_barang' => 'required',
        'kode_barang' => 'required',
        'stok'        => 'required|numeric',
        'harga'       => 'required|numeric',
    ]);

    $barang->update($data);

    return redirect()->route('barang.index')->with('success', 'Data barang berhasil diupdate.');
}
```

**Contoh penggunaan session flash:**

```php
return redirect()->route('barang.index')->with('success', 'Data barang berhasil diupdate.');
```

- `->with('success', '...')` menyimpan pesan ke **session (flash data)** — hanya tersedia untuk satu request berikutnya, lalu otomatis terhapus.
- Di view `index`, pesan ditampilkan dengan `session()`:

```blade
@if (session('success'))
    <div style="color: green;">{{ session('success') }}</div>
@endif
```

---

### 18.9 Soal 9 — Hapus Data Barang

Method `destroy`:

```php
public function destroy($id)
{
    $barang = Barang::findOrFail($id);
    $barang->delete();

    return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus.');
}
```

Route dengan method DELETE:

```php
Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');
```

Tombol hapus di halaman daftar (harus berupa form, bukan link GET biasa):

```blade
<form method="POST" action="{{ route('barang.destroy', $item->id) }}"
      onsubmit="return confirm('Yakin ingin menghapus?')">
    @csrf
    @method('DELETE')
    <button type="submit">Hapus</button>
</form>
```

**Mengapa hapus memakai method DELETE, bukan link GET biasa?**

1. **Semantik HTTP**: DELETE menandakan aksi yang mengubah/menghapus resource (bukan tindakan aman/seperti membaca). GET seharusnya tidak mengubah data.
2. **Keamanan / tidak bisa di-prekondisi**: Link GET bisa ter-klik otomatis oleh crawler, pre-fetch browser, atau tautan yang di-share — data bisa terhapus tanpa disengaja.
3. **CSRF protection**: Form POST/DELETE wajib menyertakan `@csrf`; Laravel memvalidasi token sehingga hapus tidak bisa dipicu dari situs luar. Link GET tidak punya perlindungan ini.
4. **Konsisten dengan `Route::resource`**: Laravel secara default memetakan hapus ke method DELETE (`destroy`), jadi pola ini juga cocok untuk resource controller.

---

### 18.10 Soal 10 — Resource Controller & Route Resource

Buat controller resource, model, dan migration:

```bash
php artisan make:controller ProdukController --resource
php artisan make:model Produk -m
```

Migration tabel `produks`:

```php
Schema::create('produks', function (Blueprint $table) {
    $table->id();
    $table->string('nama_produk');
    $table->string('kategori');
    $table->integer('harga');
    $table->integer('stok');
    $table->timestamps();
});
```

Daftarkan route resource:

```php
Route::resource('produk', ProdukController::class);
```

**Method-method resource controller dan fungsinya:**

| Method | HTTP | URI | Fungsi |
|--------|------|-----|--------|
| `index` | GET | `/produk` | Menampilkan daftar semua produk |
| `create` | GET | `/produk/create` | Menampilkan form tambah produk |
| `store` | POST | `/produk` | Menyimpan produk baru dari form |
| `show` | GET | `/produk/{produk}` | Menampilkan detail satu produk |
| `edit` | GET | `/produk/{produk}/edit` | Menampilkan form edit produk |
| `update` | PUT/PATCH | `/produk/{produk}` | Menyimpan perubahan produk |
| `destroy` | DELETE | `/produk/{produk}` | Menghapus produk |

**Keuntungan `Route::resource()` dibanding route satu per satu:**

| Aspek | `Route::resource()` | Route manual |
|-------|--------------------:|-------------|
| Jumlah baris | 1 baris untuk 7 route | ±7 baris terpisah |
| Nama route | Otomatis `produk.index`, `produk.store`, dst. | Harus didefinisikan manual |
| Konsistensi | HTTP verb & URI sudah baku mengikuti konvensi RESTful | Mudah salah/inconsistent |
| Perawatan | Tambah/ubah mudah, kode ringkas | Semakin banyak resource makin panjang |
| Extra | Bisa dibatasi: `->only([...])` / `->except([...])`, atau `->apiResource()` | — |

Dengan resource controller, seluruh CRUD cukup didefinisikan sekali dan langsung tersedia lengkap (index, create, store, show, edit, update, destroy).

---

## Lisensi

Project pembelajaran mengikuti kursus Laravel Daily — [Laravel from Scratch](https://laraveldaily.com/course/laravel-from-scratch). Tidak untuk tujuan komersial.
