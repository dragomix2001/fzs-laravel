@extends('layouts.layout')

@section('title', 'AI Предикција - ' . $prediction['student']['ime'] . ' ' . $prediction['student']['prezime'])

@section('page_heading', 'AI Предикција: ' . $prediction['student']['prezime'] . ' ' . $prediction['student']['ime'])

@section('section')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('prediction.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Назад на листу
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-{{ $prediction['risk_level']['color'] }}">
                <div class="card-header bg-{{ $prediction['risk_level']['color'] }} text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Ниво ризика
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-{{ $prediction['risk_level']['color'] }}">
                        {{ $prediction['risk_level']['label'] }}
                    </h2>
                    <p class="mb-0">Ризик скор: {{ $prediction['risk_level']['score'] }}%</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Вероватноћа дипломирања
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-info">
                        {{ $prediction['prediction']['graduation_probability'] }}%
                    </h2>
                    <p class="mb-0">
                        Процењено преосталих семестара: 
                        {{ $prediction['prediction']['estimated_remaining_semesters'] }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Пролазност
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success">
                        {{ $prediction['statistics']['pass_rate'] }}%
                    </h2>
                    <p class="mb-0">
                        {{ $prediction['statistics']['passed_exams'] }} / 
                        {{ $prediction['statistics']['total_exams'] }} положених испита
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Детаљне статистике
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Укупно испита</th>
                            <td>{{ $prediction['statistics']['total_exams'] }}</td>
                        </tr>
                        <tr>
                            <th>Положених</th>
                            <td class="text-success">{{ $prediction['statistics']['passed_exams'] }}</td>
                        </tr>
                        <tr>
                            <th>Палих</th>
                            <td class="text-danger">{{ $prediction['statistics']['failed_exams'] }}</td>
                        </tr>
                        <tr>
                            <th>Просечна оцена</th>
                            <td>{{ $prediction['statistics']['average_grade'] }}</td>
                        </tr>
                        <tr>
                            <th>Пролазност (последњих 6 месеци)</th>
                            <td>{{ $prediction['statistics']['recent_pass_rate'] }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Препоруке
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($prediction['risk_level']['factors']) > 0)
                    <h6>Фактори ризика:</h6>
                    <ul class="list-group mb-3">
                        @foreach($prediction['risk_level']['factors'] as $factor)
                        <li class="list-group-item list-group-item-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ $factor }}
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <h6>Препоручене акције:</h6>
                    <ul class="list-group">
                        @foreach($prediction['recommendations'] as $rec)
                        <li class="list-group-item list-group-item-{{ $rec['priority'] === 'high' ? 'danger' : ($rec['priority'] === 'medium' ? 'warning' : 'success') }}">
                            <strong>{{ $rec['action'] }}</strong>
                            <br>
                            <small class="text-muted">{{ $rec['reason'] }}</small>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if(count($prediction['prediction']['success_factors']) > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Позитивни фактори
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-inline">
                        @foreach($prediction['prediction']['success_factors'] as $factor)
                        <li class="list-inline-item">
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>
                                {{ $factor }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
