<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choco Jell - Shop</title>
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
</head>

<body>
    <!-- Navbar -->
    <header class="navbar">
        <div class="logo">
            <img src="{{ asset('img/logo.png') }}" alt="Logo">
            <span>Choco Jell</span>
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="{{ route('index') }}">Home</a></li>
                <li><a href="{{ route('sageteam') }}">Sage Team</a></li>
                <li><a href="https://www.instagram.com/chocojell.id?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==">Social Media</a></li>
                <li><a href="https://wa.me/+6281291437490">Contact Us</a></li>
                @if(Session::has('user_id'))
                    <li><a href="{{ route('my.orders') }}">Pesanan Saya</a></li>
                @endif
                <li>
                    <a href="#" class="cart-icon" id="cartIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span class="cart-count" id="cartCount">0</span>
                    </a>
                </li>
                @if(Session::has('user_id'))
                    <li><a href="{{ route('logout.get') }}" style="color: #ff6b6b;">Logout</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="order-btn">Login</a></li>
                @endif
            </ul>
        </nav>
    </header>

    <section class="hero">
        <img src="{{ asset('img/choco-jell.png') }}" alt="Responsive Image">
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <h2 class="section-title">Berbagai Varian Rasa</h2>
        <p class="section-subtitle">Chocojell is Mine,Your,Our Chocolate</p>

        <div class="products-grid">
            @forelse($products as $product)
            <div class="product-card" data-product-id="{{ $product->product_id }}">
                <div class="product-image">
                    <img src="{{ asset($product->image_url ?? 'img/logo.png') }}" alt="{{ $product->product_name }}" />
                    @if($product->stock <= 0)
                        <div class="out-of-stock-badge">Stok Habis</div>
                    @endif
                    <div class="product-overlay">
                        <button class="add-to-cart-btn" 
                                {{ $product->stock <= 0 ? 'disabled' : '' }} 
                                data-product-id="{{ $product->product_id }}"
                                data-name="{{ $product->product_name }}" 
                                data-price="{{ $product->price }}"
                                data-image="{{ asset($product->image_url ?? 'img/logo.png') }}"
                                onclick="addToCart({{ $product->product_id }}, '{{ $product->product_name }}', {{ $product->price }}, '{{ asset($product->image_url ?? 'img/logo.png') }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            {{ $product->stock <= 0 ? 'Stok Habis' : 'Add to Cart' }}
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3>{{ $product->product_name }}</h3>
                    <p class="product-description">{{ Str::limit($product->description, 80) }}</p>
                    <div class="product-price">Rp. {{ number_format($product->price, 0, ',', '.') }}</div>
                    <div class="product-tags">
                        <span class="tag {{ $product->stock > 10 ? 'bestseller' : ($product->stock > 0 ? 'popular' : 'limited') }}">
                            Stok: {{ $product->stock }}
                        </span>
                        @if($product->category)
                            <span class="tag new">{{ $product->category }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                <h3>Belum ada produk tersedia</h3>
                <p>Admin belum menambahkan produk. Silakan cek kembali nanti.</p>
            </div>
            @endforelse
        </div>
    </section>

    <!-- Cart Modal -->
    <div class="cart-modal" id="cartModal">
        <div class="cart-content">
            <div class="cart-header">
                <h2>Shopping Cart</h2>
                <button class="close-cart">&times;</button>
            </div>
            <div class="cart-items">
                <!-- Cart items will be inserted here -->
            </div>
            <div class="cart-total">
                <span>Total:</span>
                <span class="total-amount">Rp. 0</span>
            </div>
            <button class="checkout-btn">Checkout </button>
        </div>
    </div>

    <!-- Footer -->
    <div id="contact">
      <div class="wrapper">
        <div class="footer">
          <div class="footer-section">
            <h3>Kelompok Sage</h3>
            <p>Kami kelompok Dari sage dari Perbanas bekasi:</p>
            <p>Rafif Febrian Putra NIM:2314000002</p>
            <p>Muhammad Syaiful Fajri NIM: 2314000005</p>
            <p>Louis Ponglabba NIM: 2314000010 </p>
            <p>Muhammad Zidan Fikri  NIM: 2314000020</p>
            <p>Muhammad Faqih Fadlurohman NIM: 2314000025</p>
            <p>Zahra Nadhifah Nasution NIM:2314000017</p>
            <p>alamat Perbanas bekasi:</p>
            <p>
              Jl. Cut Mutia No.2, RT.001/RW.003, Sepanjang Jaya, Kec. Rawalumbu,
              Kota Bks, Jawa Barat 17114
            </p>
          </div>
          <div class="footer-section">
            <h3>About</h3>
            <p>
              Chocojell is Mine, Your, Our Chocolate. 
            </p>
            <p>
              Lebih dari sekadar cokelat Chocojell adalah wujud cinta dan kebahagiaan yang bisa dinikmati bersama. 
              Kami percaya, setiap gigitan menghadirkan momen manis yang menyatukan kita semua.
            </p>
          </div>
          <div class="footer-section">
            <h3>Contact</h3>
            <p>
              Ingin memesan atau bertanya lebih lanjut?
              Silakan klik Contact Us / Order di bagian atas, atau hubungi kami melalui Social Media.
              Kami siap membantu Anda mendapatkan cokelat terbaik dari Chocojell.
            </p>
          </div>
          <div class="footer-section">
            <h3>Social media</h3>
          <p>
            Jangan lewatkan keseruan dan promo terbaru dari kami!
            Instagram: @chocojell.id
            Ikuti perjalanan manis kami dan temukan inspirasi cokelat setiap harinya.
          </p>
          </div>
        </div>
      </div>
    </div>

    <div id="copyright">
        <div class="wrapper">
            &copy; 2025. <b>Kelompok sage</b> All Rights Reserved.
        </div>
    </div>

    <script>
        // Initialize cart from localStorage
        let cart = JSON.parse(localStorage.getItem('chocojell_cart')) || [];
        
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            const cartModal = document.getElementById('cartModal');
            const cartIcon = document.getElementById('cartIcon');
            const closeCart = document.querySelector('.close-cart');
            const checkoutBtn = document.querySelector('.checkout-btn');

            // Show cart modal
            cartIcon.addEventListener('click', (e) => {
                e.preventDefault();
                renderCart();
                cartModal.style.display = 'flex';
            });

            // Close cart modal
            closeCart.addEventListener('click', () => {
                cartModal.style.display = 'none';
            });

            // Close on outside click
            cartModal.addEventListener('click', (e) => {
                if (e.target === cartModal) {
                    cartModal.style.display = 'none';
                }
            });

            // Checkout button
            checkoutBtn.addEventListener('click', () => {
                if (cart.length === 0) {
                    alert('Keranjang Anda masih kosong!');
                    return;
                }

                @if(!Session::has('user_id'))
                    alert('Silakan login terlebih dahulu untuk checkout!');
                    window.location.href = '{{ route('login') }}';
                @else
                    // Save cart to session and redirect to checkout
                    localStorage.setItem('chocojell_cart', JSON.stringify(cart));
                    window.location.href = '{{ route('checkout') }}';
                @endif
            });
        });

        // Add to cart function
        function addToCart(productId, productName, price, imageUrl) {
            // Check if product already exists in cart
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: price,
                    image: imageUrl,
                    quantity: 1
                });
            }

            // Save to localStorage
            localStorage.setItem('chocojell_cart', JSON.stringify(cart));
            
            // Update UI
            updateCartCount();
            showNotification(productName + ' ditambahkan ke keranjang!');
        }

        // Update cart count badge
        function updateCartCount() {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = totalItems;
        }

        // Render cart items in modal
        function renderCart() {
            const cartItems = document.querySelector('.cart-items');
            const totalAmount = document.querySelector('.total-amount');
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p style="text-align: center; padding: 40px; color: #666;">Keranjang Anda kosong</p>';
                totalAmount.textContent = 'Rp 0';
                return;
            }

            let html = '';
            let total = 0;

            cart.forEach((item, index) => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                html += `
                    <div class="cart-item" data-index="${index}">
                        <img src="${item.image}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                        <div class="item-details" style="flex: 1; padding: 0 15px;">
                            <h4 style="margin: 0 0 5px 0; font-size: 1rem;">${item.name}</h4>
                            <p style="margin: 0; color: #00888a; font-weight: 600;">Rp ${item.price.toLocaleString('id-ID')}</p>
                        </div>
                        <div class="quantity-controls" style="display: flex; align-items: center; gap: 10px;">
                            <button onclick="updateQuantity(${index}, -1)" style="width: 30px; height: 30px; border: 1px solid #ddd; background: white; border-radius: 5px; cursor: pointer;">-</button>
                            <span style="font-weight: 600; min-width: 20px; text-align: center;">${item.quantity}</span>
                            <button onclick="updateQuantity(${index}, 1)" style="width: 30px; height: 30px; border: 1px solid #ddd; background: white; border-radius: 5px; cursor: pointer;">+</button>
                        </div>
                        <button onclick="removeFromCart(${index})" class="remove-item" style="margin-left: 15px; background: #ff6b6b; color: white; border: none; width: 30px; height: 30px; border-radius: 5px; cursor: pointer; font-size: 1.2rem;">&times;</button>
                    </div>
                `;
            });

            cartItems.innerHTML = html;
            totalAmount.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Update quantity
        function updateQuantity(index, change) {
            if (cart[index].quantity + change <= 0) {
                removeFromCart(index);
                return;
            }
            
            cart[index].quantity += change;
            localStorage.setItem('chocojell_cart', JSON.stringify(cart));
            updateCartCount();
            renderCart();
        }

        // Remove item from cart
        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('chocojell_cart', JSON.stringify(cart));
            updateCartCount();
            renderCart();
        }

        // Show notification
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: #00888a;
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,136,138,0.3);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
            .cart-item {
                display: flex;
                align-items: center;
                padding: 15px;
                border-bottom: 1px solid #eee;
            }
            .cart-item:last-child {
                border-bottom: none;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
