@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Евиденција присуства на настави</h2>
    
    <form method="GET" action="{{ route('prisustvo.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Предмет</label>
                <select name="predmet" class="form-control">
                    <option value="">-- Изаберите предмет --</option>
                    @foreach($predmeti as $predmet)
                        <option value="{{ $predmet->id }}">{{ $predmet->naziv }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Наставна недеља</label>
                <select name="nedelja" class="form-control">
                    <option value="">-- Изаберите недељу --</option>
                    @foreach($nedelje as $nedelja)
                        <option value="{{ $nedelja->id }}">Недеља {{ $nedelja->redni_broj }} ({{ $nedelja->datum_pocetka }} - {{ $nedelja->datum_kraja }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Прикажи</button>
            </div>
        </div>
    </form>

    @if($prisanstva)
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Број индекса</th>
                    <th>Име и презиме</th>
                    <th>Статус</th>
                    <th>Напомена</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prisanstva as $prisanstvo)
                <tr>
                    <td>{{ $prisanstvo->student->brojIndeksa }}</td>
                    <td>{{ $prisanstvo->student->ime }} {{ $prisanstvo->student->prezimeKandidata }}</td>
                    <td>
                        @if($prisanstvo->status == 'prisutan')
                            <span class="label label-success">Присутан</span>
                        @elseif($prisanstvo->status == 'odsutan')
                            <span class="label label-danger">Одсутан</span>
                        @elseif($prisanstvo->status == 'opravdan')
                            <span class="label label-warning">Оправдан</span>
                        @else
                            <span class="label label-info">Каснио</span>
                        @endif
                    </td>
                    <td>{{ $prisanstvo->napomena }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-4">
        <a href="{{ route('prisustvo.create') }}" class="btn btn-success">Унеси присуство</a>
        <a href="{{ route('prisustvo.report') }}" class="btn btn-info">Извештај</a>
    </div>
</div>
@endsection
