<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Choco Jell Admin</title>
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
                <h1>Manajemen Produk</h1>
                <a href="{{ route('admin.product.create') }}" class="btn-primary">+ Tambah Produk</a>
            </header>

            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
            @endif

            <!-- Products Grid -->
            <div class="products-management-grid">
                @forelse($products as $product)
                <div class="product-management-card">
                    <div class="product-image-container">
                        <img src="{{ asset($product->image_url ?? 'img/logo.png') }}" alt="{{ $product->product_name }}">
                        <div class="stock-badge {{ $product->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                            Stok: {{ $product->stock }}
                        </div>
                    </div>
                    <div class="product-details">
                        <h3>{{ $product->product_name }}</h3>
                        <p class="product-category">{{ $product->category }}</p>
                        <p class="product-desc">{{ Str::limit($product->description, 80) }}</p>
                        <div class="product-price-tag">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    </div>
                    <div class="product-actions">
                        <a href="{{ route('admin.product.edit', $product->product_id) }}" class="btn-edit">
                            ✏️ Edit
                        </a>
                        <form action="{{ route('admin.product.delete', $product->product_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">🗑️ Hapus</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <p>Belum ada produk. Tambahkan produk pertama Anda!</p>
                    <a href="{{ route('admin.product.create') }}" class="btn-primary">+ Tambah Produk</a>
                </div>
                @endforelse
            </div>
        </main>
    </div>

    <script>
        // Auto hide alerts after 3 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);
    </script>
</body>
</html>
