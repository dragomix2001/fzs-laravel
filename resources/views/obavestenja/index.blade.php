@extends('layouts.layout')
@section('page_heading','Обавештења')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Обавештења</h2>

    <form method="GET" action="{{ route('obavestenja.index') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <x-form-select label="Тип обавештења" name="tip"
                           :options="['' => '-- Сви типови --', 'opste' => 'Опште', 'ispit' => 'Испит', 'raspored' => 'Распоред', 'upis' => 'Упис', 'Ocena' => 'Оцена', 'stipendija' => 'Стипендија']"
                           :selected="request('tip')" />
            <div class="flex items-center">
                <label class="flex items-center gap-2 text-sm font-medium text-secondary-700">
                    <input type="checkbox" name="samo_aktivna" value="1" {{ request('samo_aktivna') ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500">
                    Само активна
                </label>
            </div>
            <div class="flex items-end">
                <x-button variant="primary" class="w-full">Филтрирај</x-button>
            </div>
        </div>
    </form>

    @if($obavestenja->count() > 0)
        <x-table class="mt-4">
            <thead>
                <tr>
                    <th>Наслов</th>
                    <th>Тип</th>
                    <th>Датум објаве</th>
                    <th>Истиче</th>
                    <th>Професор</th>
                    <th>Статус</th>
                    <th>Акције</th>
                </tr>
            </thead>
            <tbody>
                @foreach($obavestenja as $obavestenje)
                <tr>
                    <td>{{ $obavestenje->naslov }}</td>
                    <td>
                        @switch($obavestenje->tip)
                            @case('opste') Опште @break
                            @case('ispit') Испит @break
                            @case('raspored') Распоред @break
                            @case('upis') Упис @break
                            @case('Ocena') Оцена @break
                            @case('stipendija') Стипендија @break
                            @default {{ $obavestenje->tip }}
                        @endswitch
                    </td>
                    <td>{{ \Carbon\Carbon::parse($obavestenje->datum_objave)->format('d.m.Y. H:i') }}</td>
                    <td>{{ $obavestenje->datum_isteka ? \Carbon\Carbon::parse($obavestenje->datum_isteka)->format('d.m.Y.') : '-' }}</td>
                    <td>{{ $obavestenje->profesor->ime ?? '' }} {{ $obavestenje->profesor->prezime ?? '' }}</td>
                    <td>
                        @if($obavestenje->aktivan)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Активно</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Неактивно</span>
                        @endif
                    </td>
                    <td>
                        <div class="inline-flex gap-1 flex-wrap">
                            <a href="{{ url('/obavestenja/' . $obavestenje->id) }}" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Прикажи</a>
                            <a href="{{ url('/obavestenja/' . ($obavestenje->id ?? '0') . '/edit') }}" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Измени</a>
                            <a href="{{ url('/obavestenja/' . $obavestenje->id . '/toggle') }}" class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600">
                                {{ $obavestenje->aktivan ? 'Деактивирај' : 'Активирај' }}
                            </a>
                            <form action="{{ url('/obavestenja/' . $obavestenje->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700" onclick="return confirm('Да ли сте сигурни?')">Обриши</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </x-table>
    @else
        <x-card variant="info" class="mt-4">Нема обавештења.</x-card>
    @endif

    <div class="mt-4 flex gap-2">
        <a href="{{ route('obavestenja.create') }}" class="inline-flex items-center text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm font-medium">Додај обавештење</a>
        <a href="{{ route('obavestenja.javna') }}" class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium">Јавна обавештења</a>
    </div>
</div>
@endsection
