# Arsitektur Project

Dokumen ini menjelaskan struktur internal aplikasi: alur request, skema database, rute, controller, model, dan views.

## Alur Request (MVC)

```
Browser ──GET /──▶ routes/web.php ──▶ HomeController@index ──▶ Model (Eloquent) ──▶ DB
   ▲                                                                                │
   └───────────── HTML (Blade) ◀─────── resources/views/home.blade.php ◀────────────┘
```

1. Request masuk ke `public/index.php` → framework bootstrap.
2. `routes/web.php` mencocokkan URL dan method dengan rute yang terdaftar.
3. Controller dipanggil; controller mengambil data lewat Eloquent Model.
4. Data dikirim ke Blade view untuk dirender menjadi HTML.

## Skema Database

### Tabel `categories`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint (PK) | Auto increment |
| `name` | varchar(255) | Nama kategori |
| `slug` | varchar(255) | Slug unik (digunakan untuk URL) |
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

## Rute (`routes/web.php`)

### Publik (tanpa login)

| Method | URI | Controller / Aksi | Nama |
|--------|-----|-------------------|------|
| GET | `/` | `HomeController@index` | `home` |
| GET | `/posts/{post}` | `HomeController@show` | `posts.show` |
| GET | `/contact` | `Route::view` | `contact` |
| GET | `/about` | `Route::view` | `about` |
| GET | `/dashboard` | Closure | `dashboard` |

### Autentikasi (dari Breeze, `routes/auth.php`)

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

### Admin (`middleware auth + verified`, prefix `/admin`, nama `admin.*`)

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

## Controller

### `App\Http\Controllers\HomeController`
Halaman publik blog.

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

- **Route Model Binding**: parameter `{post}` otomatis di-resolve menjadi instance `Post`. Jika tidak ditemukan → HTTP 404.
- **Filter kategori**: memakai `when()` dari Query Builder — query `where('category_id', ...)` hanya ditambahkan jika parameter `category_id` ada di request.

### `App\Http\Controllers\Admin\CategoryController`
CRUD kategori standar:

- `index()` → ambil semua kategori + `withCount('posts')`, tampilkan di tabel.
- `store()` → validasi, buat slug dari nama, `Category::create()`.
- `update()` → validasi, perbarui slug, `$category->update()`.
- `destroy()` → `$category->delete()`.

### `App\Http\Controllers\Admin\PostController`
CRUD post:

- `index()` → `Post::with('category')` (eager loading agar tidak N+1 query).
- `store()` / `update()` → validasi `title`, `text`, `category_id` (harus ada di tabel `categories`).

## Model & Relasi

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

- `$fillable` mengizinkan mass-assignment hanya untuk kolom tersebut (proteksi keamanan).
- Relasi memungkinkan akses seperti `$post->category->name` dan `$category->posts`.

## Struktur Views

```
resources/views/
├── layouts/
│   ├── blog.blade.php          # Layout publik (Tailwind CDN): header My Blog + About/Contact
│   ├── app.blade.php           # Layout Breeze (component <x-app-layout>)
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

### Layout publik (`layouts/blog.blade.php`)
Menggunakan `@yield('content')` dan `@section('content')` klasik:

```blade
@extends('layouts.blog')

@section('content')
    <main>...</main>
@endsection
```

> **Catatan penting:** jangan lupa menutup blok `@section` dengan `@endsection`. Jika lupa, output buffer section tetap terbuka dan layout bisa terekam di posisi yang salah.

## Keamanan yang Diterapkan

| Aspek | Implementasi |
|-------|-------------|
| Autentikasi | Middleware `auth` di semua route admin |
| Verifikasi email | Middleware `verified` di route admin & dashboard |
| CSRF | Token otomatis di semua form Blade (`@csrf`) |
| Mass assignment | Properti `$fillable` di model |
| Validasi server-side | `$request->validate()` di controller |
| SQL Injection | Query Builder / Eloquent menggunakan prepared statements |
