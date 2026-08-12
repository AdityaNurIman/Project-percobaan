# Panduan Instalasi & Konfigurasi

Panduan lengkap untuk menjalankan project ini dari awal di mesin lokal (Windows/Linux/macOS).

## 1. Persyaratan

Sebelum memulai, pastikan terpasang:

| Tool | Versi Minimum | Cek |
|------|--------------|-----|
| PHP | 8.2 | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |
| MySQL | 8.x | `mysql --version` |
| Git | 2.x | `git --version` |

Pastikan ekstensi PHP berikut aktif di `php.ini`: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `fileinfo`, `bcmath`, `sqlite3` (untuk testing).

## 2. Clone Project

```bash
git clone https://github.com/AdityaNurIman/Project-percobaan.git
cd Project-percobaan
```

Jika tidak memakai git, cukup salin folder project dan lewati langkah ini.

## 3. Install Dependency PHP

```bash
composer install
```

Perintah ini membaca `composer.json` dan mengunduh seluruh package Laravel ke folder `vendor/` (otomatis diabaikan git).

## 4. Install & Build Aset Frontend

```bash
npm install      # unduh Vite, Tailwind, dan dependensi JS
npm run build    # kompilasi CSS/JS ke public/build
```

> **Catatan:** Halaman publik memakai Tailwind CDN, sedangkan halaman Breeze (auth/admin) memakai aset Vite. `npm run build` wajib dijalankan agar halaman auth & admin tidak error 404 pada file CSS/JS.

## 5. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan koneksi database:

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

## 6. Migrasi Database

```bash
php artisan migrate
```

Migrasi akan membuat tabel berikut:

- `users` — akun pengguna (dari Laravel default)
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens` — tabel pendukung
- `categories` — kategori post (`id`, `name`, `slug`, `description`)
- `posts` — isi blog (`id`, `title`, `text`, `category_id` → FK ke categories)

Cek status migrasi:

```bash
php artisan migrate:status
```

## 7. Seed Data Awal

```bash
php artisan db:seed
```

Seeder (`database/seeders/DatabaseSeeder.php`) membuat:

- 1 akun admin: `admin@example.com` / `password`
- 8 kategori contoh
- 2 post contoh

## 8. Jalankan Server

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

---

## Troubleshooting

### Halaman auth/admin tidak punya CSS (404 asset Vite)
Kemungkinan besar `npm run build` belum dijalankan, atau ada file `public/hot` sisa dari dev server.

```bash
npm run build
```

Jika ada file `public/hot`, hapus dulu:

```bash
rm -f public/hot   # atau hapus manual di explorer
```

### Error `Base table or view already exists: Table 'categories' already exists`
Tabel sudah pernah dibuat secara manual/dengan migrasi lama, tetapi record di tabel `migrations` tidak ada. Perbaiki dengan menandai migrasi sudah dijalankan:

```bash
php artisan migrate --pretend   # lihat perintah yang akan dijalankan
```

Lalu sisipkan record manual (sesuaikan nama file migrasi):

```php
php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '2026_08_12_004535_create_categories_table', 'batch' => 1]);"
```

### `Undefined variable $posts`
Pastikan route `/` di `routes/web.php` mengirim variabel `posts` ke view `home`, dan tidak ada route `/` duplikat yang menimpa (di Laravel, route dengan URI+method sama — yang didaftarkan belakangan menang).

### `Add [name] to fillable property`
Model memakai mass-assignment. Pastikan kolom yang diisi via `Model::create()`/`update()` tercantum di properti `$fillable` model.

### `php artisan test` menghapus data development
Test memakai `RefreshDatabase` — DB harus terpisah dari DB development. Konfigurasi sudah mengarah ke `database/testing.sqlite` di `phpunit.xml`, sehingga data MySQL development aman. Detail di [docs/testing.md](testing.md).

### Port 8000 sudah terpakai
```bash
php artisan serve --port=8080
```

### Error `The "intl" PHP extension is required`
Aktifkan ekstensi `intl` di `php.ini`, lalu restart web server/`php artisan serve`.
