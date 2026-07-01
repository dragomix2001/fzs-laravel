@extends('layouts.layout')
@section('page_heading','Кандидати са непотпуним документима')
@section('section')
    <div class="space-y-4">
        @if(session('flash-success'))
            <x-alert type="success">Подаци су сачувани.</x-alert>
        @endif

        <x-card>
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Име</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Презиме</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број индекса</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Недостајућа документа</th>
                    </tr>
                </x-slot:header>
                @forelse($kandidati as $kandidat)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">{{ $kandidat->imeKandidata }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">{{ $kandidat->prezimeKandidata }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">{{ $kandidat->brojIndeksa }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">{{ $kandidat->nedostajucaDokumenta ?? '/' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-secondary-500">Нема кандидата са непотпуним документима.</td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
@endsection
