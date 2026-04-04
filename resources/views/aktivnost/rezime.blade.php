@extends('layouts.layout')
@section('page_heading','Резиме активности')
@section('section')

<div class="col-sm-12 col-lg-10">
    <h2>Резиме активности: {{ $predmet->naziv ?? '' }}</h2>

    <a href="{{ route('aktivnost.index') }}" class="btn btn-secondary mb-3">Назад на листу</a>

    @if(isset($aktivnosti) && count($aktivnosti) > 0)
    <div class="mb-4">
        <h4>Листа активности за предмет:</h4>
        <ul>
            @foreach($aktivnosti as $aktiv)
                <li>{{ $aktiv->naziv }} (Максимално бодова: {{ $aktiv->max_bodova }})</li>
            @endforeach
        </ul>
    </div>
    @endif

    <table class="table table-bordered mt-3">
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
                            <span class="text-success"><strong>{{ $rez['procenat'] }}%</strong></span>
                        @else
                            <span class="text-danger"><strong>{{ $rez['procenat'] }}%</strong></span>
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
    </table>
</div>
@endsection
