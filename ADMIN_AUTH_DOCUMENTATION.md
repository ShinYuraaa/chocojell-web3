# Dokumentasi Admin Authentication System

## Fitur yang Sudah Diimplementasikan

### 1. **Model Admin**
- Lokasi: `app/Models/Admin.php`
- Extends `Authenticatable` untuk Laravel Auth
- Primary key: `admin_id`
- Fields: `nama`, `email`, `password`

### 2. **Middleware IsAdmin**
- Lokasi: `app/Http/Middleware/IsAdmin.php`
- Fungsi: Proteksi semua route admin
- Redirect ke login jika belum login
- Menggunakan Session untuk cek authentication

### 3. **Admin Login System**
**Login Controller Methods:**
- `showLoginForm()` - Tampilkan halaman login
- `login()` - Process login dengan validasi
- `logout()` - Hapus session dan logout

Login Features:
- Validasi email & password
- Hash password check dengan Hash::check()
- Session storage (admin_id, admin_name, admin_email)
- Redirect ke dashboard setelah login
- Error message untuk login gagal

### 4. **Protected Admin Routes**
Semua route admin sekarang dilindungi dengan middleware `'admin'`:

**Public Routes (No Auth Required):**
```
GET  /admin/login        - Halaman login
POST /admin/login        - Process login
```

**Protected Routes (Auth Required):**
```
POST   /admin/logout                      - Logout
GET    /admin/dashboard                   - Dashboard
GET    /admin/products                    - List produk
GET    /admin/products/create             - Form tambah produk
POST   /admin/products                    - Simpan produk
GET    /admin/products/{id}/edit          - Form edit produk
PUT    /admin/products/{id}               - Update produk
DELETE /admin/products/{id}               - Hapus produk
GET    /admin/orders                      - List pesanan
GET    /admin/orders/{id}                 - Detail pesanan
PATCH  /admin/orders/{id}/status          - Update status
```

### 5. **Admin Login View**
- Lokasi: `resources/views/admin/login.blade.php`
- Design: Modern gradient purple theme
- Features:
  - Email & password input
  - Error/success alerts
  - Auto-hide alerts setelah 5 detik
  - Link kembali ke homepage
  - Security note untuk user

### 6. **Sidebar Component**
- Lokasi: `resources/views/admin/partials/sidebar.blade.php`
- Menampilkan nama & email admin yang login
- Active state untuk navigation
- Logout button with CSRF protection

### 7. **Admin Seeder**
- Lokasi: `database/seeders/AdminSeeder.php`
- Membuat 2 akun admin default

---

## Default Admin Accounts

### Admin 1:
```
Email: admin@chocojell.com
Password: admin123
```

### Admin 2 (Super Admin):
```
Email: superadmin@chocojell.com
Password: super123
```

---

## Cara Menggunakan

### 1. Akses Halaman Login Admin:
```
http://127.0.0.1:8000/admin/login
```

### 2. Login dengan Kredensial:
- Email: `admin@chocojell.com`
- Password: `admin123`

### 3. Setelah Login:
- Otomatis redirect ke `/admin/dashboard`
- Session tersimpan (admin_id, admin_name, admin_email)
- Dapat akses semua fitur admin

### 4. Logout:
- Klik tombol "Logout" di sidebar
- Session dihapus
- Redirect ke halaman login

---

## Security Flow

```
User mencoba akses /admin/dashboard
           ↓
    Middleware 'admin' check
           ↓
Session::has('admin_id') ?
           ↓
    NO → Redirect ke /admin/login
    YES → Allow access
```

---

## Security Features

### Password Hashing
- Menggunakan `Hash::make()` untuk enkripsi
- Tidak ada plain text password di database
- Verifikasi dengan `Hash::check()`

### Session-Based Authentication
- Session storage: `admin_id`, `admin_name`, `admin_email`
- Session cleared on logout
- Session check di middleware

### CSRF Protection
- Semua form menggunakan `@csrf`
- Laravel automatic CSRF validation
- Protect dari Cross-Site Request Forgery

### Route Protection
- Middleware 'admin' pada semua route admin
- Automatic redirect jika belum login
- Public access hanya untuk login page

### Input Validation
- Email validation
- Password minimum 6 characters
- Server-side validation di controller

---

## Cara Menambah Admin Baru

### Opsi 1: Via Database (Recommended for first setup)
```sql
INSERT INTO admin (nama, email, password, created_at, updated_at) 
VALUES (
    'Nama Admin', 
    'email@example.com', 
    '$2y$12$...', -- Generate hash dengan Hash::make('password')
    NOW(), 
    NOW()
);
```

### Opsi 2: Via Tinker
```bash
php artisan tinker
```

```php
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Admin::create([
    'nama' => 'Admin Baru',
    'email' => 'newadmin@chocojell.com',
    'password' => Hash::make('password123')
]);
```

### Opsi 3: Buat Halaman Register Admin (Untuk Future Development)
- Buat route `/admin/register` (protected)
- Form untuk register admin baru
- Hanya super admin yang bisa akses

---

## Important Notes

### 1. Password Policy
- Minimum 6 karakter (bisa diubah di validation)
- Recommended: 8+ karakter dengan kombinasi
- Implementasi password strength di production

### 2. Session Security
- Session timeout: default Laravel (120 menit)
- Bisa diubah di `config/session.php`
- Gunakan HTTPS di production

### 3. Production Recommendations
```php
// config/session.php
'secure' => true,  // HTTPS only
'http_only' => true,  // JavaScript tidak bisa akses
'same_site' => 'strict',  // CSRF protection
```

### 4. Rate Limiting (Recommended untuk Production)
Tambahkan throttle untuk login:
```php
Route::post('/admin/login', [AdminController::class, 'login'])
    ->middleware('throttle:5,1')  // 5 attempts per minute
    ->name('admin.login.submit');
```

---

## Testing Authentication

### Test 1: Login dengan kredensial benar
1. Akses `/admin/login`
2. Input: `admin@chocojell.com` / `admin123`
3. Expected: Redirect ke dashboard dengan pesan success

### Test 2: Login dengan kredensial salah
1. Akses `/admin/login`
2. Input email/password salah
3. Expected: Error message "Email atau password salah!"

### Test 3: Akses dashboard tanpa login
1. Logout atau clear session
2. Akses `/admin/dashboard`
3. Expected: Redirect ke `/admin/login`

### Test 4: Logout
1. Login terlebih dahulu
2. Klik tombol Logout
3. Expected: Redirect ke login, session cleared

---

## Session Data Structure

Saat admin login, session menyimpan:
```php
[
    'admin_id' => 1,
    'admin_name' => 'Admin Choco Jell',
    'admin_email' => 'admin@chocojell.com'
]
```

Akses di blade template:
```blade
@if(Session::has('admin_id'))
    Logged in as: {{ Session::get('admin_name') }}
@endif
```

Akses di controller:
```php
$adminId = Session::get('admin_id');
$adminName = Session::get('admin_name');
```

---

## Next Steps untuk Improvement

### 1. Remember Me Feature
- Checkbox "Remember Me" di login form
- Set cookie untuk auto-login

### 2. Password Reset
- Forgot password functionality
- Email reset link

### 3. Admin Management Page
- CRUD untuk admin accounts
- Only accessible by super admin

### 4. Activity Log
- Log semua aktivitas admin
- Who did what and when

### 5. Two-Factor Authentication
- Extra layer security dengan OTP
- Email/SMS verification

### 6. Admin Roles & Permissions
- Different levels: Super Admin, Admin, Editor
- Permission-based access control

---

## Checklist Setup

- [x] Model Admin dibuat
- [x] Middleware IsAdmin dibuat
- [x] Middleware registered di bootstrap/app.php
- [x] Login methods di AdminController
- [x] Login view dibuat
- [x] Routes updated dengan middleware
- [x] Admin seeder dibuat dan dijalankan
- [x] Sidebar component dengan logout
- [x] Session management implemented
- [x] CSRF protection di semua form
- [x] Password hashing dengan bcrypt

---

## Selesai

Sistem authentication admin sudah fully functional dan secure.

Admin sekarang harus login terlebih dahulu untuk mengakses panel admin. Tidak ada lagi akses public ke dashboard atau fitur admin lainnya.

Ready to Use.
