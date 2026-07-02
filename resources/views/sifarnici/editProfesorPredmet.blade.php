<title>Додај предмет</title>
@extends('layouts.layout')
@section('page_heading','Додај предмет')
@section('section')

    <div class="max-w-4xl">
        <div class="mb-4">
            <a href="/profesor" class="text-primary-600 hover:text-primary-800">&lt;&lt;Назад на професоре</a>
        </div>

        <div class="mb-4">
            <form action="/profesor/{{$profesor->id}}/addPredmet" class="inline">
                <x-button variant="danger">Додај</x-button>
            </form>
        </div>

        <x-table>
            <thead>
            <tr>
                <th>Назив</th>
                <th>Тип предмета</th>
                <th>Семестар</th>
                <th>Облик наставе</th>
                <th>Акције</th>
            </tr>
            </thead>
            <tbody>
            @foreach($predmeti as $predmet)
                <tr>
                    <td>{{$predmet->predmet?->predmet?->naziv ?? '-'}} - {{$predmet->predmet?->program?->skrNazivStudijskogPrograma ?? '-'}}</td>
                    <td>{{$predmet->predmet?->tipPredmeta?->naziv ?? '-'}}</td>
                    <td>{{$predmet->predmet->semestar}}</td>
                    <td>{{$predmet->oblik_nastave->naziv}}</td>
                    <td>
                        <div class="flex gap-2">
                            <form onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="/profesor/{{$predmet->id}}/deletePredmet" class="inline">
                                <x-button variant="danger" size="sm">Обриши</x-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </x-table>
    </div>

@endsection
