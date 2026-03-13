<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2ecc71; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .details { margin: 20px 0; }
        .details table { width: 100%; }
        .details td { padding: 8px; }
        .details td:first-child { font-weight: bold; width: 40%; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Факултет за спорт</h1>
        </div>
        <div class="content">
            <h2>Потврда о пријави испита</h2>
            <p>Поштовани {{ $student }},</p>
            <p>Ваша пријава је успешно регистрована.</p>
            
            <div class="details">
                <table>
                    <tr>
                        <td>Предмет:</td>
                        <td>{{ $predmet }}</td>
                    </tr>
                    <tr>
                        <td>Испитни рок:</td>
                        <td>{{ $rok }}</td>
                    </tr>
                    <tr>
                        <td>Датум:</td>
                        <td>{{ $datum }}</td>
                    </tr>
                </table>
            </div>
            
            <p>Желимо вам успех на испиту!</p>
        </div>
        <div class="footer">
            <p>Ово је аутоматска порука. Молимо вас да не одговарате на овај имејл.</p>
            <p>&copy; {{ date('Y') }} Факултет за спорт</p>
        </div>
    </div>
</body>
</html>
