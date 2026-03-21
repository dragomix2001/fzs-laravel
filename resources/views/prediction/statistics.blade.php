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
                    <div class="chart-container">
                        <canvas id="riskChart"></canvas>
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
                    <div class="chart-container">
                        <canvas id="gradeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Статистика
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Дистрибуција ризика</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Висок ризик
                                    <span class="badge bg-danger rounded-pill">{{ $statistics['risk_distribution']['high'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Умерен ризик
                                    <span class="badge bg-warning rounded-pill">{{ $statistics['risk_distribution']['medium'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Низак ризик
                                    <span class="badge bg-success rounded-pill">{{ $statistics['risk_distribution']['low'] }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Дистрибуција оцена</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    10 (одличан)
                                    <span class="badge bg-success rounded-pill">{{ $statistics['exam_statistics']['grade_distribution']['excellent'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    9 (врло добар)
                                    <span class="badge bg-primary rounded-pill">{{ $statistics['exam_statistics']['grade_distribution']['very_good'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    8 (добар)
                                    <span class="badge bg-info rounded-pill">{{ $statistics['exam_statistics']['grade_distribution']['good'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    7 (довољан)
                                    <span class="badge bg-warning rounded-pill">{{ $statistics['exam_statistics']['grade_distribution']['sufficient'] }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stats = @json($statistics);
    
    // Risk distribution pie chart
    const riskCtx = document.getElementById('riskChart').getContext('2d');
    new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: ['Висок ризик', 'Умерен ризик', 'Низак ризик'],
            datasets: [{
                data: [
                    stats.risk_distribution.high,
                    stats.risk_distribution.medium,
                    stats.risk_distribution.low
                ],
                backgroundColor: [
                    'rgba(220, 38, 38, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(22, 163, 74, 0.8)'
                ],
                borderColor: [
                    'rgba(220, 38, 38, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(22, 163, 74, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' студената';
                        }
                    }
                }
            }
        }
    });
    
    // Grade distribution bar chart
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: ['10 (одличан)', '9 (врло добар)', '8 (добар)', '7 (довољан)'],
            datasets: [{
                label: 'Број студената',
                data: [
                    stats.exam_statistics.grade_distribution.excellent,
                    stats.exam_statistics.grade_distribution.very_good,
                    stats.exam_statistics.grade_distribution.good,
                    stats.exam_statistics.grade_distribution.sufficient
                ],
                backgroundColor: [
                    'rgba(22, 163, 74, 0.7)',
                    'rgba(37, 99, 235, 0.7)',
                    'rgba(8, 145, 178, 0.7)',
                    'rgba(245, 158, 11, 0.7)'
                ],
                borderColor: [
                    'rgba(22, 163, 74, 1)',
                    'rgba(37, 99, 235, 1)',
                    'rgba(8, 145, 178, 1)',
                    'rgba(245, 158, 11, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endsection
