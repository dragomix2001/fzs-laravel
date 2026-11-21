@extends('layouts.layout')
@section('page_heading','Аудит лог')
@section('section')

<div class="col-span-12">
    <x-card>
        <form method="GET" action="{{ route('audit.index') }}">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-6">
                <div class="space-y-1">
                    <label for="table_name" class="block text-sm font-medium text-secondary-700">Табела</label>
                    <select id="table_name" name="table_name" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">Све</option>
                        @foreach($tables as $table)
                        <option value="{{ $table }}" @selected(($filters['table_name'] ?? '') === $table)>{{ $table }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="user_id" class="block text-sm font-medium text-secondary-700">Корисник</label>
                    <select id="user_id" name="user_id" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">Сви корисници</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="action" class="block text-sm font-medium text-secondary-700">Акција</label>
                    <select id="action" name="action" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">Све акције</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="record_id" class="block text-sm font-medium text-secondary-700">ID записа</label>
                    <input id="record_id" name="record_id" type="number" min="1" value="{{ $filters['record_id'] ?? '' }}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <div class="space-y-1">
                    <label for="date_from" class="block text-sm font-medium text-secondary-700">Од датума</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <div class="space-y-1">
                    <label for="date_to" class="block text-sm font-medium text-secondary-700">До датума</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-button variant="primary" size="md" type="submit">
                    <i class="fas fa-filter mr-2"></i> Филтрирај
                </x-button>
                <x-button variant="secondary-soft" size="md" href="{{ route('audit.index') }}">
                    <i class="fas fa-undo mr-2"></i> Ресетуј
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
                    <th>Промене</th>
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
                        @switch(strtolower($log->action))
                            @case('create')<x-badge variant="success">Креирано</x-badge>@break
                            @case('update')<x-badge variant="info">Ажурирано</x-badge>@break
                            @case('delete')<x-badge variant="danger">Обрисано</x-badge>@break
                            @case('view')<x-badge variant="secondary">Прегледано</x-badge>@break
                            @default<x-badge variant="secondary">{{ $log->action }}</x-badge>
                        @endswitch
                    </td>
                    <td>{{ $log->table_name }}</td>
                    <td>{{ $log->record_id }}</td>
                    <td>
                        @php($changes = $log->changes())
                        @if($changes)
                            <details>
                                <summary class="cursor-pointer text-primary-700">{{ count($changes) }} поља</summary>
                                <dl class="mt-2 space-y-2 text-xs">
                                    @foreach($changes as $field => $change)
                                        <div>
                                            <dt class="font-semibold">{{ $field }}</dt>
                                            <dd class="text-secondary-600">
                                                <span>{{ $change['old_exists'] ? json_encode($change['old'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—' }}</span>
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                <span>{{ $change['new_exists'] ? json_encode($change['new'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '—' }}</span>
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </details>
                        @else
                            <span class="text-secondary-400">—</span>
                        @endif
                    </td>
                    <td><code class="text-xs bg-secondary-100 px-1.5 py-0.5 rounded">{{ $log->ip_address }}</code></td>
                    <td class="text-sm text-secondary-500">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                </tr>
                @endforeach
                @if($logs->isEmpty())
                    <tr>
                        <td colspan="8" class="py-6 text-center text-secondary-500">Нема резултата за изабране филтере.</td>
                    </tr>
                @endif
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-card>
</div>
@endsection
