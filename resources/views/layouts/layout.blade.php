<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Факултет за спорт')</title>
    
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 56px;
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-tertiary: #e9ecef;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
            --card-bg: #ffffff;
            --sidebar-bg: #f8f9fa;
            --sidebar-text: #333333;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a2e;
            --bg-secondary: #16213e;
            --bg-tertiary: #0f3460;
            --text-primary: #e4e4e7;
            --text-secondary: #a1a1aa;
            --border-color: #3f3f46;
            --card-bg: #1e1e2f;
            --sidebar-bg: #16162a;
            --sidebar-text: #e4e4e7;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        
        body, .main-content, .card, .table {
            background-color: var(--bg-primary) !important;
            color: var(--text-primary) !important;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
            border-right: 1px solid var(--border-color);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu > li > a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
        }
        
        .sidebar-menu > li > a:hover {
            background: #e9ecef;
            color: #000;
            padding-left: 25px;
            transition: all 0.2s ease;
        }
        
        .sidebar-menu > li > a i:first-child {
            width: 25px;
            margin-right: 10px;
        }
        
        .sidebar-menu > li > a .arrow {
            margin-left: auto;
            font-size: 12px;
            transition: transform 0.2s;
        }
        
        .sidebar-menu > li.open > a .arrow {
            transform: rotate(180deg);
        }
        
        /* Submenu */
        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: none;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
        }
        
        .submenu.show {
            display: block;
        }
        
        .submenu li a {
            display: block;
            padding: 10px 20px 10px 55px;
            color: #555;
            text-decoration: none;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .submenu li a:hover {
            background: #e9ecef;
        }
        
        /* Active state */
        .sidebar-menu > li.active > a {
            background: #0d6efd;
            color: #fff;
        }
        
        .sidebar-menu > li.active > a:hover {
            padding-left: 20px;
        }
        
        .sidebar-menu > li.active .submenu li a.active {
            background: #0d6efd;
            color: #fff;
        }
        
        /* Card styles */
        .content-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        /* Table improvements */
        .table {
            font-size: 14px;
        }
        
        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Badge improvements */
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        /* Mobile responsive tables */
        @media screen and (max-width: 767px) {
            .table-responsive {
                border: none;
            }
            .table {
                font-size: 12px;
            }
            .table th, .table td {
                padding: 6px 8px;
                white-space: nowrap;
            }
            .btn-sm {
                padding: 2px 6px;
                font-size: 11px;
            }
        }
        
        .badge-success-custom {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .badge-warning-custom {
            background: #fff3cd;
            color: #664d03;
        }
        
        .badge-danger-custom {
            background: #f8d7da;
            color: #842029;
        }
        
        .badge-info-custom {
            background: #cff4fc;
            color: #055160;
        }
        
        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
        }
        
        /* Header */
        .top-header {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0 20px;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .top-header .logo-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #212529;
        }
        
        .top-header .logo-link img {
            height: 38px;
        }
        
        .top-header .logo-link span {
            font-weight: 600;
            font-size: 18px;
        }
        
        /* Page header */
        .page-header {
            margin: 20px 0;
            padding: 0 0 15px 0;
            border-bottom: 2px solid #dee2e6;
            font-size: 24px;
            font-weight: 600;
            color: #212529;
        }
        
        /* Mobile toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 5px 10px;
            color: #333;
            transition: color 0.2s;
        }
        
        .mobile-toggle:hover {
            color: #0d6efd;
        }
        
        /* User dropdown */
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px;
        }
        
        .user-dropdown .dropdown-item {
            border-radius: 5px;
            padding: 8px 15px;
        }
        
        .user-dropdown .dropdown-item:hover {
            background: #f8f9fa;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 99;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper" x-data="{ sidebarOpen: false }">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" :class="{ 'show': sidebarOpen }" @click="sidebarOpen = false"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar" :class="{ 'show': sidebarOpen }">
            <ul class="sidebar-menu" id="side-menu">
                <li class="{{ Request::is('*kandidat*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'kandidatSubmenu')">
                        <i class="fas fa-user"></i>
                        <span>Кандидати</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="kandidatSubmenu">
                        <li><a href="{{ url('kandidat/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                        <li><a href="{{ url('kandidat?studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*master*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'masterSubmenu')">
                        <i class="fas fa-book"></i>
                        <span>Мастер кандидати</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="masterSubmenu">
                        <li><a href="{{ url('master/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                        <li><a href="{{ url('master') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*student*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'studentiSubmenu')">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Активни студенти</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="studentiSubmenu">
                        <li><a href="{{ url('student/index/1?godina=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Основне студије</a></li>
                        <li><a href="{{ url('student/index/2?studijskiProgramId=4') }}">&nbsp;&nbsp;&nbsp;Мастер студије</a></li>
                        <li><a href="{{ url('student/zamrznuti') }}">&nbsp;&nbsp;&nbsp;Статус мировања</a></li>
                        <li><a href="{{ url('student/ispisani') }}">&nbsp;&nbsp;&nbsp;Исписани студенти</a></li>
                        <li><a href="{{ url('student/diplomirani?tipStudijaId=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Дипломирани</a></li>
                        <li><a href="{{ url('/izvestaji/spiskoviStudenti') }}">&nbsp;&nbsp;&nbsp;Извештаји</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*kalendar*') || Request::is('*predmeti*') || Request::is('*zapisnik*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'ispitiSubmenu')">
                        <i class="fas fa-calendar"></i>
                        <span>Испити</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="ispitiSubmenu">
                        <li><a href="{{ url('/kalendar/') }}">&nbsp;&nbsp;&nbsp;Календар</a></li>
                        <li><a href="{{ url('/predmeti/') }}">&nbsp;&nbsp;&nbsp;Пријава испита</a></li>
                        <li><a href="{{ url('/zapisnik/') }}">&nbsp;&nbsp;&nbsp;Записник</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*tipStudija*') || Request::is('*studijskiProgram*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'adminSifarniciSubmenu')">
                        <i class="fas fa-cogs"></i>
                        <span>Админ шифарници</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="adminSifarniciSubmenu">
                        <li><a href="{{ url('/tipStudija') }}">&nbsp;&nbsp;&nbsp;Тип студија</a></li>
                        <li><a href="{{ url('/studijskiProgram') }}">&nbsp;&nbsp;&nbsp;Студијски програм</a></li>
                        <li><a href="{{ url('/godinaStudija') }}">&nbsp;&nbsp;&nbsp;Година студија</a></li>
                        <li><a href="{{ url('statusStudiranja') }}">&nbsp;&nbsp;&nbsp;Статус студирања</a></li>
                        <li><a href="{{ url('semestar') }}">&nbsp;&nbsp;&nbsp;Семестар</a></li>
                        <li><a href="{{ url('ispitniRok') }}">&nbsp;&nbsp;&nbsp;Испитни рок</a></li>
                        <li><a href="{{ url('oblikNastave') }}">&nbsp;&nbsp;&nbsp;Облик наставe</a></li>
                        <li><a href="{{ url('tipPredmeta') }}">&nbsp;&nbsp;&nbsp;Тип предмета</a></li>
                        <li><a href="{{ url('bodovanje') }}">&nbsp;&nbsp;&nbsp;Бодовање</a></li>
                        <li><a href="{{ url('statusKandidata') }}">&nbsp;&nbsp;&nbsp;Статус годыдине</a></li>
                        <li><a href="{{ url('statusIspita') }}">&nbsp;&nbsp;&nbsp;Статус испита</a></li>
                        <li><a href="{{ url('statusProfesora') }}">&nbsp;&nbsp;&nbsp;Статус професора</a></li>
                        <li><a href="{{ url('tipPrijave') }}">&nbsp;&nbsp;&nbsp;Тип пријаве</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*sport*') || Request::is('*predmet*') || Request::is('*profesor*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'sifarniciSubmenu')">
                        <i class="fas fa-list"></i>
                        <span>Шифарници</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="sifarniciSubmenu">
                        <li><a href="{{ url('sport') }}">&nbsp;&nbsp;&nbsp;Спортови</a></li>
                        <li><a href="{{ url('predmet') }}">&nbsp;&nbsp;&nbsp;Предмет</a></li>
                        <li><a href="{{ url('profesor') }}">&nbsp;&nbsp;&nbsp;Професор</a></li>
                        <li><a href="{{ url('krsnaSlava') }}">&nbsp;&nbsp;&nbsp;Крсна слава</a></li>
                        <li><a href="{{ url('region') }}">&nbsp;&nbsp;&nbsp;Регион</a></li>
                        <li><a href="{{ url('opstina') }}">&nbsp;&nbsp;&nbsp;Општина</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*prisustvo*') || Request::is('*aktivnost*') || Request::is('*raspored*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'nastavaSubmenu')">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Настава</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="nastavaSubmenu">
                        <li><a href="{{ url('/raspored') }}">&nbsp;&nbsp;&nbsp;Распоред</a></li>
                        <li><a href="{{ url('/prisustvo') }}">&nbsp;&nbsp;&nbsp;Присуство</a></li>
                        <li><a href="{{ url('/aktivnost') }}">&nbsp;&nbsp;&nbsp;Активности</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*obavestenja*') || Request::is('*moja-obavestenja*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'komunikacijaSubmenu')">
                        <i class="fas fa-bullhorn"></i>
                        <span>Комуникација</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="komunikacijaSubmenu">
                        <li><a href="{{ url('/obavestenja') }}">&nbsp;&nbsp;&nbsp;Обавештења</a></li>
                        <li><a href="{{ url('/moja-obavestenja') }}">&nbsp;&nbsp;&nbsp;Моја обавештења</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*chatbot*') || Request::is('*prediction*') || Request::is('*dashboard*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'analitikaSubmenu')">
                        <i class="fas fa-chart-pie"></i>
                        <span>Аналитика и AI</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="analitikaSubmenu">
                        <li><a href="{{ url('/dashboard') }}">&nbsp;&nbsp;&nbsp;Аналитика</a></li>
                        <li><a href="{{ url('/chatbot') }}">&nbsp;&nbsp;&nbsp;AI Чатбот</a></li>
                        <li><a href="{{ url('/prediction') }}">&nbsp;&nbsp;&nbsp;AI Предикција</a></li>
                    </ul>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="flex items-center">
                    <button class="mobile-toggle mr-3" id="sidebarToggle" @click="sidebarOpen = !sidebarOpen">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="{{ url('') }}" class="logo-link">
                        <img src="{{ asset('images/logo_fzs.png') }}" height="38" loading="lazy">
                        <span>Факултет за спорт</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ url('/pretraga') }}" class="px-3 py-1.5 border border-gray-400 text-gray-700 hover:bg-gray-100 rounded text-sm transition-colors">
                        <i class="fas fa-search"></i>
                    </a>
                    
                    <button type="button" class="px-3 py-1.5 border border-gray-400 text-gray-700 hover:bg-gray-100 rounded text-sm transition-colors" id="themeToggle" title="Тема">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                    
                    @if(!Auth::guest())
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="px-3 py-1.5 border border-gray-400 text-gray-700 hover:bg-gray-100 rounded text-sm flex items-center transition-colors" type="button" aria-expanded="false">
                                <i class="fas fa-user-circle mr-2"></i>
                                {{ Auth::user()->name }}
                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt mr-2"></i>Одјава</a>
                            </div>
                        </div>
                    @endif
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-3">
                <h1 class="page-header">@yield('page_heading')</h1>
                
                @yield('section')
            </main>
        </div>
    </div>

        @include('partials.toast')
    @include('partials.ajax-loader')
    @stack('scripts')
    
    <script>
        // Toggle submenu function
        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            var submenu = document.getElementById(submenuId);
            var parentLi = submenu.parentElement;
            
            // Toggle current submenu
            submenu.classList.toggle('show');
            parentLi.classList.toggle('open');
        }
        


        // Dark mode toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            if (newTheme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        });
    </script>
</body>
</html>
