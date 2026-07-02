@extends('layouts.layout')
@section('page_heading','Резиме активности')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Резиме активности: {{ $predmet->naziv ?? '' }}</h2>

    <x-button variant="secondary-soft" size="md" href="{{ route('aktivnost.index') }}" class="mb-4">Назад на листу</x-button>

    @if(isset($aktivnosti) && count($aktivnosti) > 0)
    <div class="mb-4">
        <h4>Листа активности за предмет:</h4>
        <ul class="list-disc pl-5">
            @foreach($aktivnosti as $aktiv)
                <li>{{ $aktiv->naziv }} (Максимално бодова: {{ $aktiv->max_bodova }})</li>
            @endforeach
        </ul>
    </div>
    @endif

    <x-table class="mt-3">
        <thead>
            <tr>
                <th>Број индекса</th>
                <th>Име и презиме</th>
                <th>Укупно бодова</th>
                <th>Максимално могућих</th>
                <th>Проценат (%)</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($rezultati) && count($rezultati) > 0)
                @foreach($rezultati as $rez)
                <tr>
                    <td>{{ $rez['student']->brojIndeksa ?? '' }}</td>
                    <td>{{ $rez['student']->ime ?? '' }} {{ $rez['student']->prezime ?? '' }}</td>
                    <td>{{ $rez['bodovi'] }}</td>
                    <td>{{ $rez['max'] }}</td>
                    <td>
                        @if($rez['procenat'] >= 51)
                            <span class="text-success-600 font-bold">{{ $rez['procenat'] }}%</span>
                        @else
                            <span class="text-danger-600 font-bold">{{ $rez['procenat'] }}%</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">Нема доступних резултата.</td>
                </tr>
            @endif
        </tbody>
    </x-table>
</div>
@endsection
