<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Факултет за спорт')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #0d6efd;
            --header-height: 56px;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar - svetla pozadina */
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
        
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover {
            background: #e9ecef;
            color: #000;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        
        .sidebar .nav-link .arrow {
            margin-left: auto;
            font-size: 12px;
            transition: transform 0.2s;
        }
        
        .sidebar .nav-second-level {
            background: #fff;
            list-style: none;
            padding: 0;
            margin: 0;
            display: none;
            border-bottom: 1px solid #e9ecef;
        }
        
        .sidebar .nav-second-level.show {
            display: block;
        }
        
        .sidebar .nav-second-level .nav-link {
            padding: 10px 20px 10px 55px;
            font-size: 14px;
            color: #555;
        }
        
        .sidebar .nav-item.active > .nav-link {
            background: var(--primary-color);
            color: #fff;
        }
        
        .sidebar .nav-item.active .nav-second-level .nav-link.active {
            background: var(--primary-color);
            color: #fff;
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
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <ul class="nav flex-column" id="side-menu">
                <li class="nav-item {{ Request::is('*kandidat*') ? 'active' : '' }}">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#kandidatSubmenu">
                        <i class="fas fa-user"></i>
                        <span>Кандидати</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="nav collapse" id="kandidatSubmenu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('kandidat/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('kandidat?studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*master*') ? 'active' : '' }}">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#masterSubmenu">
                        <i class="fas fa-book"></i>
                        <span>Мастер кандидати</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="nav collapse" id="masterSubmenu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('master/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('master') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*student*') ? 'active' : '' }}">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#studentiSubmenu">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Активни студенти</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="nav collapse" id="studentiSubmenu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/index/1?godina=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Основне студије</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/index/2?studijskiProgramId=4') }}">&nbsp;&nbsp;&nbsp;Мастер студије</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/zamrznuti') }}">&nbsp;&nbsp;&nbsp;Статус мировања</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/ispisani') }}">&nbsp;&nbsp;&nbsp;Исписани студенти</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('student/diplomirani?tipStudijaId=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Дипломирани</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/izvestaji/spiskoviStudenti') }}">&nbsp;&nbsp;&nbsp;Извештаји</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*kalendar*') || Request::is('*predmeti*') || Request::is('*zapisnik*') ? 'active' : '' }}">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#ispitiSubmenu">
                        <i class="fas fa-calendar"></i>
                        <span>Испити</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="nav collapse" id="ispitiSubmenu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('/kalendar/') }}">&nbsp;&nbsp;&nbsp;Календар</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/predmeti/') }}">&nbsp;&nbsp;&nbsp;Пријава испита</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/zapisnik/') }}">&nbsp;&nbsp;&nbsp;Записник</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*tipStudija*') || Request::is('*studijskiProgram*') ? 'active' : '' }}">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#adminSifarniciSubmenu">
                        <i class="fas fa-cogs"></i>
                        <span>Админ шифарници</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="nav collapse" id="adminSifarniciSubmenu">
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
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#sifarniciSubmenu">
                        <i class="fas fa-list"></i>
                        <span>Шифарници</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="nav collapse" id="sifarniciSubmenu">
                        <li class="nav-item"><a class="nav-link" href="{{ url('sport') }}">&nbsp;&nbsp;&nbsp;Спортови</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('predmet') }}">&nbsp;&nbsp;&nbsp;Предмет</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('profesor') }}">&nbsp;&nbsp;&nbsp;Професор</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('krsnaSlava') }}">&nbsp;&nbsp;&nbsp;Крсна слава</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('region') }}">&nbsp;&nbsp;&nbsp;Регион</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('opstina') }}">&nbsp;&nbsp;&nbsp;Општина</a></li>
                    </ul>
                </li>
                
                <li class="nav-item {{ Request::is('*prisustvo*') || Request::is('*aktivnost*') || Request::is('*raspored*') || Request::is('*obavestenja*') || Request::is('*dashboard*') ? 'active' : '' }}">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#noviModuliSubmenu">
                        <i class="fas fa-plus-circle"></i>
                        <span>Нови модули</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="nav collapse" id="noviModuliSubmenu">
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
                    <a href="{{ url('') }}" class="logo-link">
                        <img src="{{ asset('images/logo_fzs.png') }}" height="38">
                        <span>Факултет за спорт</span>
                    </a>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url('/pretraga') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-search"></i>
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
            <main class="p-3">
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
        
        // Arrow rotation on collapse
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                var arrow = this.querySelector('.arrow');
                if (arrow) {
                    arrow.classList.toggle('fa-chevron-down');
                    arrow.classList.toggle('fa-chevron-up');
                }
            });
        });
    </script>
</body>
</html>
