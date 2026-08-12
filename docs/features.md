# Fitur-Fitur Aplikasi

Penjelasan detail setiap fitur yang tersedia di aplikasi blog ini.

---

## 1. Halaman Publik

### 1.1 Beranda (`/`)
Menampilkan judul **"Latest Posts"** dan daftar post terbaru (diurutkan `latest()`). Setiap item menampilkan:

- Gambar placeholder (150x150)
- Judul post (link ke halaman detail)
- Cuplikan teks (`substr($post->text, 0, 50)` + `...`)

Data diambil oleh `HomeController@index`:

```php
$posts = Post::latest()->get();
```

### 1.2 Sidebar Kategori
Sidebar kanan menampilkan semua kategori. Klik kategori akan memfilter post melalui parameter GET:

```
/?category_id=3
```

Kategori yang aktif ditandai dengan latar gelap:

```blade
class="... {{ request('category_id') == $category->id ? 'bg-gray-800 text-white' : 'text-gray-600' }}"
```

Logika filter ada di controller memakai `when()`:

```php
$posts = Post::when($request->has('category_id'), function ($query) use ($request) {
    $query->where('category_id', $request->category_id);
})->latest()->get();
```

### 1.3 Halaman Detail Post (`/posts/{id}`)
Menampilkan judul, nama kategori (link kembali ke filter), gambar, dan isi penuh post.

Menggunakan **Route Model Binding** — Laravel otomatis mencari `Post` berdasarkan `id` di URL. Post tidak ditemukan → halaman 404.

```php
public function show(Post $post) { ... }
```

Tombol **"← Back to posts"** kembali ke beranda.

### 1.4 Halaman Statis
- `/about` — halaman profil/perkenalan.
- `/contact` — halaman kontak.
- `/article` — halaman artikel (placeholder).

---

## 2. Autentikasi (Laravel Breeze)

Fitur standar Breeze Blade:

| Fitur | Keterangan |
|-------|------------|
| **Register** | Membuat akun baru di `/register` |
| **Login** | Masuk dengan email & password di `/login` |
| **Logout** | Keluar lewat menu dropdown user |
| **Verifikasi email** | Link verifikasi dikirim setelah register (fitur diaktifkan via middleware `verified`) |
| **Lupa password** | Kirim link reset di `/forgot-password` |
| **Konfirmasi password** | Permintaan ulang password untuk aksi sensitif |
| **Kelola profil** | Ubah nama/email, ganti password, hapus akun di `/profile` |

Setelah login, user diarahkan ke `/dashboard`.

---

## 3. Panel Admin

### 3.1 Akses
Hanya user yang sudah **login** dan **email terverifikasi** yang bisa mengakses. Route dibungkus middleware:

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('posts', AdminPostController::class);
});
```

User yang belum login akan diarahkan ke `/login`. User yang belum verifikasi email akan diarahkan ke halaman verifikasi.

Navigasi admin (Dashboard, Categories, Posts) tersedia di `layouts/navigation.blade.php`.

### 3.2 CRUD Kategori (`/admin/categories`)

**Index** — tabel berisi kolom:
- Nama
- Deskripsi
- Jumlah post terkait (`withCount('posts')`)
- Aksi Edit & Delete

**Create** — form isi `name` (wajib) dan `description` (opsional). Slug dibuat otomatis dari nama:

```php
'slug' => Str::slug($data['name']),
```

**Edit** — form yang sudah terisi data lama, dikirim dengan method `PUT`.

**Delete** — tombol dengan konfirmasi browser (`onsubmit="return confirm(...)"`). Karena relasi `ON DELETE CASCADE`, post terkait ikut terhapus.

### 3.3 CRUD Post (`/admin/posts`)

**Index** — tabel berisi:
- Judul
- Nama kategori (dari relasi, memakai eager loading `with('category')`)
- Waktu dibuat (`diffForHumans()`)
- Aksi Edit & Delete

**Create/Edit** — form dengan:
- `title` (wajib, max 255)
- `category_id` (dropdown, wajib, harus id yang ada di tabel categories)
- `text` (textarea, wajib)

Eager loading mencegah masalah **N+1 query**: semua relasi kategori diambil dalam satu query tambahan, bukan satu query per post.

---

## 4. Validasi Form

Semua form admin memakai validasi server-side di controller:

```php
$data = $request->validate([
    'title' => ['required', 'string', 'max:255'],
    'text' => ['required', 'string'],
    'category_id' => ['required', 'exists:categories,id'],
]);
```

### Aturan per field

| Field | Aturan | Gagal → |
|-------|--------|---------|
| `name` (kategori) | `required`, `string`, `max:255` | Pesan error di bawah field |
| `description` | `nullable`, `string` | — |
| `title` (post) | `required`, `string`, `max:255` | Pesan error di bawah field |
| `text` (post) | `required`, `string` | Pesan error di bawah field |
| `category_id` | `required`, `exists:categories,id` | Pesan error di bawah field |

Error ditampilkan di view dengan `@error` dan nilai input lama dipertahankan memakai `old()`:

```blade
<input id="name" name="name" value="{{ old('name') }}" required>
@error('name')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
```

Jika validasi gagal, Laravel otomatis redirect kembali dengan error di session (`assertSessionHasErrors` di test).

---

## 5. Keamanan

| Aspek | Detail |
|-------|--------|
| **CSRF** | Semua form memakai `@csrf`; token divalidasi middleware `VerifyCsrfToken` |
| **Mass assignment** | Hanya kolom di `$fillable` yang bisa diisi lewat `create()`/`update()` |
| **SQL injection** | Eloquent/Query Builder memakai parameter binding |
| **XSS** | Output Blade otomatis di-escape (`{{ }}`) |
| **Auth protection** | Middleware `auth` + `verified` di semua route admin & profil |
| **Konfirmasi delete** | `confirm()` di sisi browser sebelum submit delete |

---

## 6. Testing

Project memiliki `tests/Feature/AdminCrudTest.php` yang menguji:

- CRUD kategori lengkap (create → update → delete)
- CRUD post lengkap
- Validasi error (field kosong / foreign key salah)
- Proteksi auth (route admin mengarahkan ke login)

Dijalankan dengan:

```bash
php artisan test
```

Test memakai database SQLite terpisah (`database/testing.sqlite`) sehingga data MySQL development tidak pernah terganggu.
