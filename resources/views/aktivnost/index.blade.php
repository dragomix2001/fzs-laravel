@extends('layouts.layout')

@section('section')
<div class="container">
    <h2>Континуирано оцењивање</h2>
    
    <a href="{{ route('aktivnost.create') }}" class="btn btn-success mb-3">Нова активност</a>
    <a href="{{ route('aktivnost.rezime') }}" class="btn btn-info mb-3">Преглед свих активности</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Предмет</th>
                <th>Назив</th>
                <th>Тип</th>
                <th>Бодови</th>
                <th>Датум</th>
                <th>Акције</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aktivnosti as $aktivnost)
            <tr>
                <td>{{ $aktivnost->predmet->naziv }}</td>
                <td>{{ $aktivnost->naziv }}</td>
                <td>{{ ucfirst($aktivnost->tip) }}</td>
                <td>{{ $aktivnost->max_bodova }}</td>
                <td>{{ $aktivnost->datum }}</td>
                <td>
                    <a href="{{ route('aktivnost.show', $aktivnost->id) }}" class="btn btn-sm btn-primary">Прикажи</a>
                    <a href="{{ route('aktivnost.ocenjivanje', $aktivnost->id) }}" class="btn btn-sm btn-warning">Оцени</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
