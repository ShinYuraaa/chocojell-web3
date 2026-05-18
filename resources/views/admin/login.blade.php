<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Choco Jell</title>
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <img src="{{ asset('img/logo.png') }}" alt="Choco Jell Logo">
            <h1>Admin Login</h1>
            <p>Panel Administrasi Choco Jell</p>
        </div>

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

        @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="email">Email Admin</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Masukkan email admin"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-login">
               Login ke Admin Panel
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('index') }}">← Kembali ke Halaman Utama</a>
        </div>

        <div class="security-note">
            <strong>Area Terbatas</strong><br>
            Hanya admin terdaftar yang dapat mengakses panel ini.
        </div>
    </div>

    <script>
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
