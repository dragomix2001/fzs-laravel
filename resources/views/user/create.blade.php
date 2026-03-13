@extends('layouts.layout')
@section('page_heading','Нови корисник')
@section('section')

<div class="col-sm-12 col-lg-10">
    <form method="POST" action="{{ route('user.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>Име *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Лозинка *</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Потврди лозинку *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Улога *</label>
                    <select name="role" class="form-control" required>
                        <option value="">Одаберите улогу</option>
                        <option value="admin">Админ</option>
                        <option value="professor">Професор</option>
                        <option value="student">Студент</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Креирај</button>
                <a href="{{ route('user.index') }}" class="btn btn-secondary">Откажи</a>
            </div>
        </div>
    </form>
</div>
@endsection
