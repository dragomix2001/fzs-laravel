@extends('layouts.header')

@section('body')

    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark" role="navigation" style="margin-bottom: 0; min-height: 50px;">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <a href="{{ url('') }}">
                        <img src="{{ asset('images/logo_fzs.png') }}" height="35" class="d-inline-block align-text-top">
                    </a>
                    <a class="navbar-brand ms-2" href="{{ url('') }}">Факултет за спорт</a>
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="{{ url('/pretraga') }}"><i class="fas fa-search"></i> <b>Претрага</b></a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right me-3">
                    @if (Auth::guest())
                        <li><a href="/login">Пријава</a></li>
                        <li><a href="/register">Регистрација</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">Активни корисник: {{ Auth::user()->name }} <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt"></i> Одјава</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav id="sidebarMenu" class="col-md-2 collapse d-md-block bg-light sidebar" style="min-height: calc(100vh - 50px); width: 220px; position: fixed; left: 0; top: 50px; overflow-y: auto; border-right: 1px solid #ddd;">
                    <div class="position-sticky pt-2">
                        <ul class="nav flex-column" id="side-menu">
                            <li class="nav-item">
                                <a class="nav-link" href="#submenuKandidati" data-bs-toggle="collapse">
                                    <i class="fas fa-user"></i>&nbsp;Кандидати<span class="fas fa-angle-down float-end"></span>
                                </a>
                                <ul class="nav collapse" id="submenuKandidati" data-bs-parent="#side-menu">
                                    <li class="nav-item"><a class="nav-link" href="{{ url('kandidat/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('kandidat?studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#submenuMaster" data-bs-toggle="collapse">
                                    <i class="fas fa-book"></i>&nbsp;Мастер кандидати<span class="fas fa-angle-down float-end"></span>
                                </a>
                                <ul class="nav collapse" id="submenuMaster" data-bs-parent="#side-menu">
                                    <li class="nav-item"><a class="nav-link" href="{{ url('master/create') }}">&nbsp;&nbsp;&nbsp;Додавање</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('master') }}">&nbsp;&nbsp;&nbsp;Преглед</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#submenuStudenti" data-bs-toggle="collapse">
                                    <i class="fas fa-graduation-cap"></i>&nbsp;Активни студенти<span class="fas fa-angle-down float-end"></span>
                                </a>
                                <ul class="nav collapse" id="submenuStudenti" data-bs-parent="#side-menu">
                                    <li class="nav-item"><a class="nav-link" href="{{ url('student/index/1?godina=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Основне студије</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('student/index/2?studijskiProgramId=4') }}">&nbsp;&nbsp;&nbsp;Мастер студије</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('student/zamrznuti') }}">&nbsp;&nbsp;&nbsp;Статус мировања</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('student/ispisani') }}">&nbsp;&nbsp;&nbsp;Исписани студенти</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('student/diplomirani?tipStudijaId=1&studijskiProgramId=1') }}">&nbsp;&nbsp;&nbsp;Дипломирани студенти</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/izvestaji/spiskoviStudenti') }}">&nbsp;&nbsp;&nbsp;Извештаји</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#submenuIspiti" data-bs-toggle="collapse">
                                    <i class="fas fa-calendar"></i>&nbsp;Испити<span class="fas fa-angle-down float-end"></span>
                                </a>
                                <ul class="nav collapse" id="submenuIspiti" data-bs-parent="#side-menu">
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/kalendar/') }}">&nbsp;&nbsp;&nbsp;Календар</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/predmeti/') }}">&nbsp;&nbsp;&nbsp;Пријава испита</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/zapisnik/') }}">&nbsp;&nbsp;&nbsp;Записник о полагању испита</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#submenuSifarniciAdmin" data-bs-toggle="collapse">
                                    <i class="fas fa-cog"></i>&nbsp;Админ шифарници<span class="fas fa-angle-down float-end"></span>
                                </a>
                                <ul class="nav collapse" id="submenuSifarniciAdmin" data-bs-parent="#side-menu">
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/tipStudija') }}">&nbsp;&nbsp;&nbsp;Тип студија</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/studijskiProgram') }}">&nbsp;&nbsp;&nbsp;Студијски програм</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/godinaStudija') }}">&nbsp;&nbsp;&nbsp;Година студија</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('statusStudiranja') }}">&nbsp;&nbsp;&nbsp;Статус студирања</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('semestar') }}">&nbsp;&nbsp;&nbsp;Семестар</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('ispitniRok') }}">&nbsp;&nbsp;&nbsp;Испитни рок</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('oblikNastave') }}">&nbsp;&nbsp;&nbsp;Облик наставe</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('tipPredmeta') }}">&nbsp;&nbsp;&nbsp;Тип предмета</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('bodovanje') }}">&nbsp;&nbsp;&nbsp;Бодовање</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('statusKandidata') }}">&nbsp;&nbsp;&nbsp;Статус године</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('statusIspita') }}">&nbsp;&nbsp;&nbsp;Статус испита</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('statusProfesora') }}">&nbsp;&nbsp;&nbsp;Статус професора</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('tipPrijave') }}">&nbsp;&nbsp;&nbsp;Тип пријаве</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#submenuSifarnici" data-bs-toggle="collapse">
                                    <i class="fas fa-table"></i>&nbsp;Шифарници<span class="fas fa-angle-down float-end"></span>
                                </a>
                                <ul class="nav collapse" id="submenuSifarnici" data-bs-parent="#side-menu">
                                    <li class="nav-item"><a class="nav-link" href="{{ url('sport') }}">&nbsp;&nbsp;&nbsp;Спортови</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('predmet') }}">&nbsp;&nbsp;&nbsp;Предмет</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('profesor') }}">&nbsp;&nbsp;&nbsp;Професор</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('krsnaSlava') }}">&nbsp;&nbsp;&nbsp;Крсна слава</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('region') }}">&nbsp;&nbsp;&nbsp;Регион</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('opstina') }}">&nbsp;&nbsp;&nbsp;Општина</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#submenuNovi" data-bs-toggle="collapse">
                                    <i class="fas fa-plus-circle"></i>&nbsp;Нови модули<span class="fas fa-angle-down float-end"></span>
                                </a>
                                <ul class="nav collapse" id="submenuNovi" data-bs-parent="#side-menu">
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/prisustvo') }}">&nbsp;&nbsp;&nbsp;Присуство</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/aktivnost') }}">&nbsp;&nbsp;&nbsp;Активности</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/raspored') }}">&nbsp;&nbsp;&nbsp;Распоред</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/obavestenja') }}">&nbsp;&nbsp;&nbsp;Обавештења</a></li>
                                    <li class="nav-item"><a class="nav-link" href="{{ url('/dashboard') }}">&nbsp;&nbsp;&nbsp;Аналитика</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main content -->
                <main class="col-md-10 ms-sm-auto" style="margin-left: 220px; padding: 20px;">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="page-header">@yield('page_heading')</h2>
                        </div>
                    </div>
                    <div class="row">
                        @yield('section')
                    </div>
                </main>
            </div>
        </div>
    </div>

@stop
