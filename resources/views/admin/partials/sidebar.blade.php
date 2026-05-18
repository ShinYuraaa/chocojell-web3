<!-- Sidebar -->
<aside class="sidebar">
    <div class="logo">
        <img src="{{ asset('img/logo.png') }}" alt="Logo">
        <h2>Choco Jell Admin</h2>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span>📊</span> Dashboard
        </a>
        <a href="{{ route('admin.products') }}" class="nav-item {{ request()->routeIs('admin.products*') || request()->routeIs('admin.product.*') ? 'active' : '' }}">
            <span>🍫</span> Produk
        </a>
        <a href="{{ route('admin.orders') }}" class="nav-item {{ request()->routeIs('admin.orders*') || request()->routeIs('admin.order.*') ? 'active' : '' }}">
            <span>📦</span> Pesanan
        </a>
        <a href="{{ route('index') }}" class="nav-item">
            <span>🏠</span> Ke Halaman Utama
        </a>
        <a href="{{ route('admin.logout.get') }}" class="nav-item">
            <span>🚪</span> Logout
        </a>
    </nav>
    
    @if(Session::has('admin_name'))
    <div style="padding: 20px; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.8); font-size: 0.85rem;">
        <strong>{{ Session::get('admin_name') }}</strong><br>
        <small>{{ Session::get('admin_email') }}</small>
    </div>
    @endif
</aside>
