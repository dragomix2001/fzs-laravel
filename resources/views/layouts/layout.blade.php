<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Факултет за спорт')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-resources/1.0.0/responsive.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        :root {
            --sidebar-width: 240px;
            --primary-color: #0d6efd;
            --sidebar-bg: #212529;
            --sidebar-text: #c9c9c9;
            --sidebar-hover: #3a3f44;
            --header-height: 56px;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        
        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 12px 20px;
            border-bottom: 1px solid #323539;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            font-size: 14px;
        }
        
        .sidebar .nav-second-level {
            background: #1a1d20;
            list-style: none;
            padding: 0;
            margin: 0;
            display: none;
        }
        
        .sidebar .nav-second-level.show {
            display: block;
        }
        
        .sidebar .nav-second-level .nav-link {
            padding: 10px 20px 10px 50px;
            font-size: 13px;
            border-bottom: 1px solid #2c3035;
        }
        
        .sidebar .nav-item.active > .nav-link {
            background: var(--primary-color);
            color: #fff;
        }
        
        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 20px;
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
        }
        
        .top-header .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #212529;
            text-decoration: none;
        }
        
        .top-header .navbar-brand img {
            height: 35px;
        }
        
        .top-header .navbar-brand span {
            font-weight: 600;
            font-size: 18px;
        }
        
        /* Page header */
        .page-header {
            margin: 20px 0;
            padding-bottom: 10px;
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
        }
        
        /* Cards */
        .card-custom {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-custom .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 20px;
            font-weight: 600;
        }
        
        .card-custom .card-body {
            padding: 20px;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
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
        
        /* Arrow animation */
        .nav-link .fa-chevron-down {
            transition: transform 0.2s;
            font-size: 10px;
        }
        
        .nav-link[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="p-3 border-bottom border-secondary">
                <a href="{{ url('') }}" class="d-flex align-items-center text-white text-decoration-none">
                    <img src="{{ asset('images/logo_fzs.png') }}" height="35" class="me-2">
                    <span>Факултет за спорт</span>
                </a>
            </div>
            
            <ul class="nav flex-column" id="side-menu">
                <li class="nav-item {{ Request::is('*kandidat*') ? 'active' : '' }}">
                    <a class="nav-link" href="#kandidatSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-user"></i>
                        <span>Кандидати</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="nav collapse" id="kandidatSubmenu" data-bs-parent="#side-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('kandidat/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('kandidat?studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*master*') ? 'active' : '' }}">
                    <a class="nav-link" href="#masterSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-book"></i>
                        <span>Мастер кандидати</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="nav collapse" id="masterSubmenu" data-bs-parent="#side-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('master/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('master') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*student*') ? 'active' : '' }}">
                    <a class="nav-link" href="#studentiSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Активни студенти</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="nav collapse" id="studentiSubmenu" data-bs-parent="#side-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/index/1?godina=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Основне студије</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/index/2?studijskiProgramId=4') }}">&nbsp;&nbsp;&nbsp;Мастер студије</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/zamrznuti') }}">&nbsp;&nbsp;&nbsp;Статус мировања</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/ispisani') }}">&nbsp;&nbsp;&nbsp;Исписани студенти</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/diplomirani?tipStudijaId=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Дипломирани</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/izvestaji/spiskoviStudenti') }}">&nbsp;&nbsp;&nbsp;Извештаји</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*kalendar*') || Request::is('*predmeti*') || Request::is('*zapisnik*') ? 'active' : '' }}">
                    <a class="nav-link" href="#ispitiSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-calendar"></i>
                        <span>Испити</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="nav collapse" id="ispitiSubmenu" data-bs-parent="#side-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('/kalendar/') }}">&nbsp;&nbsp;&nbsp;Календар</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/predmeti/') }}">&nbsp;&nbsp;&nbsp;Пријава испита</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/zapisnik/') }}">&nbsp;&nbsp;&nbsp;Записник</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*tipStudija*') || Request::is('*studijskiProgram*') ? 'active' : '' }}">
                    <a class="nav-link" href="#adminSifarniciSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-cogs"></i>
                        <span>Админ шифарници</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="nav collapse" id="adminSifarniciSubmenu" data-bs-parent="#side-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('/tipStudija') }}">&nbsp;&nbsp;&nbsp;Тип студија</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/studijskiProgram') }}">&nbsp;&nbsp;&nbsp;Студијски програм</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/godinaStudija') }}">&nbsp;&nbsp;&nbsp;Година студија</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('statusStudiranja') }}">&nbsp;&nbsp;&nbsp;Статус студирања</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('semestar') }}">&nbsp;&nbsp;&nbsp;Семестар</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('ispitniRok') }}">&nbsp;&nbsp;&nbsp;Испитни рок</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('oblikNastave') }}">&nbsp;&nbsp;&nbsp;Облик наставe</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('tipPredmeta') }}">&nbsp;&nbsp;&nbsp;Тип предмета</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('bodovanje') }}">&nbsp;&nbsp;&nbsp;Бодовање</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('statusKandidata') }}">&nbsp;&nbsp;&nbsp;Статус годыдине</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('statusIspita') }}">&nbsp;&nbsp;&nbsp;Статус испита</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('statusProfesora') }}">&nbsp;&nbsp;&nbsp;Статус професора</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('tipPrijave') }}">&nbsp;&nbsp;&nbsp;Тип пријаве</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*sport*') || Request::is('*predmet*') || Request::is('*profesor*') ? 'active' : '' }}">
                    <a class="nav-link" href="#sifarniciSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-list"></i>
                        <span>Шифарници</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="nav collapse" id="sifarniciSubmenu" data-bs-parent="#side-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('sport') }}">&nbsp;&nbsp;&nbsp;Спортови</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('predmet') }}">&nbsp;&nbsp;&nbsp;Предмет</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('profesor') }}">&nbsp;&nbsp;&nbsp;Професор</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('krsnaSlava') }}">&nbsp;&nbsp;&nbsp;Крсна слава</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('region') }}">&nbsp;&nbsp;&nbsp;Регион</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('opstina') }}">&nbsp;&nbsp;&nbsp;Општина</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*prisustvo*') || Request::is('*aktivnost*') || Request::is('*raspored*') || Request::is('*obavestenja*') || Request::is('*dashboard*') ? 'active' : '' }}">
                    <a class="nav-link" href="#noviModuliSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-plus-circle"></i>
                        <span>Нови модули</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="nav collapse" id="noviModuliSubmenu" data-bs-parent="#side-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('/prisustvo') }}">&nbsp;&nbsp;&nbsp;Присуство</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/aktivnost') }}">&nbsp;&nbsp;&nbsp;Активности</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/raspored') }}">&nbsp;&nbsp;&nbsp;Распоред</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/obavestenja') }}">&nbsp;&nbsp;&nbsp;Обавештења</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/dashboard') }}">&nbsp;&nbsp;&nbsp;Аналитика</a></li>
                    </ul>
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
                    <a href="{{ url('') }}" class="navbar-brand">
                        <img src="{{ asset('images/logo_fzs.png') }}" height="35">
                        <span>Факултет за спорт</span>
                    </a>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url('/pretraga') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-search"></i> Претрага
                    </a>
                    
                    @if(!Auth::guest())
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Одјава</a></li>
                            </ul>
                        </div>
                    @endif
                </div>
            </header>
            
            <!-- Page Content -->
            <main>
                <h1 class="page-header">@yield('page_heading')</h1>
                
                @yield('section')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });
        
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });
        
        // Keep submenu open on page load if active
        document.querySelectorAll('.nav-item.active .nav-collapse').forEach(function(el) {
            el.classList.add('show');
        });
    </script>
</body>
</html>
