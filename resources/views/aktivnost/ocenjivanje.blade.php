@extends('layouts.layout')
@section('page_heading','Оцењивање')
@section('section')

<div class="col-sm-12 col-lg-10">
    <h2>Оцењивање: {{ $aktivnost->naziv }}</h2>
    
    <div class="alert alert-info">
        <strong>Максимално бодова:</strong> {{ $aktivnost->max_bodova }}
    </div>

    <form action="{{ route('aktivnost.saveOcenjivanje', $aktivnost->id) }}" method="POST">
        @csrf
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Број индекса</th>
                    <th>Име и презиме</th>
                    <th>Освојени бодови</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studenti as $student)
                <tr>
                    <td>{{ $student->brojIndeksa }}</td>
                    <td>{{ $student->ime }} {{ $student->prezime }}</td>
                    <td>
                        <input type="number" 
                               name="bodovi[{{ $student->id }}]" 
                               value="{{ $ocene[$student->id] ?? '' }}" 
                               class="form-control" 
                               min="0" 
                               max="{{ $aktivnost->max_bodova }}" 
                               step="0.01">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 mb-5">
            <button type="submit" class="btn btn-success">Сачувај оцене</button>
            <a href="{{ route('aktivnost.index') }}" class="btn btn-secondary">Одустани</a>
        </div>
    </form>
</div>
@endsection
