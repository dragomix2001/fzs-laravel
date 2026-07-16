@extends('layouts.layout')
@section('page_heading','Пријава испита')
@section('section')
    <div class="space-y-4">
        @if (Session::get('flash-error'))
            <x-alert type="danger">
                Дошло је до грешке при чувању података! Молимо вас покушајте поново.
            </x-alert>
        @endif
        @if (Session::get('flash-success'))
            <x-alert type="success">
                Подаци су успешно сачувани.
            </x-alert>
        @endif

        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Предмет: {{ $predmet?->naziv }}</h3>
            <a href="{{ url('/prijava/predmetVise/'.$predmet?->id) }}" class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-800">
                + Нова пријава - више студената
            </a>
        </div>

        <x-card>
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Кандидат</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Број Индекса</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Рок</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Код професора</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Датум</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </x-slot:header>
                @forelse($prijave as $prijava)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ ($prijava->kandidat?->imeKandidata ?? '') . ' ' . ($prijava->kandidat?->prezimeKandidata ?? '') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $prijava->kandidat?->brojIndeksa ?? '' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $prijava->rok?->naziv ?? '' }}</td>
                        <td class="px-4 py-3 text-sm">{{ ($prijava->profesor?->ime ?? '') . ' ' . ($prijava->profesor?->prezime ?? '') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $prijava->datum?->format('d.m.Y.') }}</td>
                        <td class="px-4 py-3">
                            <a class="text-danger-600 hover:text-danger-800 text-sm"
                               href="{{ url('/prijava/delete/'.$prijava->id) }}?prijava=predmet"
                               onclick="return confirm('Да ли сте сигурни да желите да обришете ову пријаву?');">Бриши</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm text-secondary-500">Нема пријављених студената.</td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
@endsection
