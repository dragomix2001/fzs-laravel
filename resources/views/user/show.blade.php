@extends('layouts.layout')
@section('page_heading','Корисник')
@section('section')

<div class="col-sm-12 col-lg-10">
    <div class="card">
        <div class="card-body">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <td>{{ $user->id }}</td>
                </tr>
                <tr>
                    <th>Име</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th>Улога</th>
                    <td>
                        @switch($user->role)
                            @case('admin')<span class="badge badge-danger">Админ</span>@break
                            @case('professor')<span class="badge badge-primary">Професор</span>@break
                            @case('student')<span class="badge badge-info">Студент</span>@break
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <th>Статус</th>
                    <td>
                        @if(isset($user->active) && $user->active)
                        <span class="badge badge-success">Активан</span>
                        @else
                        <span class="badge badge-secondary">Неактиван</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Креиран</th>
                    <td>{{ $user->created_at->format('d.m.Y. H:i') }}</td>
                </tr>
            </table>
        </div>
        <div class="card-footer">
            <a href="{{ route('user.index') }}" class="btn btn-secondary">Назад</a>
            <a href="{{ route('user.edit', $user->id) }}" class="btn btn-primary">Измени</a>
        </div>
    </div>
</div>
@endsection
