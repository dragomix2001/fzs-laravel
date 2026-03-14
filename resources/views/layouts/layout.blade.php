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
                    <a href="{{ url('') }}">
                        <img src="{{ asset('images/logo_fzs.png') }}" height="40" style="margin: 5px 10px 5px 10px">
                    </a>
                    <a class="navbar-brand" href="{{ url('') }}"> Факултет за спорт</a>
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="{{ url('/pretraga') }}"><i class="fas fa-search"></i> <b>Претрага</b></a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right" style="margin-right: 5%">
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

        <div class="navbar-default sidebar" role="navigation" style="background: #f8f9fa; min-height: 100vh; width: 250px; position: fixed; left: 0; top: 50px; overflow-y: auto;">
            <div class="sidebar-nav">
                <ul class="nav" id="side-menu" style="padding: 0;">
                    <li class="{{ Request::is('*kandidat*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-user"></i>&nbsp;Кандидати<span class="fas fa-chevron-down float-end"></span></a>
                        <ul class="nav nav-second-level">
                            <li {{ (Request::is('*kandidat/create') ? 'class="active"' : '') }}>
                                <a href="{{ url('kandidat/create') }}">Додавање</a>
                            </li>
                            <li {{ (Request::is('*kandidat?*') ? 'class="active"' : '') }}>
                                <a href="{{ url('kandidat?studijskiProgramId=1') }}">Преглед</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{ Request::is('*master*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-book"></i>&nbsp;Мастер кандидати<span class="fas fa-chevron-down float-end"></span></a>
                        <ul class="nav nav-second-level">
                            <li {{ (Request::is('*master/create') ? 'class="active"' : '') }}>
                                <a href="{{ url('master/create') }}">Додавање</a>
                            </li>
                            <li {{ (Request::is('*master') ? 'class="active"' : '') }}>
                                <a href="{{ url('master') }}">Преглед</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{ Request::is('*student*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-graduation-cap"></i>&nbsp;Активни студенти<span class="fas fa-chevron-down float-end"></span></a>
                        <ul class="nav nav-second-level">
                            <li {{ (Request::is('*student/index/1*') ? 'class="active"' : '') }}>
                                <a href="{{ url('student/index/1?godina=1&studijskiProgramId=1') }}">Основне студије</a>
                            </li>
                            <li {{ (Request::is('*student/index/2*') ? 'class="active"' : '') }}>
                                <a href="{{ url('student/index/2?studijskiProgramId=4') }}">Мастер студије</a>
                            </li>
                            <li {{ (Request::is('*student/zamrznuti*') ? 'class="active"' : '') }}>
                                <a href="{{ url('student/zamrznuti') }}">Статус мировања</a>
                            </li>
                            <li {{ (Request::is('*student/ispisani*') ? 'class="active"' : '') }}>
                                <a href="{{ url('student/ispisani') }}">Исписани студенти</a>
                            </li>
                            <li {{ (Request::is('*student/diplomirani*') ? 'class="active"' : '') }}>
                                <a href="{{ url('student/diplomirani?tipStudijaId=1&studijskiProgramId=1') }}">Дипломирани студенти</a>
                            </li>
                            <li {{ (Request::is('*izvestaji/spiskoviStudenti*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/izvestaji/spiskoviStudenti') }}">Извештаји</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{ Request::is('*kalendar*') || Request::is('*predmeti*') || Request::is('*zapisnik*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-calendar"></i>&nbsp;Испити<span class="fas fa-chevron-down float-end"></span></a>
                        <ul class="nav nav-second-level">
                            <li {{ (Request::is('*kalendar*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/kalendar/') }}">Календар</a>
                            </li>
                            <li {{ (Request::is('*predmeti*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/predmeti/') }}">Пријава испита</a>
                            </li>
                            <li {{ (Request::is('*zapisnik*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/zapisnik/') }}">Записник о полагању испита</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{ Request::is('*tipStudija*') || Request::is('*studijskiProgram*') || Request::is('*sifarnici*') || Request::is('*sifarniciAdmin*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-cog"></i>&nbsp;Админ шифарници<span class="fas fa-chevron-down float-end"></span></a>
                        <ul class="nav nav-second-level">
                            <li {{ (Request::is('*tipStudija*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/tipStudija') }}">Тип студија</a>
                            </li>
                            <li {{ (Request::is('*studijskiProgram*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/studijskiProgram') }}">Студијски програм</a>
                            </li>
                            <li {{ (Request::is('*godinaStudija*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/godinaStudija') }}">Година студија</a>
                            </li>
                            <li {{ (Request::is('*statusStudiranja*') ? 'class="active"' : '') }}>
                                <a href="{{ url('statusStudiranja') }}">Статус студирања</a>
                            </li>
                            <li {{ (Request::is('*semestar*') ? 'class="active"' : '') }}>
                                <a href="{{ url('semestar') }}">Семестар</a>
                            </li>
                            <li {{ (Request::is('*ispitniRok*') ? 'class="active"' : '') }}>
                                <a href="{{ url('ispitniRok') }}">Испитни рок</a>
                            </li>
                            <li {{ (Request::is('*oblikNastave*') ? 'class="active"' : '') }}>
                                <a href="{{ url('oblikNastave') }}">Облик наставe</a>
                            </li>
                            <li {{ (Request::is('*tipPredmeta*') ? 'class="active"' : '') }}>
                                <a href="{{ url('tipPredmeta') }}">Тип предмета</a>
                            </li>
                            <li {{ (Request::is('*bodovanje*') ? 'class="active"' : '') }}>
                                <a href="{{ url('bodovanje') }}">Бодовање</a>
                            </li>
                            <li {{ (Request::is('*statusKandidata*') ? 'class="active"' : '') }}>
                                <a href="{{ url('statusKandidata') }}">Статус године</a>
                            </li>
                            <li {{ (Request::is('*statusIspita*') ? 'class="active"' : '') }}>
                                <a href="{{ url('statusIspita') }}">Статус испита</a>
                            </li>
                            <li {{ (Request::is('*statusProfesora*') ? 'class="active"' : '') }}>
                                <a href="{{ url('statusProfesora') }}">Статус професора</a>
                            </li>
                            <li {{ (Request::is('*tipPrijave*') ? 'class="active"' : '') }}>
                                <a href="{{ url('tipPrijave') }}">Тип пријаве</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{ Request::is('*sport*') || Request::is('*predmet*') || Request::is('*profesor*') || Request::is('*krsnaSlava*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-table"></i>&nbsp;Шифарници<span class="fas fa-chevron-down float-end"></span></a>
                        <ul class="nav nav-second-level">
                            <li {{ (Request::is('*sport*') ? 'class="active"' : '') }}>
                                <a href="{{ url('sport') }}">Спортови</a>
                            </li>
                            <li {{ (Request::is('*predmet*') ? 'class="active"' : '') }}>
                                <a href="{{ url('predmet') }}">Предмет</a>
                            </li>
                            <li {{ (Request::is('*profesor*') ? 'class="active"' : '') }}>
                                <a href="{{ url('profesor') }}">Професор</a>
                            </li>
                            <li {{ (Request::is('*krsnaSlava*') ? 'class="active"' : '') }}>
                                <a href="{{ url('krsnaSlava') }}">Крсна слава</a>
                            </li>
                            <li {{ (Request::is('*region*') ? 'class="active"' : '') }}>
                                <a href="{{ url('region') }}">Регион</a>
                            </li>
                            <li {{ (Request::is('*opstina*') ? 'class="active"' : '') }}>
                                <a href="{{ url('opstina') }}">Општина</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{ Request::is('*prisustvo*') || Request::is('*aktivnost*') || Request::is('*raspored*') || Request::is('*obavestenja*') || Request::is('*dashboard*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-plus-circle"></i>&nbsp;Нови модули<span class="fas fa-chevron-down float-end"></span></a>
                        <ul class="nav nav-second-level">
                            <li {{ (Request::is('*prisustvo*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/prisustvo') }}">Присуство</a>
                            </li>
                            <li {{ (Request::is('*aktivnost*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/aktivnost') }}">Активности</a>
                            </li>
                            <li {{ (Request::is('*raspored*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/raspored') }}">Распоред</a>
                            </li>
                            <li {{ (Request::is('*obavestenja*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/obavestenja') }}">Обавештења</a>
                            </li>
                            <li {{ (Request::is('*dashboard*') ? 'class="active"' : '') }}>
                                <a href="{{ url('/dashboard') }}">Аналитика</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div id="page-wrapper" style="margin-left: 250px; padding: 20px;">
            <div class="row">
                <div class="col-12">
                    <h2 class="page-header">@yield('page_heading')</h2>
                </div>
            </div>
            <div class="row">
                @yield('section')
            </div>
        </div>
    </div>

@stop
