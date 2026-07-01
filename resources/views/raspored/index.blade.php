@extends('layouts.layout')
@section('page_heading','Распоред часова')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Распоред часова</h2>

    <form method="GET" action="{{ route('raspored.index') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-form-select label="Студијски програм" name="studijski_program_id"
                           :options="collect(['' => '-- Сви програми --'] + $studijskiProgrami->pluck('naziv','id')->toArray())->toArray()"
                           :selected="request('studijski_program_id')" />
            <x-form-select label="Семестар" name="semestar_id"
                           :options="collect(['' => '-- Сви семестри --'] + $semestri->pluck('naziv','id')->toArray())->toArray()"
                           :selected="request('semestar_id')" />
            <x-form-select label="Школска година" name="skolska_godina_id"
                           :options="$skolskeGodine->mapWithKeys(function($item) { return [$item->id => $item->godina.'/'.($item->godina+1)]; })->prepend('-- Све године --', '')->toArray()"
                           :selected="request('skolska_godina_id')" />
            <div class="flex items-end">
                <x-button variant="primary" class="w-full">Филтрирај</x-button>
            </div>
        </div>
    </form>

    <div class="mb-3 flex gap-2">
        <a href="{{ route('raspored.kalendar') }}" class="inline-flex items-center gap-1 text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium">
            <span class="fa fa-calendar"></span> Календар
        </a>
        <a href="{{ route('raspored.pregled') }}" class="inline-flex items-center gap-1 text-gray-700 bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded text-sm font-medium">
            <span class="fa fa-list"></span> Табеларни преглед
        </a>
    </div>

    @if($raspored->count() > 0)
        <x-table class="mt-4">
            <thead>
                <tr>
                    <th>Дан</th>
                    <th>Време</th>
                    <th>Предмет</th>
                    <th>Професор</th>
                    <th>Облик наставе</th>
                    <th>Година</th>
                    <th>Просторија</th>
                    <th>Група</th>
                    <th>Акције</th>
                </tr>
            </thead>
            <tbody>
                @foreach($raspored as $r)
                <tr>
                    <td>
                        @switch($r->dan)
                            @case(1) Понедељак @break
                            @case(2) Уторак @break
                            @case(3) Среда @break
                            @case(4) Четвртак @break
                            @case(5) Петак @break
                            @case(6) Субота @break
                            @case(7) Недеља @break
                        @endswitch
                    </td>
                    <td>{{ \Carbon\Carbon::parse($r->vreme_od)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->vreme_do)->format('H:i') }}</td>
                    <td>{{ $r->predmet->naziv ?? '-' }}</td>
                    <td>{{ $r->profesor->ime ?? '' }} {{ $r->profesor->prezime ?? '' }}</td>
                    <td>{{ $r->oblikNastave->naziv ?? '-' }}</td>
                    <td>{{ $r->godinaStudija->naziv ?? '-' }}</td>
                    <td>{{ $r->prostorija ?? '-' }}</td>
                    <td>{{ $r->grupa ?? '-' }}</td>
                    <td>
                        <div class="inline-flex gap-1">
                            <a href="{{ route('raspored.edit', $r->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Измени</a>
                            <form action="{{ route('raspored.destroy', $r->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700" onclick="return confirm('Да ли сте сигурни?')">Обриши</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </x-table>
    @else
        <x-card variant="info" class="mt-4">
            Нема унесених часова за изабране критеријуме.
        </x-card>
    @endif

    <div class="mt-4 flex gap-2">
        <a href="{{ route('raspored.create') }}" class="inline-flex items-center text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm font-medium">Додај час</a>
        <a href="{{ route('raspored.pregled') }}" class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium">Преглед распореда</a>
    </div>
</div>
@endsection
