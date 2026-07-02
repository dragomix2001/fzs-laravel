<title>Додај програм</title>
@extends('layouts.layout')
@section('page_heading','Додај програм')
@section('section')

    <div class="max-w-4xl">
        <div class="mb-4">
            <a href="/predmet" class="text-primary-600 hover:text-primary-800">&lt;&lt;Назад на предмете</a>
        </div>

        <div class="mb-4">
            <form action="/predmet/{{$predmet->id}}/addProgram" class="inline">
                <x-button variant="danger">Додај</x-button>
            </form>
        </div>

        <x-table>
            <thead>
            <tr>
                <th>Назив</th>
                <th>Тип студија</th>
                <th>Година студија</th>
                <th>Семестар</th>
                <th>Тип предмета</th>
                <th>ЕСПБ</th>
                <th>Акције</th>
            </tr>
            </thead>
            <tbody>
            @foreach($programi as $program)
                <tr>
                    <td>
                        @if($program->program)
                            {{$program->program->naziv}}
                        @endif
                    </td>
                    <td>
                        @if($program->program)
                            {{$program->program->tipStudija?->naziv ?? '-'}}
                        @endif
                    </td>
                    <td>
                        @if($program->godinaStudija)
                            {{$program->godinaStudija->naziv}}
                        @endif
                    </td>
                    <td>{{$program->semestar}}</td>
                    <td>
                        @if($program->tipPredmeta)
                            {{$program->tipPredmeta->naziv}}
                        @endif
                    </td>
                    <td>{{$program->espb}}</td>
                    <td>
                        <div class="flex gap-2">
                            <form onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="/predmet/{{$program->id}}/deleteProgram" class="inline">
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
