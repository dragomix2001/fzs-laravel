@extends('layouts.layout')
@section('page_heading','Преглед распореда часова')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Преглед распореда часова</h2>

    <form method="GET" action="{{ route('raspored.pregled') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-form-select label="Школска година" name="skolska_godina_id"
                           :options="$skolskeGodine->mapWithKeys(function($item) { return [$item->id => $item->godina.'/'.($item->godina+1)]; })->toArray()" />
            <div class="flex items-end">
                <x-button variant="primary" class="w-full">Прикажи</x-button>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        @foreach($rasporedPoDanima as $dan => $data)
            @if($data['casovi']->count() > 0)
                <x-card variant="primary">
                    <x-slot:header>
                        <h5 class="text-lg font-semibold text-white">{{ $data['naziv'] }}</h5>
                    </x-slot:header>
                    <x-table>
                        <thead>
                            <tr>
                                <th>Време</th>
                                <th>Предмет</th>
                                <th>Професор</th>
                                <th>Облик</th>
                                <th>Прост.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['casovi'] as $cas)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($cas->vreme_od)->format('H:i') }}<br>{{ \Carbon\Carbon::parse($cas->vreme_do)->format('H:i') }}</td>
                                <td>
                                    {{ $cas->predmet->naziv ?? '-' }}
                                    @if($cas->grupa)
                                        <br><small class="text-secondary-500">Група: {{ $cas->grupa }}</small>
                                    @endif
                                </td>
                                <td>{{ $cas->profesor->ime ?? '' }} {{ $cas->profesor->prezime ?? '' }}</td>
                                <td>{{ $cas->oblikNastave->naziv ?? '-' }}</td>
                                <td>{{ $cas->prostorija ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </x-table>
                </x-card>
            @endif
        @endforeach
    </div>

    <div class="mt-4">
        <x-button variant="secondary-soft" size="md" href="{{ route('raspored.index') }}">Назад на управљање</x-button>
    </div>
</div>
@endsection
