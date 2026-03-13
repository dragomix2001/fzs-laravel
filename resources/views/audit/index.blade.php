@extends('layouts.layout')
@section('page_heading','Аудит лог')
@section('section')

<div class="col-sm-12 col-lg-12">
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label>Табела:</label>
                    <select name="table_name" class="form-control">
                        <option value="">Све</option>
                        @foreach($tables as $table)
                        <option value="{{ $table }}" {{ request('table_name') == $table ? 'selected' : '' }}>{{ $table }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Филтрирај</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Корисник</th>
                        <th>Акција</th>
                        <th>Табела</th>
                        <th>Запис</th>
                        <th>IP</th>
                        <th>Време</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->user->name ?? 'Систем' }}</td>
                        <td>
                            @switch($log->action)
                                @case('create')<span class="badge badge-success">Креирано</span>@break
                                @case('update')<span class="badge badge-info">Ажурирано</span>@break
                                @case('delete')<span class="badge badge-danger">Обрисано</span>@break
                                @default{{ $log->action }}
                            @endswitch
                        </td>
                        <td>{{ $log->table_name }}</td>
                        <td>{{ $log->record_id }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
