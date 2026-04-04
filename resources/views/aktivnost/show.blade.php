@extends('layouts.layout')
@section('page_heading','Детаљи активности')
@section('section')

<div class="col-sm-12 col-lg-10">
    <h2>{{ $aktivnost->naziv }}</h2>

    <div class="mb-4">
        <p><strong>Предмет:</strong> {{ $aktivnost->predmet->naziv ?? '' }}</p>
        <p><strong>Тип:</strong> {{ ucfirst($aktivnost->tip) }}</p>
        <p><strong>Максимално бодова:</strong> {{ $aktivnost->max_bodova }}</p>
        <p><strong>Датум:</strong> {{ \Carbon\Carbon::parse($aktivnost->datum)->format('d.m.Y.') }}</p>
    </div>

    <a href="{{ route('aktivnost.ocenjivanje', $aktivnost->id) }}" class="btn btn-warning mb-3">Оцени студенте</a>
    <a href="{{ route('aktivnost.index') }}" class="btn btn-secondary mb-3">Назад на листу</a>

    <h4 class="mt-4">Оцене студената</h4>
    
    @if(count($ocene) > 0)
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Студент</th>
                <th>Број индекса</th>
                <th>Освојени бодови</th>
                <th>Оцена</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ocene as $ocena)
            <tr>
                <td>{{ $ocena->student->ime ?? '' }} {{ $ocena->student->prezime ?? '' }}</td>
                <td>{{ $ocena->student->brojIndeksa ?? '' }}</td>
                <td>{{ $ocena->bodovi }}</td>
                <td>{{ $ocena->ocena }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="alert alert-info mt-3">Тренутно нема унетих оцена за ову активност.</div>
    @endif
</div>
@endsection
