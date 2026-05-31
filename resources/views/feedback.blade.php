<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback & Saran - Choco Jell</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="css/feedback.css">
</head>
<body>
    <!-- Navigation -->
    <div class="navbar">
        <div class="logo-nav">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 30px; height: 30px; border-radius: 5px;">
            <span style="font-weight: 600;">Choco Jell</span>
        </div>
        <a href="{{ route('index') }}">← Kembali ke Beranda</a>
    </div>

    <!-- Feedback Container -->
    <div class="feedback-container">
        <div class="feedback-header">
            <h1>Feedback & Saran</h1>
            <p>Kami mendengarkan, kirimkan masukan Anda untuk kami</p>
        </div>

        <div class="feedback-content">
            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Terjadi kesalahan!</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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

            <form action="{{ route('feedback.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="title">Judul Feedback *</label>
                        <input type="text" id="title" name="title" placeholder="Contoh: Produk berkualitas tinggi" 
                               value="{{ old('title') }}" required maxlength="255">
                        <small>Berikan judul singkat untuk feedback Anda</small>
                    </div>

                    <div class="form-group">
                        <label for="message">Pesan/Saran *</label>
                        <textarea id="message" name="message" placeholder="Silakan tulis feedback atau saran Anda secara detail..." 
                                  required maxlength="5000">{{ old('message') }}</textarea>
                        <small>Maksimal 5000 karakter</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-submit">Kirim Feedback</button>
                        <a href="{{ route('index') }}" class="btn btn-back" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">Batal</a>
                    </div>
                </form>
        </div>
    </div>
</body>
</html>
