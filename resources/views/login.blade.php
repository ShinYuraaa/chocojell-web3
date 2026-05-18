<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Choco Jell ID</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <a href="{{ route('index') }}" class="back-home">← Back to Home</a>
    
    <div class="login-container">
        <div class="login-header">
            <img src="{{ asset('img/logo.png') }}" alt="Choco Jell Logo">
            <h2>Welcome Back!</h2>
            <p>Please login to your account</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="input-group">
                <label for="email">User ID</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your User ID" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="login-button">Login</button>

            <div class="signup-link">
                Don't have an account? <a href="{{ route('signup') }}">Sign up</a>
            </div>
        </form>
    </div>
</body>
</html>
