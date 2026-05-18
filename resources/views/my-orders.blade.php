<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Choco Jell</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/my-orders.css') }}">
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
                <li><a href="{{ route('my.orders') }}" style="color: #00888a; font-weight: 700;">Pesanan Saya</a></li>
                @if(Session::has('user_id'))
                    <li><a href="{{ route('logout.get') }}" style="color: #ff6b6b;">Logout</a></li>
                @endif
            </ul>
        </nav>
    </header>

    <div class="orders-container">
        <div class="orders-header">
            <h1>Pesanan Saya</h1>
            <p>Lacak dan kelola pesanan Anda di sini</p>
        </div>

        @if($orders->isEmpty())
            <div class="empty-state">
                <div style="font-size: 5rem;">🛒</div>
                <h2>Belum Ada Pesanan</h2>
                <p style="color: #666;">Anda belum melakukan pemesanan. Mulai belanja sekarang!</p>
                <a href="{{ route('menu') }}" class="btn-shop">🍫 Mulai Belanja</a>
            </div>
        @else
            @foreach($orders as $order)
            <div class="order-card">
                <div class="order-header-row">
                    <div>
                        <div class="order-id">Order #{{ $order->order_id }}</div>
                        <div style="color: #666; font-size: 0.9rem;">{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="order-status status-{{ str_replace(' ', '-', $order->status) }}">
                        @if($order->status == 'pending')
                            ⏳ Menunggu Konfirmasi
                        @elseif($order->status == 'sedang dibuat')
                            👨‍🍳 Sedang Dibuat
                        @elseif($order->status == 'dalam perjalanan')
                            🚚 Dalam Perjalanan
                        @elseif($order->status == 'selesai')
                            ✅ Selesai
                        @else
                            ❌ Dibatalkan
                        @endif
                    </div>
                </div>

                <div class="order-info">
                    <div class="info-item">
                        <strong>Penerima:</strong>
                        <span>{{ $order->nama }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Total:</strong>
                        <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div style="text-align: right;">
                    <a href="{{ route('order.status', $order->order_id) }}" class="btn-detail">
                        Lihat Detail
                    </a>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</body>
</html>
