@extends('layouts.layout')
@section('page_heading','Обавештења')
@section('section')

<div class="col-sm-12 col-lg-10">
<h2>Обавештења</h2>
    
    <form method="GET" action="{{ route('obavestenja.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Тип обавештења</label>
                <select name="tip" class="form-control">
                    <option value="">-- Сви типови --</option>
                    <option value="opste" {{ request('tip') == 'opste' ? 'selected' : '' }}>Опште</option>
                    <option value="ispit" {{ request('tip') == 'ispit' ? 'selected' : '' }}>Испит</option>
                    <option value="raspored" {{ request('tip') == 'raspored' ? 'selected' : '' }}>Распоред</option>
                    <option value="upis" {{ request('tip') == 'upis' ? 'selected' : '' }}>Упис</option>
                    <option value="Ocena" {{ request('tip') == 'Ocena' ? 'selected' : '' }}>Оцена</option>
                    <option value="stipendija" {{ request('tip') == 'stipendija' ? 'selected' : '' }}>Стипендија</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="samo_aktivna" value="1" {{ request('samo_aktivna') ? 'checked' : '' }}>
                        Само активна
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Филтрирај</button>
            </div>
        </div>
    </form>

    @if($obavestenja->count() > 0)
        <table class="table table-bordered mt-4">
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
                            <span class="label label-success">Ативно</span>
                        @else
                            <span class="label label-default">Неактивно</span>
                        @endif
                    </td>
                    <td>
<a href="{{ url('/obavestenja/' . $obavestenje->id) }}" class="btn btn-sm btn-info">Прикажи</a>
<a href="{{ url('/obavestenja/' . ($obavestenje->id ?? '0') . '/edit') }}" class="btn btn-sm btn-primary">Измени</a>
<a href="{{ url('/obavestenja/' . $obavestenje->id . '/toggle') }}" class="btn btn-sm btn-warning">
{{ $obavestenje->aktivan ? 'Деактивирај' : 'Активирај' }}
</a>
<form action="{{ url('/obavestenja/' . $obavestenje->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Да ли сте сигурни?')">Обриши</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-info mt-4">
            Нема обавештења.
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('obavestenja.create') }}" class="btn btn-success">Додај обавештење</a>
        <a href="{{ route('obavestenja.javna') }}" class="btn btn-info">Jавна обавештења</a>
    </div>
</div>
@endsection
