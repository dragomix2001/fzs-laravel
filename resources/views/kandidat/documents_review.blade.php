@extends('layouts.layout')
@section('page_heading','Преглед документације')
@section('section')
    <div class="space-y-6">
        @if(session('flash-success'))
            <x-alert type="success">{{ session('flash-success') }}</x-alert>
        @endif

        <x-card>
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Кандидат</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број индекса</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Статус документације</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Акција</th>
                    </tr>
                </x-slot:header>
                @forelse($kandidati as $kandidat)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-secondary-900">{{ $kandidat->imeKandidata }} {{ $kandidat->prezimeKandidata }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">{{ $kandidat->brojIndeksa ?? '/' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = $kandidat->dokumentacijaKompletna ? 'success' : 'warning';
                            @endphp
                            <x-badge :variant="$statusClass">
                                {{ $kandidat->dokumentacijaKompletna ? 'Комплетна' : 'Недостаје документа' }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ url('/kandidat/' . $kandidat->id) }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                Преглед
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-secondary-500">Нема кандидата.</td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
@endsection
