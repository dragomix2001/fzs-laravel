@extends('layouts.layout')

@section('title', 'AI Предикција - Факултет за спорт')

@section('page_heading', 'AI Предикција успеха')

@section('section')
<div class="col-span-12">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-secondary-500">
                AI предикција анализира перформансе студената и предвиђа вероватноћу успеха.
            </p>
        </div>
        <div class="text-right">
            <a href="{{ route('prediction.statistics') }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-chart-bar mr-2"></i>
                Статистика класе
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-primary-600 text-white">
            <h5 class="text-base font-semibold mb-0">
                <i class="fas fa-users mr-2"></i>
                Студенти
            </h5>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Студент</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">Акције</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-secondary-200">
                    @foreach($students as $student)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-4 py-3 text-sm">
                            <strong class="text-secondary-900">{{ $student->prezimeKandidata }} {{ $student->imeKandidata }}</strong>
                        </td>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->email }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('prediction.student', $student->id) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fas fa-chart-line mr-1"></i>
                                Анализирај
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
