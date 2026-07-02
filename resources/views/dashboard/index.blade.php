@extends('layouts.layout')
@section('page_heading','Аналитика и статистика')
@section('section')

<div class="col-span-12 lg:col-span-10">
<div class="flex items-center justify-between mb-4">
    <div>
        <h2 class="text-xl font-semibold text-secondary-900">Аналитика и статистика</h2>
    </div>
    <div class="text-right">
        <x-button variant="secondary-soft" size="sm">
            <i class="fas fa-cog mr-1"></i> Виџети
        </x-button>
    </div>
</div>

    <form method="GET" action="{{ route('dashboard.index') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Школска година</label>
                <select name="skolska_godina_id" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" onchange="this.form.submit()">
                    @foreach($skolskeGodine as $godina)
                        <option value="{{ $godina->id }}" {{ $skolskaGodinaId == $godina->id ? 'selected' : '' }}>
                            {{ $godina->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        @if($widgets['studenti_ukupno'])
        <div>
            <div class="bg-primary-600 text-white rounded-lg shadow-sm p-6">
                <h5 class="text-sm font-medium text-primary-100 uppercase tracking-wide">Укупно студената</h5>
                <p class="text-3xl font-bold mt-2 mb-0">{{ $ukupnoStudenata }}</p>
            </div>
        </div>
        @endif
        @if($widgets['polozeni_ispiti'])
        <div>
            <div class="bg-success-600 text-white rounded-lg shadow-sm p-6">
                <h5 class="text-sm font-medium text-success-100 uppercase tracking-wide">Положени испити</h5>
                <p class="text-3xl font-bold mt-2 mb-0">{{ $polozeniIspiti }}</p>
            </div>
        </div>
        @endif
        @if($widgets['prijavljeni_ispiti'])
        <div>
            <div class="bg-warning-600 text-white rounded-lg shadow-sm p-6">
                <h5 class="text-sm font-medium text-warning-100 uppercase tracking-wide">Пријављени испити</h5>
                <p class="text-3xl font-bold mt-2 mb-0">{{ $prijavljeniIspiti }}</p>
            </div>
        </div>
        @endif
        @if($widgets['aktivna_obavestenja'])
        <div>
            <div class="bg-cyan-600 text-white rounded-lg shadow-sm p-6">
                <h5 class="text-sm font-medium text-cyan-100 uppercase tracking-wide">Активна обавештења</h5>
                <p class="text-3xl font-bold mt-2 mb-0">{{ $aktivnaObavestenja }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        @if($widgets['studenti_po_programu'])
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900">Студенти по студијском програму</h5>
                </div>
                <div class="p-4">
                    <x-table>
                        <x-slot:header>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Програм</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број</th>
                            </tr>
                        </x-slot:header>
                        @foreach($studentiPoProgramu as $sp)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $sp->program->naziv ?? '-' }}</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $sp->broj }}</td>
                        </tr>
                        @endforeach
                    </x-table>
                </div>
            </div>
        </div>
        @endif
        
        @if($widgets['studenti_po_godini'])
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900">Студенти по години уписа</h5>
                </div>
                <div class="p-4">
                    <x-table>
                        <x-slot:header>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Година</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број</th>
                            </tr>
                        </x-slot:header>
                        @foreach($studentiPoGodini as $sg)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $sg->godinaUpisa->godina ?? '-' }}/{{ ($sg->godinaUpisa->godina ?? 0) + 1 }}</td>
                            <td class="px-3 py-2 text-sm text-secondary-700">{{ $sg->broj }}</td>
                        </tr>
                        @endforeach
                    </x-table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        @if($widgets['prolaznost'])
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-success-600 text-white">
                    <h5 class="text-base font-semibold">Пролазност</h5>
                </div>
                <div class="p-6 text-center">
                    <p class="text-4xl font-bold text-secondary-900">{{ $prolaznost }}%</p>
                    <p class="text-sm text-secondary-500 mt-1">Положено / Пријављено</p>
                </div>
            </div>
        </div>
        @endif
        
        @if($widgets['neuspesni_predmeti'])
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-secondary-200 bg-secondary-50">
                    <h5 class="text-base font-semibold text-secondary-900">Најчешће неуспешни предмети (тренутна година)</h5>
                </div>
                <div class="p-4">
                    @if($najcesciNeuspesni->count() > 0)
                        <x-table>
                            <x-slot:header>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Предмет</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број неуспешних</th>
                                </tr>
                            </x-slot:header>
                            @foreach($najcesciNeuspesni as $np)
                            <tr class="hover:bg-secondary-50">
                                <td class="px-3 py-2 text-sm text-secondary-700">{{ $np->predmet->naziv ?? '-' }}</td>
                                <td class="px-3 py-2 text-sm text-secondary-700">{{ $np->broj }}</td>
                            </tr>
                            @endforeach
                        </x-table>
                    @else
                        <p class="text-sm text-secondary-500">Нема података</p>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="mt-4 flex gap-2">
        <x-button variant="primary" size="md" href="{{ route('dashboard.studenti') }}">Детаљни преглед студената</x-button>
        <x-button variant="info" size="md" href="{{ route('dashboard.ispiti') }}">Аналитика испита</x-button>
    </div>
</div>

<div x-data="{ open: false }" x-show="open" x-cloak @open-modal.window="if ($event.detail === 'widgetSettings') open = true" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-secondary-500 bg-opacity-75 transition-opacity" @click="open = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('dashboard.widgets') }}">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between pb-3 border-b border-secondary-200">
                        <h3 class="text-lg font-semibold text-secondary-900" id="modal-title">Подешавање виџета</h3>
                        <button type="button" @click="open = false" class="inline-flex items-center p-1.5 text-secondary-400 hover:text-secondary-500 rounded-md">
                            <span class="sr-only">Затвори</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                    <div class="mt-4 space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="studenti_ukupno" {{ $widgets['studenti_ukupno'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Укупно студената</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="polozeni_ispiti" {{ $widgets['polozeni_ispiti'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Положени испити</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="prijavljeni_ispiti" {{ $widgets['prijavljeni_ispiti'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Пријављени испити</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="aktivna_obavestenja" {{ $widgets['aktivna_obavestenja'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Активна обавештења</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="studenti_po_programu" {{ $widgets['studenti_po_programu'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Студенти по програму</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="studenti_po_godini" {{ $widgets['studenti_po_godini'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Студенти по години</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="prolaznost" {{ $widgets['prolaznost'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Пролазност</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="neuspesni_predmeti" {{ $widgets['neuspesni_predmeti'] ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-700">Неуспешни предмети</span>
                        </label>
                    </div>
                </div>
                <div class="px-4 py-3 bg-secondary-50 text-right sm:px-6 border-t border-secondary-200">
                    <x-button variant="primary" size="md" type="submit">Сачувај</x-button>
                    <x-button variant="primary" size="md" type="submit">Откажи</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
