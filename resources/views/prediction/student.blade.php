@extends('layouts.layout')

@section('title', 'AI Предикција - ' . $prediction['student']['ime'] . ' ' . $prediction['student']['prezime'])

@section('page_heading', 'AI Предикција: ' . $prediction['student']['prezime'] . ' ' . $prediction['student']['ime'])

@section('section')
<div class="col-span-12">
    <div class="mb-4">
        <x-button variant="secondary-soft" size="md" href="{{ route('prediction.index') }}">
            <i class="fas fa-arrow-left mr-2"></i>
            Назад на листу
        </x-button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden" style="border-left: 4px solid {{ $prediction['risk_level']['color'] == 'danger' ? '#dc2626' : ($prediction['risk_level']['color'] == 'warning' ? '#f59e0b' : '#16a34a') }}">
                <div class="px-4 py-3" style="background-color: {{ $prediction['risk_level']['color'] == 'danger' ? '#dc2626' : ($prediction['risk_level']['color'] == 'warning' ? '#f59e0b' : '#16a34a') }}; color: white;">
                    <h5 class="text-base font-semibold mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Ниво ризика
                    </h5>
                </div>
                <div class="p-6 text-center">
                    <h2 style="color: {{ $prediction['risk_level']['color'] == 'danger' ? '#dc2626' : ($prediction['risk_level']['color'] == 'warning' ? '#f59e0b' : '#16a34a') }};">
                        {{ $prediction['risk_level']['label'] }}
                    </h2>
                    <p class="mb-0 text-sm text-secondary-500">Ризик скор: {{ $prediction['risk_level']['score'] }}%</p>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-cyan-600 text-white">
                    <h5 class="text-base font-semibold mb-0">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Вероватноћа дипломирања
                    </h5>
                </div>
                <div class="p-6 text-center">
                    <h2 class="text-cyan-600 text-3xl font-bold">
                        {{ $prediction['prediction']['graduation_probability'] }}%
                    </h2>
                    <p class="mb-0 text-sm text-secondary-500">
                        Процењено преосталих семестара: 
                        {{ $prediction['prediction']['estimated_remaining_semesters'] }}
                    </p>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-success-600 text-white">
                    <h5 class="text-base font-semibold mb-0">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Пролазност
                    </h5>
                </div>
                <div class="p-6 text-center">
                    <h2 class="text-success-600 text-3xl font-bold">
                        {{ $prediction['statistics']['pass_rate'] }}%
                    </h2>
                    <p class="mb-0 text-sm text-secondary-500">
                        {{ $prediction['statistics']['passed_exams'] }} / 
                        {{ $prediction['statistics']['total_exams'] }} положених испита
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900 mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Детаљне статистике
                    </h5>
                </div>
                <div class="p-4">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <tbody class="divide-y divide-secondary-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-medium text-secondary-700">Укупно испита</th>
                                <td class="px-3 py-2 text-sm text-secondary-700">{{ $prediction['statistics']['total_exams'] }}</td>
                            </tr>
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-medium text-secondary-700">Положених</th>
                                <td class="px-3 py-2 text-sm text-success-600">{{ $prediction['statistics']['passed_exams'] }}</td>
                            </tr>
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-medium text-secondary-700">Палих</th>
                                <td class="px-3 py-2 text-sm text-danger-600">{{ $prediction['statistics']['failed_exams'] }}</td>
                            </tr>
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-medium text-secondary-700">Просечна оцена</th>
                                <td class="px-3 py-2 text-sm text-secondary-700">{{ $prediction['statistics']['average_grade'] }}</td>
                            </tr>
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-medium text-secondary-700">Пролазност (последњих 6 месеци)</th>
                                <td class="px-3 py-2 text-sm text-secondary-700">{{ $prediction['statistics']['recent_pass_rate'] }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900 mb-0">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Препоруке
                    </h5>
                </div>
                <div class="p-4">
                    @if(count($prediction['risk_level']['factors']) > 0)
                    <h6 class="text-sm font-semibold text-secondary-700 mb-2">Фактори ризика:</h6>
                    <ul class="space-y-2 mb-4">
                        @foreach($prediction['risk_level']['factors'] as $factor)
                        <li class="rounded-lg bg-warning-50 border border-warning-200 px-3 py-2 text-sm text-warning-800">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ $factor }}
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <h6 class="text-sm font-semibold text-secondary-700 mb-2">Препоручене акције:</h6>
                    <ul class="space-y-2">
                        @foreach($prediction['recommendations'] as $rec)
                        <li class="rounded-lg px-3 py-2 text-sm {{ $rec['priority'] === 'high' ? 'bg-danger-50 border border-danger-200 text-danger-800' : ($rec['priority'] === 'medium' ? 'bg-warning-50 border border-warning-200 text-warning-800' : 'bg-success-50 border border-success-200 text-success-800') }}">
                            <strong>{{ $rec['action'] }}</strong>
                            <br>
                            <small class="opacity-75">{{ $rec['reason'] }}</small>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if(count($prediction['prediction']['success_factors']) > 0)
    <div class="mt-4">
        <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-success-600 text-white">
                <h5 class="text-base font-semibold mb-0">
                    <i class="fas fa-star mr-2"></i>
                    Позитивни фактори
                </h5>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-2">
                    @foreach($prediction['prediction']['success_factors'] as $factor)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-success-100 text-success-700 ring-1 ring-inset ring-success-600/20">
                        <i class="fas fa-check mr-1"></i>
                        {{ $factor }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
