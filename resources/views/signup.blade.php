<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Choco Jell ID</title>
    <link rel="stylesheet" href="{{ asset('css/signup.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}">
</head>
<body>
    <a href="{{ route('index') }}" class="back-home">Back to Home</a>
    
    <div class="signup-container">
        <div class="signup-header">
            <img src="{{ asset('img/logo.png') }}" alt="Choco Jell ID Logo">
            <h2>Create Account</h2>
            <p>Join us and discover our delicious chocolate selections</p>
        </div>

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

        <form action="{{ route('signup.submit') }}" method="POST">
            @csrf
            <div class="input-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" value="{{ old('fullname') }}" required placeholder="Enter your full name">
            </div>

            <div class="input-group">
                <label for="email">Email Address (User ID)</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="Enter your email">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Create a password">
            </div>

            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" required placeholder="Confirm your password">
            </div>

            <button type="submit" class="signup-button">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="{{ route('login') }}">Login here</a>
        </div>
    </div>
</body>
</html>