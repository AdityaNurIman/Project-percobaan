# Panduan Testing

## Menjalankan Test

Dari root project:

```bash
php artisan test
```

Contoh output:

```
  PASS  Tests\Feature\AdminCrudTest
  ✓ category crud
  ✓ post crud
  ✓ validation errors
  ✓ admin requires auth

  Tests:    29 passed (89 assertions)
```

## Menjalankan Satu File Test

```bash
php artisan test --filter=AdminCrudTest
```

## Menjalankan Satu Method Test

```bash
php artisan test --filter=test_category_crud
```

---

## Kenapa Test Tidak Menghapus Data Development?

Test memakai trait `RefreshDatabase` yang **mengosongkan dan memigrasi ulang** database setiap kali dijalankan. Jika test memakai database yang sama dengan development, semua data akan hilang!

Solusinya: **test memakai database terpisah**. Konfigurasi di `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value="database/testing.sqlite"/>
```

Artinya:

- Saat `php artisan test` berjalan, Laravel otomatis memakai **SQLite** (file `database/testing.sqlite`).
- Database development (`mysql` / database `laravel`) **tidak tersentuh**.
- File `database/testing.sqlite` otomatis diabaikan git? **Tidak** — tambahkan ke `.gitignore` jika ingin.

### Verifikasi data development aman

```bash
# di terminal terpisah, setelah menjalankan test
php artisan tinker --execute="echo App\Models\User::count().' users / '.App\Models\Category::count().' categories / '.App\Models\Post::count().' posts';"
```

Angka harus tetap sesuai sebelum test dijalankan.

---

## Struktur Test

### `tests/Feature/AdminCrudTest.php`

Menggunakan `RefreshDatabase` — tiap test dimulai dari database bersih, perubahan dibungkus transaksi dan di-rollback setelah test.

| Test | Yang Diuji |
|------|-----------|
| `test_category_crud` | Create kategori → tersimpan di DB; update → berubah; delete → hilang |
| `test_post_crud` | Create post (buat kategori dulu) → update → delete |
| `test_validation_errors` | Submit form kosong / category_id tidak valid → error session `name`, `title`, `text`, `category_id` |
| `test_admin_requires_auth` | Akses `/admin/categories` & `/admin/posts` tanpa login → redirect ke `/login` |

Poin penting pada `test_post_crud`: karena database test bersih, kategori tidak otomatis ada. Test harus **membuat kategori sendiri** sebelum membuat post:

```php
$category = Category::create([
    'name' => 'Test Dev Category',
    'slug' => 'test-dev-category',
]);
```

### Test Breeze (bawaan)
`tests/Feature/Auth/*` menguji seluruh alur autentikasi Breeze: login, register, logout, verifikasi email, reset password, konfirmasi password, update password, dan profil.

---

## Tips

- Jangan menjalankan `php artisan test` saat `.env` masih memakai DB development dan `phpunit.xml` belum diubah ke sqlite — data development bisa terhapus oleh `RefreshDatabase`.
- Jika test error `no such table: ...`, pastikan `DB_DATABASE` di `phpunit.xml` menunjuk ke file sqlite yang benar.
- Jika test error `Attempt to read property 'id' on null`, biasanya test mengasumsikan data yang tidak ada di database test bersih — buat data dulu di dalam test.
