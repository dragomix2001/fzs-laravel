@extends('layouts.layout')
@section('page_heading','Измени корисника')
@section('section')

<div class="col-sm-12 col-lg-10">
    <form method="POST" action="{{ route('user.update', $user->id) }}">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>Име *</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>
                <div class="form-group">
                    <label>Нова лозинка (празно ако не мењате)</label>
                    <input type="password" name="password" class="form-control" minlength="8">
                </div>
                <div class="form-group">
                    <label>Потврди лозинку</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <label>Улога *</label>
                    <select name="role" class="form-control" required>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Админ</option>
                        <option value="professor" {{ $user->role == 'professor' ? 'selected' : '' }}>Професор</option>
                        <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>Студент</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сачувај</button>
                <a href="{{ route('user.index') }}" class="btn btn-secondary">Откажи</a>
            </div>
        </div>
    </form>
</div>
@endsection
