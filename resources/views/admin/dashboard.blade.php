<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Choco Jell</title>
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
                <a href="{{ route('admin.dashboard') }}" class="nav-item active">
                    <span><img src="{{ asset('img/dashboard.png') }}" alt="dashboard" style="width: 20px; height: 20px;"></span> Dashboard
                </a>
                <a href="{{ route('admin.products') }}" class="nav-item">
                    <span><img src="{{ asset('img/produk.png') }}" alt="produk" style="width: 20px; height: 20px;"></span> Produk
                </a>
                <a href="{{ route('admin.orders') }}" class="nav-item">
                    <span><img src="{{ asset('img/pesanan.png') }}" alt="pesanan" style="width: 20px; height: 20px;"></span> Pesanan
                </a>
                <a href="{{ route('index') }}" class="nav-item" target="_blank">
                    <span><img src="{{ asset('img/home.png') }}" alt="home" style="width: 20px; height: 20px;"></span> Home
                </a>
                <a href="{{ route('admin.logout.get') }}" class="nav-item">
                    <span><img src="{{ asset('img/logout.png') }}" alt="logout" style="width: 20px; height: 20px;"></span> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Selamat datang, Admin</span>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ff6b6b;"><img src="{{ asset('img/produk.png') }}" alt="produk" style="width: 25px; height: 25px;"></div>
                    <div class="stat-info">
                        <h3>Total Produk</h3>
                        <p class="stat-number">{{ $totalProducts }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #4ecdc4;"><img src="{{ asset('img/pesanan.png') }}" alt="pesanan" style="width: 25px; height: 25px;"></div>
                    <div class="stat-info">
                        <h3>Total Pesanan</h3>
                        <p class="stat-number">{{ $totalOrders }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #ffd93d;"><img src="{{ asset('img/pending.png') }}" alt="pending" style="width: 25px; height: 25px;"></div>
                    <div class="stat-info">
                        <h3>Pesanan Pending</h3>
                        <p class="stat-number">{{ $pendingOrders }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #6bcf7f;"><img src="{{ asset('img/correct.png') }}" alt="correct" style="width: 25px; height: 25px;"></div>
                    <div class="stat-info">
                        <h3>Selesai Hari Ini</h3>
                        <p class="stat-number">0</p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Pesanan Terbaru</h2>
                    <a href="{{ route('admin.orders') }}" class="btn-link">Lihat Semua</a>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td>#{{ $order->order_id }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ date('d M Y', strtotime($order->order_date)) }}</td>
                                <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-badge status-{{ str_replace(' ', '-', $order->status) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align: center;">Belum ada pesanan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-section">
                <h2>Aksi Cepat</h2>
                <div class="quick-actions">
                    <a href="{{ route('admin.product.create') }}" class="action-btn">
                        <span><img src="{{ asset('img/plus.png') }}" alt="plus" style="width: 40px; height: 40px;"></span>
                        <div>
                            <h3>Tambah Produk</h3>
                            <p>Tambah produk baru ke katalog</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.orders') }}" class="action-btn">
                        <span><img src="{{ asset('img/order-delivery.png') }}" alt="order-delivery" style="width: 40px; height: 40px;"></span>
                        <div>
                            <h3>Kelola Pesanan</h3>
                            <p>Update status pesanan</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.products') }}" class="action-btn">
                        <span><img src="{{ asset('img/update.png') }}" alt="update" style="width: 40px; height: 40px;"></span>
                        <div>
                            <h3>Update Stok</h3>
                            <p>Perbarui stok produk</p>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
