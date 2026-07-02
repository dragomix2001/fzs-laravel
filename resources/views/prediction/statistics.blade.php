@extends('layouts.layout')

@section('title', 'AI Статистика класе - Факултет за спорт')

@section('page_heading', 'AI Статистика класе')

@section('section')
<div class="col-span-12">
    <div class="mb-4">
        <a href="{{ route('prediction.index') }}" class="inline-flex items-center px-4 py-2 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Назад на листу студената
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <div class="bg-primary-600 text-white rounded-lg shadow-sm p-6 text-center">
                <p class="text-3xl font-bold">{{ $statistics['total_students'] }}</p>
                <p class="mb-0 text-sm text-primary-100">Укупно студената</p>
            </div>
        </div>
        <div>
            <div class="bg-success-600 text-white rounded-lg shadow-sm p-6 text-center">
                <p class="text-3xl font-bold">{{ $statistics['overall_pass_rate'] }}%</p>
                <p class="mb-0 text-sm text-success-100">Укупна пролазност</p>
            </div>
        </div>
        <div>
            <div class="bg-cyan-600 text-white rounded-lg shadow-sm p-6 text-center">
                <p class="text-3xl font-bold">{{ $statistics['exam_statistics']['total_passed'] }}</p>
                <p class="mb-0 text-sm text-cyan-100">Укупно положених испита</p>
            </div>
        </div>
        <div>
            <div class="bg-warning-600 text-white rounded-lg shadow-sm p-6 text-center">
                <p class="text-3xl font-bold">{{ $statistics['exam_statistics']['average_grade'] }}</p>
                <p class="mb-0 text-sm text-warning-100">Просечна оцена</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900 mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Дистрибуција ризика
                    </h5>
                </div>
                <div class="p-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="riskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900 mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Дистрибуција оцена
                    </h5>
                </div>
                <div class="p-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="gradeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                <h5 class="text-base font-semibold text-secondary-900 mb-0">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Статистика
                </h5>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h6 class="text-sm font-semibold text-secondary-700 mb-2">Дистрибуција ризика</h6>
                        <ul class="space-y-2">
                            <li class="flex items-center justify-between px-3 py-2 rounded-lg bg-secondary-50">
                                <span class="text-sm text-secondary-700">Висок ризик</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700 ring-1 ring-inset ring-danger-600/20">{{ $statistics['risk_distribution']['high'] }}</span>
                            </li>
                            <li class="flex items-center justify-between px-3 py-2 rounded-lg bg-secondary-50">
                                <span class="text-sm text-secondary-700">Умерен ризик</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-700 ring-1 ring-inset ring-warning-600/20">{{ $statistics['risk_distribution']['medium'] }}</span>
                            </li>
                            <li class="flex items-center justify-between px-3 py-2 rounded-lg bg-secondary-50">
                                <span class="text-sm text-secondary-700">Низак ризик</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700 ring-1 ring-inset ring-success-600/20">{{ $statistics['risk_distribution']['low'] }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h6 class="text-sm font-semibold text-secondary-700 mb-2">Дистрибуција оцена</h6>
                        <ul class="space-y-2">
                            <li class="flex items-center justify-between px-3 py-2 rounded-lg bg-secondary-50">
                                <span class="text-sm text-secondary-700">10 (одличан)</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700 ring-1 ring-inset ring-success-600/20">{{ $statistics['exam_statistics']['grade_distribution']['excellent'] }}</span>
                            </li>
                            <li class="flex items-center justify-between px-3 py-2 rounded-lg bg-secondary-50">
                                <span class="text-sm text-secondary-700">9 (врло добар)</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700 ring-1 ring-inset ring-primary-600/20">{{ $statistics['exam_statistics']['grade_distribution']['very_good'] }}</span>
                            </li>
                            <li class="flex items-center justify-between px-3 py-2 rounded-lg bg-secondary-50">
                                <span class="text-sm text-secondary-700">8 (добар)</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700 ring-1 ring-inset ring-cyan-600/20">{{ $statistics['exam_statistics']['grade_distribution']['good'] }}</span>
                            </li>
                            <li class="flex items-center justify-between px-3 py-2 rounded-lg bg-secondary-50">
                                <span class="text-sm text-secondary-700">7 (довољан)</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-700 ring-1 ring-inset ring-warning-600/20">{{ $statistics['exam_statistics']['grade_distribution']['sufficient'] }}</span>
                            </li>
                        </ul>
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
