@extends('layouts.header')

@section('body')

    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target=".navbar-collapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <img class="float-start" src="{{ asset('images/logo_fzs.png') }}" height="30px" style="margin: 10px 10px 10px 10px">
                    <a class="navbar-brand" href="{{ url('') }}"> Факултет за спорт</a>
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="{{ url('/pretraga') }}"><span class="fas fa-search"></span><b> Претрага</b></a></li>
                </ul>
                <!-- /.navbar-header -->
                <ul class="nav navbar-nav navbar-right" style="margin-right: 5%">
                    @if (Auth::guest())
                        <li><a href="/login">Пријава</a></li>
                        <li><a href="/register">Регистрација</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">Активни
                                корисник: {{ Auth::user()->name }} <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/logout') }}">Одјава</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>

                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar - desktop -->
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse navbar-collapse">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column" id="side-menu">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="fas fa-user"></span>&nbsp;Кандидати
                                </a>
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('kandidat/create') }}">Додавање</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('kandidat?studijskiProgramId=1') }}">Преглед</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="fas fa-book"></span>&nbsp;Мастер кандидати
                                </a>
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('master/create') }}">Додавање</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('master') }}">Преглед</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="fas fa-graduation-cap"></span>&nbsp;Активни студенти
                                </a>
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('student/index/1?godina=1&studijskiProgramId=1') }}">Основне студије</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('student/index/2?studijskiProgramId=4') }}">Мастер студије</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('student/zamrznuti') }}">Статус мировања</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('student/ispisani') }}">Исписани студенти</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('student/diplomirani?tipStudijaId=1&studijskiProgramId=1') }}">Дипломирани студенти</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/izvestaji/spiskoviStudenti') }}">Извештаји</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="fas fa-calendar"></span>&nbsp;Испити
                                </a>
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/kalendar/') }}">Календар</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/predmeti/') }}">Пријава испита</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/zapisnik/') }}">Записник о полагању испита</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="fas fa-cog"></span>&nbsp;Админ шифарници
                                </a>
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/tipStudija') }}">Тип студија</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/studijskiProgram') }}">Студијски програм</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/prilozenaDokumenta') }}">Приложена документа</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/godinaStudija') }}">Година студија</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('statusStudiranja') }}">Статус студирања</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('semestar') }}">Семестар</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('ispitniRok') }}">Испитни рок</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('oblikNastave') }}">Облик наставе</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('tipPredmeta') }}">Тип предмета</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('bodovanje') }}">Бодовање</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('statusKandidata') }}">Статус године</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('statusIspita') }}">Статус испита</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('statusProfesora') }}">Статус професора</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('tipPrijave') }}">Тип пријаве</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="fas fa-table"></span>&nbsp;Шифарници
                                </a>
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('sport') }}">Спортови</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('predmet') }}">Предмет</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('krsnaSlava') }}">Крсна слава</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('region') }}">Регион</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('opstina') }}">Општина</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('profesor') }}">Професор</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span class="fas fa-plus-circle"></span>&nbsp;Нови модули
                                </a>
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/prisustvo') }}">Присуство</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/aktivnost') }}">Активности</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/raspored') }}">Распоред</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/obavestenja') }}">Обавештења</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/dashboard') }}">Аналитика</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="row mt-4">
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
