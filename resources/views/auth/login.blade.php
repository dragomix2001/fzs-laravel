<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Факултет за спорт - Пријава</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-secondary-100 font-sans">
    <div class="max-w-lg mx-auto my-20 px-4">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-primary-600 text-white px-6 py-8 text-center">
                <div class="flex items-center justify-center gap-4 mb-3">
                    <img src="{{ asset('images/logo_fzs.png') }}" alt="FZS" class="h-12">
                    <h3 class="m-0 text-xl font-semibold">Факултет за спорт</h3>
                </div>
            </div>
            <div class="px-6 py-8">
                <form method="POST" action="{{ url('/login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-secondary-700 mb-1">Емаил адреса</label>
                        <input type="text" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('email') border-danger-300 text-danger-900 focus:border-danger-500 focus:ring-danger-500 @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-secondary-700 mb-1">Лозинка</label>
                        <input type="password" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @error('password') border-danger-300 text-danger-900 focus:border-danger-500 focus:ring-danger-500 @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500" id="remember" name="remember">
                        <label class="ml-2 text-sm text-secondary-700" for="remember">Запамти ме</label>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i> Пријава
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
