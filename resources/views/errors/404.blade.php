<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Страница не постоји | Факултет за спорт</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f5f5f5;
        }
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #0d6efd;
            line-height: 1;
        }
        .error-message {
            font-size: 24px;
            color: #6c757d;
            margin: 20px 0;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-code">404</div>
            <p class="error-message">Страница коју тражите не постоји</p>
            <p class="text-muted">Страница је можда уклоњена или сте погрешили у адреси.</p>
            <a href="{{ url('/') }}" class="btn btn-primary mt-3">
                <i class="fas fa-home me-2"></i> На почетну страницу
            </a>
        </div>
    </div>
</body>
</html>
