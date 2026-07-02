@extends('layouts.layout')
@section('page_heading','Аудит лог')
@section('section')

<div class="col-span-12">
    <x-card>
        <form method="GET">
            <div class="flex flex-wrap items-end gap-3">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-secondary-700">Табела:</label>
                    <select name="table_name" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">Све</option>
                        @foreach($tables as $table)
                        <option value="{{ $table }}" {{ request('table_name') == $table ? 'selected' : '' }}>{{ $table }}</option>
                        @endforeach
                    </select>
                </div>
                <x-button variant="primary" size="md" type="submit">
                    <i class="fas fa-filter mr-2"></i> Филтрирај
                </x-button>
            </div>
        </form>
    </x-card>

    <x-card>
        <x-table>
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
                            @case('create')<x-badge variant="success">Креирано</x-badge>@break
                            @case('update')<x-badge variant="info">Ажурирано</x-badge>@break
                            @case('delete')<x-badge variant="danger">Обрисано</x-badge>@break
                            @default<x-badge variant="secondary">{{ $log->action }}</x-badge>
                        @endswitch
                    </td>
                    <td>{{ $log->table_name }}</td>
                    <td>{{ $log->record_id }}</td>
                    <td><code class="text-xs bg-secondary-100 px-1.5 py-0.5 rounded">{{ $log->ip_address }}</code></td>
                    <td class="text-sm text-secondary-500">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-card>
</div>
@endsection
