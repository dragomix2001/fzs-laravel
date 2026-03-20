<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Факултет за спорт')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 56px;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: #f8f9fa;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
            border-right: 1px solid #dee2e6;
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
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #e9ecef;
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
    <div class="wrapper">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
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
                
                <li class="{{ Request::is('*prisustvo*') || Request::is('*aktivnost*') || Request::is('*raspored*') || Request::is('*obavestenja*') || Request::is('*dashboard*') ? 'active' : '' }}">
                    <a href="#" onclick="toggleSubmenu(event, 'noviModuliSubmenu')">
                        <i class="fas fa-plus-circle"></i>
                        <span>Нови модули</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu" id="noviModuliSubmenu">
                        <li><a href="{{ url('/prisustvo') }}">&nbsp;&nbsp;&nbsp;Присуство</a></li>
                        <li><a href="{{ url('/aktivnost') }}">&nbsp;&nbsp;&nbsp;Активности</a></li>
                        <li><a href="{{ url('/raspored') }}">&nbsp;&nbsp;&nbsp;Распоред</a></li>
                        <li><a href="{{ url('/obavestenja') }}">&nbsp;&nbsp;&nbsp;Обавештења</a></li>
                        <li><a href="{{ url('/dashboard') }}">&nbsp;&nbsp;&nbsp;Аналитика</a></li>
                    </ul>
                </li>
                
                <li class="{{ Request::is('*chatbot*') ? 'active' : '' }}">
                    <a href="{{ url('/chatbot') }}">
                        <i class="fas fa-robot"></i>
                        <span>AI Chatbot</span>
                    </a>
                </li>
                
                <li class="{{ Request::is('*prediction*') ? 'active' : '' }}">
                    <a href="{{ url('/prediction') }}">
                        <i class="fas fa-chart-line"></i>
                        <span>AI Предикција</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="d-flex align-items-center">
                    <button class="mobile-toggle me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="{{ url('') }}" class="logo-link">
                        <img src="{{ asset('images/logo_fzs.png') }}" height="38" loading="lazy">
                        <span>Факултет за спорт</span>
                    </a>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url('/pretraga') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-search"></i>
                    </a>
                    
                    @if(!Auth::guest())
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-2"></i>
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Одјава</a></li>
                            </ul>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
        
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });
        
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });
    </script>
</body>
</html>
