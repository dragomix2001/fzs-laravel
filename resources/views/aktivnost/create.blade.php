@extends('layouts.layout')
@section('page_heading','Нова активност')
@section('section')

<div class="col-sm-12 col-lg-10">
    <h2>Додавање нове активности</h2>

    <form action="{{ route('aktivnost.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="predmet_id">Предмет</label>
            <select name="predmet_id" id="predmet_id" class="form-control" required>
                <option value="">-- Изаберите предмет --</option>
                @foreach($predmeti as $predmet)
                    <option value="{{ $predmet->id }}">{{ $predmet->naziv }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="naziv">Назив активности</label>
            <input type="text" name="naziv" id="naziv" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label for="tip">Тип активности</label>
            <select name="tip" id="tip" class="form-control" required>
                <option value="">-- Изаберите тип --</option>
                <option value="kolokvijum">Колоквијум</option>
                <option value="seminarski">Семинарски рад</option>
                <option value="prisustvo">Присуство</option>
                <option value="aktivnost">Активност на часу</option>
                <option value="ostalo">Остало</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="max_bodova">Максимално бодова</label>
            <input type="number" name="max_bodova" id="max_bodova" class="form-control" required min="1">
        </div>

        <div class="form-group mb-3">
            <label for="datum">Датум</label>
            <input type="date" name="datum" id="datum" class="form-control" required>
        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-success">Сачувај</button>
            <a href="{{ route('aktivnost.index') }}" class="btn btn-secondary">Одустани</a>
        </div>
    </form>
</div>
@endsection
