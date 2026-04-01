<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Факултет за спорт</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Lato';
        }
        .fa-btn {
            margin-right: 6px;
        }
    </style>
</head>
<body id="app-layout" class="bg-gray-100 font-sans text-gray-900 leading-normal">
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-8 w-auto mr-2" src="{{ asset('images/logo_fzs.png') }}" loading="lazy" alt="Logo">
                        <a href="{{ url('') }}" class="font-bold text-xl text-gray-800 tracking-tight hover:text-gray-600">Факултет за спорт</a>
                    </div>
                </div>

                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    @if (Auth::guest())
                        <div class="space-x-4">
                            <a href="{{ url('/login') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Пријава</a>
                            <a href="{{ url('/register') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Регистрација</a>
                        </div>
                    @else
                        <div class="ml-3 relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <div>
                                <button @click="dropdownOpen = !dropdownOpen" type="button" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out" id="user-menu" aria-expanded="false" aria-haspopup="true">
                                    <span class="text-gray-700 font-medium px-3 py-2">{{ Auth::user()->name }} <i class="fas fa-caret-down ml-1"></i></span>
                                </button>
                            </div>

                            <div x-show="dropdownOpen" 
                                 x-transition:enter="transition ease-out duration-100" 
                                 x-transition:enter-start="transform opacity-0 scale-95" 
                                 x-transition:enter-end="transform opacity-100 scale-100" 
                                 x-transition:leave="transition ease-in duration-75" 
                                 x-transition:leave-start="transform opacity-100 scale-100" 
                                 x-transition:leave-end="transform opacity-0 scale-95" 
                                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5" 
                                 role="menu" aria-orientation="vertical" aria-labelledby="user-menu" style="display: none;">
                                <a href="{{ url('/logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem"><i class="fas fa-sign-out-alt mr-2"></i>Одјава</a>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <span class="sr-only">Отвори мени</span>
                        <i class="fas fa-bars text-xl" x-show="!open"></i>
                        <i class="fas fa-times text-xl" x-show="open" style="display: none;"></i>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="open" class="sm:hidden" style="display: none;">
            <div class="pt-2 pb-3 space-y-1">
                @if (Auth::guest())
                    <a href="{{ url('/login') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">Пријава</a>
                    <a href="{{ url('/register') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">Регистрација</a>
                @else
                    <div class="border-t border-gray-200 pt-4 pb-1">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a href="{{ url('/logout') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-100 transition duration-150 ease-in-out">Одјава</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/4.0.0/jquery.min.js"></script>
</body>
</html>
