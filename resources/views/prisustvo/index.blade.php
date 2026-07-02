@extends('layouts.layout')
@section('page_heading','Евиденција присуства')
@section('section')

<div class="col-span-12 lg:col-span-10">
<h2 class="text-xl font-semibold text-secondary-900 mb-4">Евиденција присуства на настави</h2>
    
    <form method="GET" action="{{ route('prisustvo.index') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Предмет</label>
                <select name="predmet" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="">-- Изаберите предмет --</option>
                    @foreach($predmeti as $predmet)
                        <option value="{{ $predmet->id }}">{{ $predmet->naziv }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Наставна недеља</label>
                <select name="nedelja" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    <option value="">-- Изаберите недељу --</option>
                    @foreach($nedelje as $nedelja)
                        <option value="{{ $nedelja->id }}">Недеља {{ $nedelja->redni_broj }} ({{ $nedelja->datum_pocetka }} - {{ $nedelja->datum_kraja }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors w-full">Прикажи</button>
            </div>
        </div>
    </form>

    @if($prisanstva)
        <div class="overflow-x-auto rounded-lg border border-secondary-200 shadow-sm mt-4">
            <table class="min-w-full divide-y divide-secondary-200">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број индекса</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Име и презиме</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Напомена</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-secondary-200">
                    @foreach($prisanstva as $prisanstvo)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $prisanstvo->student->brojIndeksa }}</td>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $prisanstvo->student->ime }} {{ $prisanstvo->student->prezimeKandidata }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($prisanstvo->status == 'prisutan')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-success-100 text-success-700 ring-1 ring-inset ring-success-600/20">Присутан</span>
                            @elseif($prisanstvo->status == 'odsutan')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-700 ring-1 ring-inset ring-danger-600/20">Одсутан</span>
                            @elseif($prisanstvo->status == 'opravdan')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-warning-100 text-warning-700 ring-1 ring-inset ring-warning-600/20">Оправдан</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 ring-1 ring-inset ring-primary-600/20">Каснио</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $prisanstvo->napomena }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-4 flex gap-2">
        <a href="{{ route('prisustvo.create') }}" class="inline-flex items-center px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors">Унеси присуство</a>
        <a href="{{ route('prisustvo.report') }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg transition-colors">Извештај</a>
    </div>
</div>
@endsection
