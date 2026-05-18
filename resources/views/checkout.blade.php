<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Choco Jell</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
</head>
<body>
    <!-- Navbar -->
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

    <div class="checkout-container">
        <!-- Form Data Customer -->
        <div class="checkout-section">
            <h2>📋 Data Pengiriman</h2>
            
            @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                @csrf
                
                <input type="hidden" name="cart" id="cart" value="">
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label for="no_telp">Nomor Telepon/WhatsApp *</label>
                    <input type="tel" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required placeholder="08xxxxxxxxxx">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat Lengkap Pengiriman *</label>
                    <textarea id="alamat" name="alamat" rows="4" required placeholder="Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos">{{ old('alamat') }}</textarea>
                </div>

                <button type="submit" class="btn-checkout">Lanjut ke Pembayaran →</button>
            </form>
        </div>

        <!-- Ringkasan Pesanan -->
        <div class="checkout-section">
            <h2>🛒 Ringkasan Pesanan</h2>
            
            <div class="cart-items" id="cartItems">
                <!-- Cart items will be loaded from localStorage -->
            </div>

            <div class="total-section">
                <div class="total-row grand-total">
                    <span>Total Pembayaran:</span>
                    <span id="totalAmount">Rp 0</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load cart from localStorage
        const cart = JSON.parse(localStorage.getItem('chocojell_cart')) || [];
        
        // Redirect back if cart is empty
        if (cart.length === 0) {
            alert('Keranjang Anda kosong!');
            window.location.href = '{{ route('menu') }}';
        }

        // Render cart items
        function renderCart() {
            const cartItemsContainer = document.getElementById('cartItems');
            const totalAmountElement = document.getElementById('totalAmount');
            let total = 0;
            let html = '';

            cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                html += `
                    <div class="cart-item">
                        <img src="${item.image}" alt="${item.name}">
                        <div class="cart-item-info">
                            <h4>${item.name}</h4>
                            <p>Jumlah: ${item.quantity} x Rp ${item.price.toLocaleString('id-ID')}</p>
                            <div class="cart-item-price">Subtotal: Rp ${subtotal.toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                `;
            });

            cartItemsContainer.innerHTML = html;
            totalAmountElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        renderCart();

        // Submit form with cart data
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            document.getElementById('cart').value = JSON.stringify(cart);
        });
    </script>
</body>
</html>
