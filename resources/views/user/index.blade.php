@extends('layouts.layout')
@section('page_heading','Корисници')
@section('section')

<div class="col-sm-12 col-lg-10">
    <div class="mb-3">
        <a href="{{ route('user.create') }}" class="btn btn-success">Додај корисника</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Име</th>
                        <th>Email</th>
                        <th>Улога</th>
                        <th>Статус</th>
                        <th>Акције</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @switch($user->role)
                                @case('admin')<span class="badge badge-danger">Админ</span>@break
                                @case('professor')<span class="badge badge-primary">Професор</span>@break
                                @case('student')<span class="badge badge-info">Студент</span>@break
                            @endswitch
                        </td>
                        <td>
                            @if(isset($user->active) && $user->active)
                            <span class="badge badge-success">Активан</span>
                            @else
                            <span class="badge badge-secondary">Неактиван</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('user.show', $user->id) }}" class="btn btn-sm btn-info">Прикажи</a>
                            <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-primary">Измени</a>
                            <a href="{{ route('user.toggle', $user->id) }}" class="btn btn-sm btn-warning">
                                {{ isset($user->active) && $user->active ? 'Деактивирај' : 'Активирај' }}
                            </a>
                            <form method="POST" action="{{ route('user.destroy', $user->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Да ли сте сигурни?')">Обриши</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
