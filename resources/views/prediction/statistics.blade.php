@extends('layouts.layout')

@section('title', 'AI Статистика класе - Факултет за спорт')

@section('page_heading', 'AI Статистика класе')

@section('section')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('prediction.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Назад на листу студената
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $statistics['total_students'] }}</h3>
                    <p class="mb-0">Укупно студената</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $statistics['overall_pass_rate'] }}%</h3>
                    <p class="mb-0">Укупна пролазност</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $statistics['exam_statistics']['total_passed'] }}</h3>
                    <p class="mb-0">Укупно положених испита</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $statistics['exam_statistics']['average_grade'] }}</h3>
                    <p class="mb-0">Просечна оцена</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Дистрибуција ризика
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h2 class="text-danger">{{ $statistics['risk_distribution']['high'] }}</h2>
                                    <p class="mb-0">Висок ризик</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h2 class="text-warning">{{ $statistics['risk_distribution']['medium'] }}</h2>
                                    <p class="mb-0">Умерен ризик</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h2 class="text-success">{{ $statistics['risk_distribution']['low'] }}</h2>
                                    <p class="mb-0">Низак ризик</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Дистрибуција оцена
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Оцена</th>
                                <th class="text-center">Број</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success">10 (одличан)</span></td>
                                <td class="text-center">{{ $statistics['exam_statistics']['grade_distribution']['excellent'] }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">9 (врло добар)</span></td>
                                <td class="text-center">{{ $statistics['exam_statistics']['grade_distribution']['very_good'] }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">8 (добар)</span></td>
                                <td class="text-center">{{ $statistics['exam_statistics']['grade_distribution']['good'] }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">7 (довољан)</span></td>
                                <td class="text-center">{{ $statistics['exam_statistics']['grade_distribution']['sufficient'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
