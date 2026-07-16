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
                @forelse($rows as $row)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">{{ $row['kandidat']->imeKandidata }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">{{ $row['kandidat']->prezimeKandidata }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">{{ $row['kandidat']->brojIndeksa ?? '/' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">{{ $row['completion']['missing_count'] }}</td>
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
