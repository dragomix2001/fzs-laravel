<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Факултет за спорт - Пријава</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f5f5f5;
        }
        .login-container {
            max-width: 500px;
            margin: 80px auto;
        }
        .login-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background: #0d6efd;
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .login-header img {
            height: 50px;
            margin-bottom: 15px;
        }
        .login-header h3 {
            margin: 0;
            font-weight: 600;
        }
        .login-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 10px 25px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="{{ asset('images/logo_fzs.png') }}" alt="FZS">
                <h3>Факултет за спорт</h3>
            </div>
            <div class="login-body">
                <form method="POST" action="{{ url('/login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Емаил адреса</label>
                        <input type="text" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Лозинка</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Запамти ме</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Пријава
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
