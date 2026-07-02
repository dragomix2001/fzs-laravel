@extends('layouts.layout')
@section('page_heading','Аналитика испита')
@section('section')

<div class="col-span-12 lg:col-span-10">
<h2 class="text-xl font-semibold text-secondary-900 mb-4">Аналитика испита</h2>

    <form method="GET" action="{{ route('dashboard.ispiti') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Година</label>
                <select name="godina" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $godina == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900">Положени испити по месецима</h5>
                </div>
                <div class="p-4">
                    <x-table>
                        <x-slot:header>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Месец</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број</th>
                            </tr>
                        </x-slot:header>
                        @php
                        $meseci = ['', 'Јануар', 'Фебруар', 'Март', 'Април', 'Мај', 'Јун', 'Јул', 'Август', 'Септембар', 'Октобар', 'Новембар', 'Децембар'];
                        @endphp
                        @for($m = 1; $m <= 12; $m++)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $meseci[$m] }}</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $polozeniPoMesecima->firstWhere('mesec', $m)->broj ?? 0 }}</td>
                        </tr>
                        @endfor
                    </x-table>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900">Пријаве испита по месецима</h5>
                </div>
                <div class="p-4">
                    <x-table>
                        <x-slot:header>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Месец</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број</th>
                            </tr>
                        </x-slot:header>
                        @for($m = 1; $m <= 12; $m++)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $meseci[$m] }}</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $prijavePoMesecima->firstWhere('mesec', $m)->broj ?? 0 }}</td>
                        </tr>
                        @endfor
                    </x-table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900">Успех по предметима ({{ $godina }})</h5>
                </div>
                <div class="p-4">
                    <x-table>
                        <x-slot:header>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Предмет</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Укупно</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Положено</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Пролазност</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Просечна оцена</th>
                            </tr>
                        </x-slot:header>
                        @forelse($uspehPoPredmetu as $up)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $up->predmet->naziv ?? '-' }}</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $up->ukupno }}</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $up->polozeni }}</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $up->ukupno > 0 ? round(($up->polozeni / $up->ukupno) * 100, 1) : 0 }}%</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $up->prosek ? round($up->prosek, 2) : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-2 text-sm text-secondary-500 text-center">Нема података</td>
                        </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <x-button variant="secondary-soft" size="md" href="{{ route('dashboard.index') }}">Назад</x-button>
    </div>
</div>
@endsection
