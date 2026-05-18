<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - Choco Jell</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/order-status.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
</head>
<body>
    <header class="navbar" style="position: fixed; top: 0; width: 100%; z-index: 1000; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="logo">
            <img src="{{ asset('img/logo.png') }}" alt="Logo">
            <span>Choco Jell</span>
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="{{ route('index') }}">Home</a></li>
                <li><a href="{{ route('menu') }}">Menu</a></li>
                <li><a href="{{ route('my.orders') }}">Pesanan Saya</a></li>
            </ul>
        </nav>
    </header>

    <div class="status-container">
        <div class="status-section">
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <h2>Status Pesanan</h2>

            @if($order->status == 'pending' && $order->payment_method == 'qris')
            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin: 15px 0;">
                <p style="margin: 0; color: #856404;">
                    <strong>⏳ Menunggu Verifikasi Pembayaran</strong><br>
                    <small>Admin sedang memverifikasi bukti pembayaran QRIS Anda. Biasanya membutuhkan waktu 1-2 jam. Terima kasih atas kesabarannya!</small>
                </p>
            </div>
            @endif

            <div class="order-info-grid">
                <div class="info-box">
                    <strong>Order ID</strong>
                    <span>#{{ $order->order_id }}</span>
                </div>
                <div class="info-box">
                    <strong>Tanggal</strong>
                    <span>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}</span>
                </div>
            </div>
            </div>

            <!-- Timeline Status -->
            <div class="timeline">
                <div class="timeline-line">
                    <div class="timeline-progress" style="width: {{ $order->status == 'pending' ? '0%' : ($order->status == 'sedang dibuat' ? '33%' : ($order->status == 'dalam perjalanan' ? '66%' : '100%')) }};"></div>
                </div>
                
                <div class="timeline-steps">
                    <div class="timeline-step {{ in_array($order->status, ['sedang dibuat', 'dalam perjalanan', 'selesai']) ? 'active' : '' }}">
                        <div class="step-icon">📝</div>
                        <div class="step-label">Pesanan Diterima</div>
                        <div class="step-time">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</div>
                    </div>

                    <div class="timeline-step {{ in_array($order->status, ['sedang dibuat', 'dalam perjalanan', 'selesai']) ? 'active' : '' }}">
                        <div class="step-icon">👨‍🍳</div>
                        <div class="step-label">Sedang Dibuat</div>
                        <div class="step-time">{{ $order->status == 'sedang dibuat' ? 'Sedang proses' : '' }}</div>
                    </div>

                    <div class="timeline-step {{ in_array($order->status, ['dalam perjalanan', 'selesai']) ? 'active' : '' }}">
                        <div class="step-icon">🚚</div>
                        <div class="step-label">Dalam Perjalanan</div>
                        <div class="step-time">{{ $order->status == 'dalam perjalanan' ? 'Dalam perjalanan' : '' }}</div>
                    </div>

                    <div class="timeline-step {{ $order->status == 'selesai' ? 'active' : '' }}">
                        <div class="step-icon">✅</div>
                        <div class="step-label">Selesai</div>
                        <div class="step-time">{{ $order->status == 'selesai' ? 'Selesai' : '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Pesanan -->
        <div class="status-section">
            <h2>Produk Pesanan</h2>
            
            <div class="product-list">
                @foreach($orderDetails as $detail)
                <div class="product-item">
                    <img src="{{ asset($detail->image_url ?? 'img/logo.png') }}" alt="{{ $detail->product_name }}">
                    <div class="product-info">
                        <h4>{{ $detail->product_name }}</h4>
                        <p class="product-quantity">Jumlah: {{ $detail->quantity }}</p>
                        <p class="product-price">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="total-section">
                <span>Total Pembayaran:</span>
                <div class="total-amount">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="status-section">
            <div class="customer-details">
                <h3>Informasi Pengiriman</h3>
                <div class="detail-row">
                    <strong>Nama:</strong>
                    <span>{{ $order->nama }}</span>
                </div>
                <div class="detail-row">
                    <strong>No. Telepon:</strong>
                    <span>{{ $order->no_telp }}</span>
                </div>
                <div class="detail-row">
                    <strong>Alamat:</strong>
                    <span>{{ $order->alamat }}</span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('my.orders') }}" class="btn-back">← Lihat Semua Pesanan</a>
                <a href="{{ route('menu') }}" class="btn-back" style="background: #28a745;">🛒 Pesan Lagi</a>
            </div>
        </div>
    </div>

    <script>
        // Clear cart from localStorage after successful order
        localStorage.removeItem('chocojell_cart');
    </script>
</body>
</html>
