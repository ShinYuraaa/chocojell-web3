<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Choco Jell Admin</title>
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
                <a href="{{ route('admin.products') }}" class="nav-item">
                    <span><img src="{{ asset('img/produk.png') }}" alt="produk" style="width: 20px; height: 20px;"></span> Produk
                </a>
                <a href="{{ route('admin.orders') }}" class="nav-item active">
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
                <h1>Manajemen Pesanan</h1>
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

            <!-- Orders Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Customer</th>
                            <th>Alamat</th>
                            <th>Produk</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td><strong>#{{ $order->order_id }}</strong></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->customer_email }}</td>
                            <td>
                                <small>{{ Str::limit($order->products_list ?? 'N/A', 50) }}</small>
                            </td>
                            <td><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></td>
                            <td>
                                <form action="{{ route('admin.order.updateStatus', $order->order_id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="status-select status-{{ str_replace(' ', '-', $order->status) }}">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                        <option value="sedang dibuat" {{ $order->status == 'sedang dibuat' ? 'selected' : '' }}>👨‍🍳 Sedang Dibuat</option>
                                        <option value="dalam perjalanan" {{ $order->status == 'dalam perjalanan' ? 'selected' : '' }}>🚚 Dalam Perjalanan</option>
                                        <option value="selesai" {{ $order->status == 'selesai' ? 'selected' : '' }}>✅ Selesai</option>
                                        <option value="dibatalkan" {{ $order->status == 'dibatalkan' ? 'selected' : '' }}>❌ Dibatalkan</option>
                                    </select>
                                </form>
                            </td>
                            <td>{{ date('d M Y H:i', strtotime($order->created_at)) }}</td>
                            <td>
                                <a href="{{ route('admin.order.detail', $order->order_id) }}" class="btn-view">👁️ Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                Belum ada pesanan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Status Legend -->
            <div class="status-legend">
                <h3>Keterangan Status:</h3>
                <div class="legend-items">
                    <div class="legend-item">
                        <span class="status-badge status-pending">Pending</span>
                        <p>Pesanan baru masuk, menunggu konfirmasi</p>
                    </div>
                    <div class="legend-item">
                        <span class="status-badge status-sedang-dibuat">Sedang Dibuat</span>
                        <p>Pesanan sedang diproses/dibuat</p>
                    </div>
                    <div class="legend-item">
                        <span class="status-badge status-dalam-perjalanan">Dalam Perjalanan</span>
                        <p>Pesanan sedang dikirim ke customer</p>
                    </div>
                    <div class="legend-item">
                        <span class="status-badge status-selesai">Selesai</span>
                        <p>Pesanan telah diterima customer</p>
                    </div>
                    <div class="legend-item">
                        <span class="status-badge status-dibatalkan">Dibatalkan</span>
                        <p>Pesanan dibatalkan</p>
                    </div>
                </div>
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
