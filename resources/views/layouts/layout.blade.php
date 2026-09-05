<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Факултет за спорт')</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.dataTables.min.css">
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
        
        .sidebar-menu > li.active > a {
            background: #0d6efd;
            color: #fff;
        }
        
        .sidebar-menu > li.active > a:hover {
            padding-left: 20px;
        }
        
        .sidebar-menu > li.submenu-parent-active .submenu li a.active {
            background: #0d6efd;
            color: #fff;
        }

        .sidebar-menu > li.submenu-parent-active .submenu {
            display: block;
        }
        
        .content-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
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
        

        
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
        }
        
        .top-header {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
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

        /* DataTables Bootstrap 5 integration */
        .dataTables_wrapper .dataTables_length select { display: inline-block; width: auto; }
        .dataTables_wrapper .dataTables_filter input { display: inline-block; width: auto; margin-left: 0.5rem; }
        .dataTables_wrapper .dataTables_info { padding-top: 0.75rem; }
        .dataTables_wrapper .dataTables_paginate { padding-top: 0.75rem; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0.375rem 0.75rem; margin-left: 2px; border-radius: 0.375rem; border: 1px solid #dee2e6; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #0d6efd; color: #fff !important; border-color: #0d6efd; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #e9ecef; border-color: #dee2e6; }
    </style>
</head>
<body>
    <div class="wrapper" x-data="{ sidebarOpen: false }">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" :class="{ 'show': sidebarOpen }" @click="sidebarOpen = false"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar" :class="{ 'show': sidebarOpen }">
            <ul class="sidebar-menu" id="side-menu">
                <li class="{{ Request::is('*kandidat*') || Request::is('*kandidat-dokumentacija*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'kandidatSubmenu')">
                        <i class="fas fa-user"></i>
                        <span>Кандидати</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="kandidatSubmenu">
                        <li><a class="{{ Request::is('kandidat/create') ? 'active' : '' }}" href="{{ url('kandidat/create') }}">Додавање</a></li>
                        <li><a class="{{ Request::is('kandidat') ? 'active' : '' }}" href="{{ url('kandidat?studijskiProgramId=1') }}">Преглед</a></li>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <li><a class="{{ Request::is('kandidat/documents/incomplete') ? 'active' : '' }}" href="{{ route('kandidat.documents.incomplete') }}">Преглед документације</a></li>
                        @endif
                    </ul>
                </li>
                
                <li class="{{ Request::is('*master*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'masterSubmenu')">
                        <i class="fas fa-book"></i>
                        <span>Мастер кандидати</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="masterSubmenu">
                        <li><a class="{{ Request::is('master/create') ? 'active' : '' }}" href="{{ url('master/create') }}">Додавање</a></li>
                        <li><a class="{{ Request::is('master') ? 'active' : '' }}" href="{{ url('master') }}">Преглед</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*student*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'studentiSubmenu')">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Активни студенти</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="studentiSubmenu">
                        <li><a class="{{ Request::is('student/index/1') ? 'active' : '' }}" href="{{ url('student/index/1?godina=1&studijskiProgramId=1') }}">Основне студије</a></li>
                        <li><a class="{{ Request::is('student/index/2') ? 'active' : '' }}" href="{{ url('student/index/2?studijskiProgramId=4') }}">Мастер студије</a></li>
                        <li><a class="{{ Request::is('student/zamrznuti') ? 'active' : '' }}" href="{{ url('student/zamrznuti') }}">Статус мировања</a></li>
                        <li><a class="{{ Request::is('student/ispisani') ? 'active' : '' }}" href="{{ url('student/ispisani') }}">Исписани студенти</a></li>
                        <li><a class="{{ Request::is('student/diplomirani') ? 'active' : '' }}" href="{{ url('student/diplomirani?tipStudijaId=1&studijskiProgramId=1') }}">Дипломирани</a></li>
                        <li><a class="{{ Request::is('izvestaji/spiskoviStudenti') ? 'active' : '' }}" href="{{ url('/izvestaji/spiskoviStudenti') }}">Извештаји</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*kalendar*') || Request::is('*predmeti*') || Request::is('*zapisnik*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'ispitiSubmenu')">
                        <i class="fas fa-calendar"></i>
                        <span>Испити</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="ispitiSubmenu">
                        <li><a class="{{ Request::is('kalendar*') ? 'active' : '' }}" href="{{ url('/kalendar/') }}">Календар</a></li>
                        <li><a class="{{ Request::is('predmeti*') || Request::is('prijava/*') ? 'active' : '' }}" href="{{ url('/predmeti/') }}">Пријава испита</a></li>
                        <li><a class="{{ Request::is('zapisnik*') ? 'active' : '' }}" href="{{ url('/zapisnik/') }}">Записник</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*tipStudija*') || Request::is('*studijskiProgram*') || Request::is('*godinaStudija*') || Request::is('*statusStudiranja*') || Request::is('*semestar*') || Request::is('*ispitniRok*') || Request::is('*oblikNastave*') || Request::is('*tipPredmeta*') || Request::is('*bodovanje*') || Request::is('*statusKandidata*') || Request::is('*statusIspita*') || Request::is('*statusProfesora*') || Request::is('*tipPrijave*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'adminSifarniciSubmenu')">
                        <i class="fas fa-cogs"></i>
                        <span>Админ шифарници</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="adminSifarniciSubmenu">
                        <li><a class="{{ Request::is('tipStudija*') ? 'active' : '' }}" href="{{ url('/tipStudija') }}">Тип студија</a></li>
                        <li><a class="{{ Request::is('studijskiProgram*') ? 'active' : '' }}" href="{{ url('/studijskiProgram') }}">Студијски програм</a></li>
                        <li><a class="{{ Request::is('godinaStudija*') ? 'active' : '' }}" href="{{ url('/godinaStudija') }}">Година студија</a></li>
                        <li><a class="{{ Request::is('statusStudiranja*') ? 'active' : '' }}" href="{{ url('statusStudiranja') }}">Статус студирања</a></li>
                        <li><a class="{{ Request::is('semestar*') ? 'active' : '' }}" href="{{ url('semestar') }}">Семестар</a></li>
                        <li><a class="{{ Request::is('ispitniRok*') ? 'active' : '' }}" href="{{ url('ispitniRok') }}">Испитни рок</a></li>
                        <li><a class="{{ Request::is('oblikNastave*') ? 'active' : '' }}" href="{{ url('oblikNastave') }}">Облик наставе</a></li>
                        <li><a class="{{ Request::is('tipPredmeta*') ? 'active' : '' }}" href="{{ url('tipPredmeta') }}">Тип предмета</a></li>
                        <li><a class="{{ Request::is('bodovanje*') ? 'active' : '' }}" href="{{ url('bodovanje') }}">Бодовање</a></li>
                        <li><a class="{{ Request::is('statusKandidata*') ? 'active' : '' }}" href="{{ url('statusKandidata') }}">Статус године</a></li>
                        <li><a class="{{ Request::is('statusIspita*') ? 'active' : '' }}" href="{{ url('statusIspita') }}">Статус испита</a></li>
                        <li><a class="{{ Request::is('statusProfesora*') ? 'active' : '' }}" href="{{ url('statusProfesora') }}">Статус професора</a></li>
                        <li><a class="{{ Request::is('tipPrijave*') ? 'active' : '' }}" href="{{ url('tipPrijave') }}">Тип пријаве</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('sport*') || Request::is('predmet') || Request::is('predmet/*') || Request::is('profesor*') || Request::is('krsnaSlava*') || Request::is('region*') || Request::is('opstina*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'sifarniciSubmenu')">
                        <i class="fas fa-list"></i>
                        <span>Шифарници</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="sifarniciSubmenu">
                        <li><a class="{{ Request::is('sport*') ? 'active' : '' }}" href="{{ url('sport') }}">Спортови</a></li>
                        <li><a class="{{ Request::is('predmet*') ? 'active' : '' }}" href="{{ url('predmet') }}">Предмет</a></li>
                        <li><a class="{{ Request::is('profesor*') ? 'active' : '' }}" href="{{ url('profesor') }}">Професор</a></li>
                        <li><a class="{{ Request::is('krsnaSlava*') ? 'active' : '' }}" href="{{ url('krsnaSlava') }}">Крсна слава</a></li>
                        <li><a class="{{ Request::is('region*') ? 'active' : '' }}" href="{{ url('region') }}">Регион</a></li>
                        <li><a class="{{ Request::is('opstina*') ? 'active' : '' }}" href="{{ url('opstina') }}">Општина</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*prisustvo*') || Request::is('*aktivnost*') || Request::is('*raspored*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'nastavaSubmenu')">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Настава</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="nastavaSubmenu">
                        <li><a class="{{ Request::is('raspored*') ? 'active' : '' }}" href="{{ url('/raspored') }}">Распоред</a></li>
                        <li><a class="{{ Request::is('prisustvo*') ? 'active' : '' }}" href="{{ url('/prisustvo') }}">Присуство</a></li>
                        <li><a class="{{ Request::is('aktivnost*') ? 'active' : '' }}" href="{{ url('/aktivnost') }}">Активности</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*obavestenja*') || Request::is('*moja-obavestenja*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'komunikacijaSubmenu')">
                        <i class="fas fa-bullhorn"></i>
                        <span>Комуникација</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="komunikacijaSubmenu">
                        <li><a class="{{ Request::is('obavestenja*') ? 'active' : '' }}" href="{{ url('/obavestenja') }}">Обавештења</a></li>
                        <li><a class="{{ Request::is('moja-obavestenja*') ? 'active' : '' }}" href="{{ url('/moja-obavestenja') }}">Моја обавештења</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*chatbot*') || Request::is('*prediction*') || Request::is('*dashboard*') ? 'submenu-parent-active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'analitikaSubmenu')">
                        <i class="fas fa-chart-pie"></i>
                        <span>Аналитика и AI</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="analitikaSubmenu">
                        <li><a class="{{ Request::is('dashboard*') ? 'active' : '' }}" href="{{ url('/dashboard') }}">Аналитика</a></li>
                        <li><a class="{{ Request::is('chatbot*') ? 'active' : '' }}" href="{{ url('/chatbot') }}">AI Чатбот</a></li>
                        <li><a class="{{ Request::is('prediction*') ? 'active' : '' }}" href="{{ url('/prediction') }}">AI Предикција</a></li>
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
                    <x-button variant="outline" size="sm" href="{{ url('/pretraga') }}">
                        <i class="fas fa-search"></i>
                    </x-button>
                    
                    <x-button variant="outline" size="sm" type="button" id="themeToggle" title="Тема">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </x-button>
                    
                    @if(!Auth::guest())
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <x-button variant="outline" size="sm" type="button" @click="open = !open" aria-expanded="false">
                                <i class="fas fa-user-circle mr-2"></i>
                                {{ Auth::user()->name }}
                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </x-button>
                            <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-secondary-200">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50" href="#" onclick="event.preventDefault(); this.closest('form').submit();"><i class="fas fa-sign-out-alt mr-2"></i>Одјава</a>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-3">
                <h1 class="text-2xl font-semibold text-secondary-800 mb-4 pb-4 border-b border-secondary-200">@yield('page_heading')</h1>
                
                @yield('section')
            </main>
        </div>
    </div>

    @include('partials.toast')
    @include('partials.ajax-loader')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @stack('scripts')
    
    <script>
        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            var submenu = document.getElementById(submenuId);
            var parentLi = submenu.parentElement;
            submenu.classList.toggle('show');
            parentLi.classList.toggle('open');
        }

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
