<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Choco Jell Admin</title>
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
                <h1>Detail Pesanan #{{ $order->order_id }}</h1>
                <a href="{{ route('admin.orders') }}" class="btn-secondary">← Kembali</a>
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

            @if(session('info'))
            <div class="alert alert-info">
                {{ session('info') }}
            </div>
            @endif

            <div class="order-detail-container">
                <!-- Order Info Card -->
                <div class="detail-card">
                    <h2>Informasi Pesanan</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">ID Pesanan:</span>
                            <span class="value">#{{ $order->order_id }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Status:</span>
                            <span class="value">
                                <span class="status-badge status-{{ str_replace(' ', '-', $order->status) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tanggal Order:</span>
                            <span class="value">{{ date('d F Y, H:i', strtotime($order->created_at)) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Total Pembayaran:</span>
                            <span class="value" style="color: #4ecdc4; font-weight: bold; font-size: 1.2em;">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <h3 style="margin-top: 20px;">Update Status Pesanan</h3>
                    <form action="{{ route('admin.order.updateStatus', $order->order_id) }}" method="POST" class="status-update-form">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="status-select-large">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="sedang dibuat" {{ $order->status == 'sedang dibuat' ? 'selected' : '' }}>👨‍🍳 Sedang Dibuat</option>
                            <option value="dalam perjalanan" {{ $order->status == 'dalam perjalanan' ? 'selected' : '' }}>🚚 Dalam Perjalanan</option>
                            <option value="selesai" {{ $order->status == 'selesai' ? 'selected' : '' }}>✅ Selesai</option>
                            <option value="dibatalkan" {{ $order->status == 'dibatalkan' ? 'selected' : '' }}>❌ Dibatalkan</option>
                        </select>
                        <button type="submit" class="btn-primary"><img src="{{ asset('img/update.png') }}" alt="update" style="width: 20px; height: 20px; vertical-align: middle;"> Update Status</button>
                    </form>
                </div>

                <!-- Customer Info Card -->
                <div class="detail-card">
                    <h2>Informasi Customer</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Nama:</span>
                            <span class="value">{{ $order->customer_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Email:</span>
                            <span class="value">{{ $order->customer_email ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Telepon:</span>
                            <span class="value">{{ $order->customer_phone ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Alamat:</span>
                            <span class="value">{{ $order->customer_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Proof Card (QRIS) -->
                @if($order->payment_method == 'qris' && $order->payment_proof_path)
                <div class="detail-card" style="background: #f0fffe; border-left: 4px solid #00888a;">
                    <h2>💳 Bukti Pembayaran QRIS</h2>
                    <div style="margin: 15px 0;">
                        <img src="{{ route('admin.payment.proof', $order->order_id) }}" alt="Bukti Pembayaran" style="max-width: 400px; width: 100%; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    
                    @if($order->payment_verified_at)
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 6px; margin: 10px 0;">
                        <p style="margin: 0; color: #155724;">
                            <strong>✅ Pembayaran Terverifikasi</strong><br>
                            Diverifikasi pada: {{ date('d F Y H:i', strtotime($order->payment_verified_at)) }}
                        </p>
                    </div>
                    @else
                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 6px; margin: 10px 0;">
                        <p style="margin: 0; color: #856404;">
                            <strong>⏳ Menunggu Verifikasi</strong><br>
                            Silakan periksa bukti pembayaran di atas. Jika valid, klik tombol verifikasi di bawah.
                        </p>
                    </div>
                    <div style="margin-top: 15px;">
                        <form action="{{ route('admin.order.verifyPayment', $order->order_id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-primary" style="background: #28a745; border: none; padding: 10px 20px; border-radius: 6px; color: white; cursor: pointer; font-weight: bold;">
                                ✅ Verifikasi Pembayaran
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @elseif($order->payment_method == 'qris')
                <div class="detail-card" style="background: #f8d7da; border-left: 4px solid #f5222d;">
                    <h2>⚠️ Bukti Pembayaran Belum Ada</h2>
                    <p style="color: #721c24; margin: 0;">Customer belum mengunggah bukti pembayaran QRIS.</p>
                </div>
                @endif

                <!-- Order Items Card -->
                <div class="detail-card">
                    <h2>Detail Produk</h2>
                    <div class="order-items">
                        @foreach($orderDetails as $item)
                        <div class="order-item">
                            <div class="item-image">
                                <img src="{{ asset($item->image_url ?? 'img/logo.png') }}" alt="{{ $item->product_name }}">
                            </div>
                            <div class="item-details">
                                <h3>{{ $item->product_name }}</h3>
                                <p class="item-price">Rp {{ number_format($item->price, 0, ',', '.') }} x {{ $item->quantity }}</p>
                            </div>
                            <div class="item-total">
                                <strong>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="order-total">
                        <span>Total:</span>
                        <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
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
