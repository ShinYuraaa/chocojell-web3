<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Choco Jell</title>
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
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
          <li><a href="{{ route('logout.get') }}" style="background: #ff6b6b; padding: 10px 20px; border-radius: 25px; color: white;">Logout ({{ Session::get('user_name') }})</a></li>
        @else
          <li><a href="{{ route('login') }}" class="order-btn">Order Now</a></li>
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
        <p class="section-subtitle">Chocojell is Mine, Your, Our Chocolate</p>

        <div class="products-grid">
            @forelse($products as $product)
            <div class="product-card">
                <div class="product-image">
                    <img src="{{ asset($product->image_url ?? 'img/logo.png') }}" alt="{{ $product->product_name }}" />
                    <div class="product-overlay">
                        <a href="{{ route('login') }}" class="add-to-cart-btn">Order Now</a>
                    </div>
                </div>
                <div class="product-info">
                    <h3>{{ $product->product_name }}</h3>
                    <p class="product-description">{{ Str::limit($product->description, 60) }}</p>
                    <div class="product-price">Rp. {{ number_format($product->price, 0, ',', '.') }}</div>
                    <div class="product-tags">
                        @if($product->stock > 0)
                            <span class="tag bestseller">Stok: {{ $product->stock }}</span>
                        @else
                            <span class="tag" style="background: #ff6b6b;">Stok Habis</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                <p>Belum ada produk tersedia. Silakan cek kembali nanti.</p>
            </div>
            @endforelse
        </div>
    </section>



<script>
  document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".read-more-btn");

    buttons.forEach(function (button) {
      button.addEventListener("click", function () {
        const moreText = this.previousElementSibling.querySelector(".more-text");
        if (moreText.style.display === "inline") {
          moreText.style.display = "none";
          this.textContent = "Click for More";
        } else {
          moreText.style.display = "inline";
          this.textContent = "Read Less";
        }
      });
    });
  });
</script>
<!-- end card list -->


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

<!-- Footer -->
</body>
</html>
