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
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">Активно</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800">Неактивно</span>
                        @endif
                    </td>
                    <td>
                        <div class="inline-flex gap-1 flex-wrap">
                            <x-button variant="primary" size="xs" href="{{ url('/obavestenja/' . $obavestenje->id) }}">Прикажи</x-button>
                            <x-button variant="primary" size="xs" href="{{ url('/obavestenja/' . ($obavestenje->id ?? '0') . '/edit') }}">Измени</x-button>
                            <a href="{{ url('/obavestenja/' . $obavestenje->id . '/toggle') }}" class="inline-flex items-center px-2 py-1 bg-warning-500 text-white text-xs font-medium rounded hover:bg-warning-600">
                                {{ $obavestenje->aktivan ? 'Деактивирај' : 'Активирај' }}
                            </a>
                            <form action="{{ url('/obavestenja/' . $obavestenje->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <x-button variant="danger" size="xs" type="submit" onclick="return confirm('Да ли сте сигурни?')">Обриши</x-button>
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
        <x-button variant="success" size="md" href="{{ route('obavestenja.create') }}">Додај обавештење</x-button>
        <x-button variant="primary" size="md" href="{{ route('obavestenja.javna') }}">Јавна обавештења</x-button>
    </div>
</div>
@endsection
