<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Грешка на серверу | Факултет за спорт</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-secondary-100 font-sans">
    <div class="max-w-lg mx-auto my-24 px-4 text-center">
        <div class="text-8xl font-bold text-danger-600 leading-none mb-4">500</div>
        <p class="text-2xl text-secondary-500 mb-4">Грешка на серверу</p>
        <p class="text-secondary-400 mb-6">Дошло је до грешке при обради вашег захтева. Молимо покушајте касније.</p>
        <x-button variant="primary" size="md" href="{{ url('/') }}">
            <i class="fas fa-home mr-2"></i> На почетну страницу
        </x-button>
    </div>
</body>
</html>
