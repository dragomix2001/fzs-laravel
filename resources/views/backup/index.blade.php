@extends('layouts.layout')
@section('page_heading','Резервне копије')
@section('section')

<div class="col-sm-12 col-lg-10">
    <div class="card mb-4">
        <div class="card-header">
            <h4>Креирај нову резервну копију</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backup.create') }}" class="d-inline">
                @csrf
                <div class="btn-group" role="group">
                    <button type="submit" name="type" value="full" class="btn btn-primary">Пуна копија (DB + FAJLOVI)</button>
                    <button type="submit" name="type" value="database" class="btn btn-info">Само база</button>
                    <button type="submit" name="type" value="files" class="btn btn-secondary">Само фајлови</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Постојеће резервне копије</h4>
        </div>
        <div class="card-body">
            @if(count($backups) > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Име фајла</th>
                        <th>Величина</th>
                        <th>Датум</th>
                        <th>Акције</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr>
                        <td>{{ $backup['name'] }}</td>
                        <td>{{ round($backup['size'] / 1024, 2) }} KB</td>
                        <td>{{ $backup['modified'] }}</td>
                        <td>
                            <a href="{{ route('backup.download', ['filename' => $backup['name']]) }}" class="btn btn-sm btn-success">Преузми</a>
                            <form method="POST" action="{{ route('backup.delete', ['filename' => $backup['name']]) }}" class="d-inline">
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
            <div class="alert alert-info">Нема резервних копија</div>
            @endif
        </div>
    </div>
</div>
@endsection
