<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Choco Jell Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="{{ asset('img/logo.png') }}" alt="Logo">
                <h2>Choco Jell Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item">
                    <span><img src="{{ asset('img/dashboard.png') }}" alt="dashboard" style="width: 20px; height: 20px;"></span> Dashboard
                </a>
                <a href="{{ route('admin.products') }}" class="nav-item active">
                    <span><img src="{{ asset('img/produk.png') }}" alt="produk" style="width: 20px; height: 20px;"></span> Produk
                </a>
                <a href="{{ route('admin.orders') }}" class="nav-item">
                    <span><img src="{{ asset('img/pesanan.png') }}" alt="pesanan" style="width: 20px; height: 20px;"></span> Pesanan
                </a>
                <a href="{{ route('index') }}" class="nav-item" target="_blank">
                    <span><img src="{{ asset('img/home.png') }}" alt="home" style="width: 20px; height: 20px;"></span> Ke Halaman Utama
                </a>
                <a href="{{ route('admin.logout.get') }}" class="nav-item">
                    <span><img src="{{ asset('img/logout.png') }}" alt="logout" style="width: 20px; height: 20px;"></span> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Tambah Produk Baru</h1>
                <a href="{{ route('admin.products') }}" class="btn-secondary">← Kembali</a>
            </header>

            @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="form-container">
                <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data" class="product-form">
                    @csrf
                    
                    <div class="form-group">
                        <label for="product_name">Nama Produk *</label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name') }}" required placeholder="Contoh: Strawberry Chocolate">
                    </div>

                    <div class="form-group">
                        <label for="category">Kategori *</label>
                        <select id="category" name="category" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Chocolate" {{ old('category') == 'Chocolate' ? 'selected' : '' }}>Chocolate</option>
                            <option value="Minuman" {{ old('category') == 'Minuman' ? 'selected' : '' }}>Minuman</option>
                            <option value="Special" {{ old('category') == 'Special' ? 'selected' : '' }}>Special</option>
                            <option value="Limited Edition" {{ old('category') == 'Limited Edition' ? 'selected' : '' }}>Limited Edition</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Harga (Rp) *</label>
                        <input type="number" id="price" name="price" value="{{ old('price') }}" required min="0" placeholder="10000">
                    </div>

                    <div class="form-group">
                        <label for="stock">Stok Awal *</label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock') }}" required min="0" placeholder="100">
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" rows="4" placeholder="Deskripsikan produk Anda...">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Gambar Produk</label>
                        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                        <small>Format: JPG, PNG, GIF. Maksimal 10MB</small>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">💾 Simpan Produk</button>
                        <a href="{{ route('admin.products') }}" class="btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('imagePreview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 300px; border-radius: 8px; margin-top: 10px;">`;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
