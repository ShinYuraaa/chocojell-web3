# Dokumentasi Panel Admin Choco Jell

## Overview

Panel Admin Choco Jell adalah sistem manajemen backend untuk e-commerce minuman/coklat yang memungkinkan admin untuk:

- Mengelola produk (CRUD)
- Update stok minuman
- Menambahkan display minuman baru
- Menentukan harga produk
- Memberikan informasi status pesanan kepada konsumen

## Fitur Utama

### 1. Dashboard Admin
- Statistik overview (Total Produk, Total Pesanan, Pesanan Pending)
- Daftar pesanan terbaru
- Akses cepat ke fitur-fitur utama

**URL:** `/admin/dashboard`

### 2. Manajemen Produk
Fitur lengkap untuk mengelola produk:

- **Lihat Semua Produk** - Grid view dengan informasi stok
- **Tambah Produk Baru** - Form lengkap dengan upload gambar
- **Edit Produk** - Update nama, harga, stok, deskripsi, kategori, dan gambar
- **Hapus Produk** - Menghapus produk dari sistem
- **Update Stok** - Kelola stok real-time

**URLs:**
- `/admin/products` - Daftar produk
- `/admin/products/create` - Tambah produk
- `/admin/products/{id}/edit` - Edit produk

### 3. Manajemen Pesanan
Kelola status pesanan dengan 5 status berbeda:

1. Pending - Pesanan baru masuk
2. Sedang Dibuat - Pesanan sedang diproses
3. Dalam Perjalanan - Pesanan dalam pengiriman
4. Selesai - Pesanan telah diterima
5. Dibatalkan - Pesanan dibatalkan

**URLs:**
- `/admin/orders` - Daftar semua pesanan
- `/admin/orders/{id}` - Detail pesanan

## Struktur File

```
app/
├── Http/
│   └── Controllers/
│       └── AdminController.php          # Controller admin

resources/
├── views/
│   └── admin/
│       ├── dashboard.blade.php          # Dashboard
│       ├── products.blade.php           # List produk
│       ├── product-create.blade.php     # Form tambah produk
│       ├── product-edit.blade.php       # Form edit produk
│       ├── orders.blade.php             # List pesanan
│       └── order-detail.blade.php       # Detail pesanan

public/
├── css/
│   └── admin.css                        # Styling admin panel
└── img/
    └── products/                        # Folder upload gambar produk

routes/
└── web.php                              # Admin routes
```

## Design

Panel admin menggunakan:
- **Layout:** Sidebar + Main Content
- **Color Scheme:** Purple gradient (#667eea to #764ba2)
- **Responsive:** Mobile-friendly
- **Modern UI:** Card-based design dengan smooth animations

## Instalasi & Setup

### 1. Pastikan database sudah ter-setup dengan migration yang ada

### 2. Akses Panel Admin

```
http://localhost/ChocoJell/admin/dashboard
```

### 3. Upload Gambar Produk
Gambar akan disimpan di: `public/img/products/`

Format yang didukung:
- JPG, JPEG, PNG, GIF
- Maksimal ukuran: 2MB

## Database Schema

Panel admin bekerja dengan tabel berikut:

### Products
```sql
- product_id (PK)
- product_name
- description
- price
- category
- image_url
- created_at
- updated_at
```

### Inventory
```sql
- inventory_id (PK)
- product_id (FK)
- stock
- last_updated
```

### Orders
```sql
- order_id (PK)
- customer_id (FK)
- order_date
- total_price
- status (pending, sedang dibuat, dalam perjalanan, selesai, dibatalkan)
- created_at
- updated_at
```

### OrdersDetail
```sql
- orderdetail_id (PK)
- order_id (FK)
- product_id (FK)
- quantity
- price
```

## Security Notes

Penting untuk Production:

1. **Tambahkan Authentication Middleware**
   ```php
   Route::middleware(['auth', 'admin'])->group(function () {
       // admin routes
   });
   ```

2. **Validasi Input**
   - Sudah ada validasi dasar
   - Tambahkan CSRF protection (sudah ada @csrf)

3. **Authorization**
   - Buat middleware untuk membedakan admin dan customer
   - Implementasikan role-based access control

4. **File Upload Security**
   - Validasi tipe file (sudah ada)
   - Sanitasi nama file (sudah ada)
   - Set permission yang tepat pada folder uploads

## Status Pesanan Flow

```
Pending → Sedang Dibuat → Dalam Perjalanan → Selesai
                ↓
           Dibatalkan (bisa dari status apapun)
```

## Responsive Design

- **Desktop:** Full sidebar + content
- **Tablet:** Sidebar 200px
- **Mobile:** Sidebar menjadi full width, stacked layout

## Next Steps (Improvement Ideas)

1. **Authentication System**
   - Login khusus untuk admin
   - Session management
   - Role-based access

2. **Dashboard Analytics**
   - Grafik penjualan
   - Produk terlaris
   - Revenue tracking

3. **Notification System**
   - Alert untuk pesanan baru
   - Stock warning (stok menipis)
   - Email notification ke customer

4. **Export/Import**
   - Export data produk (Excel/CSV)
   - Import bulk products

5. **Image Gallery**
   - Multiple images per product
   - Image cropping tool

6. **Customer Management**
   - View customer list
   - Customer order history

7. **Reports**
   - Sales report
   - Inventory report
   - Order statistics

## Tips Penggunaan

1. **Menambah Produk Baru:**
   - Pergi ke Products → Tambah Produk
   - Isi semua field yang wajib (*)
   - Upload gambar yang menarik
   - Set stok awal

2. **Update Status Pesanan:**
   - Bisa langsung dari halaman Orders (dropdown)
   - Atau masuk ke Detail pesanan untuk info lengkap

3. **Monitoring Stok:**
   - Badge hijau = ada stok
   - Badge merah = stok habis
   - Edit produk untuk update stok

## Troubleshooting

**Gambar tidak muncul?**
- Pastikan folder `public/img/products/` ada
- Cek permission folder (readable)
- Cek path di database sesuai dengan file

**Error saat upload?**
- Cek ukuran file < 2MB
- Format file harus JPG/PNG/GIF
- Cek permission write pada folder

**Status tidak terupdate?**
- Pastikan form method POST dengan @method('PATCH')
- Cek CSRF token

## Support

Dibuat oleh Kelompok Sage:
- Rafif Febrian Putra
- Muhammad Syaiful Fajri
- Louis Ponglabba
- Muhammad Zidan Fikri
- Muhammad Faqih Fadlurohman
- Zahra Nadhifah Nasution
