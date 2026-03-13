<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Факултет за спорт</h1>
        </div>
        <div class="content">
            <h2>{{ $naslov }}</h2>
            <p>{{ $sadrzaj }}</p>
            <hr>
            <p><strong>Тип:</strong> {{ $tip }}</p>
        </div>
        <div class="footer">
            <p>Ово је аутоматска порука. Молимо вас да не одговарате на овај имејл.</p>
            <p>&copy; {{ date('Y') }} Факултет за спорт</p>
        </div>
    </div>
</body>
</html>
