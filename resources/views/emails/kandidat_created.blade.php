<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Добродошли</title>
</head>
<body>
    <h1>Добродошли на Факултет за спорт!</h1>
    
    <p>Поштовани/а {{ $kandidat->imeKandidata }} {{ $kandidat->prezimeKandidata }},</p>
    
    <p>Успешно сте се уписали на Факултет за спорт. Ваши приступни подаци:</p>
    
    <ul>
        <li>Број индекса: {{ $kandidat->brojIndeksa }}</li>
        <li>Емаил: {{ $kandidat->email }}</li>
    </ul>
    
    <p>Срдан поздрав,<br>Факултет за спорт</p>
</body>
</html>
