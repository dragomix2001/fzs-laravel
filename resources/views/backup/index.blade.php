@extends('layouts.layout')
@section('page_heading','Резервне копије')
@section('section')

<div class="col-span-12 lg:col-span-10">
    <x-card>
        <x-slot:title>Креирај нову резервну копију</x-slot:title>
        <form method="POST" action="{{ route('backup.create') }}">
            @csrf
            <div class="flex flex-wrap gap-2">
                <button type="submit" name="type" value="full" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-database mr-2"></i> Пуна копија (DB + FAJLOVI)
                </button>
                <button type="submit" name="type" value="database" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-server mr-2"></i> Само база
                </button>
                <button type="submit" name="type" value="files" class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-500 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-folder mr-2"></i> Само фајлови
                </button>
            </div>
        </form>
    </x-card>

    <x-card>
        <x-slot:title>Постојеће резервне копије</x-slot:title>
        @if(count($backups) > 0)
        <x-table>
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
                    <td class="text-sm">{{ $backup['name'] }}</td>
                    <td>{{ round($backup['size'] / 1024, 2) }} KB</td>
                    <td>{{ $backup['modified'] }}</td>
                    <td>
                        <div class="flex gap-1">
                            <a href="{{ route('backup.download', ['filename' => $backup['name']]) }}" class="inline-flex items-center px-2.5 py-1.5 bg-success-600 hover:bg-success-500 text-white text-xs font-medium rounded transition-colors">
                                <i class="fas fa-download mr-1"></i> Преузми
                            </a>
                            <form method="POST" action="{{ route('backup.delete', ['filename' => $backup['name']]) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-danger-600 hover:bg-danger-500 text-white text-xs font-medium rounded transition-colors" onclick="return confirm('Да ли сте сигурни?')">
                                    <i class="fas fa-trash mr-1"></i> Обриши
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </x-table>
        @else
        <div class="flex items-center gap-2 bg-cyan-50 border border-cyan-200 text-cyan-800 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-info-circle text-cyan-500"></i>
            Нема резервних копија
        </div>
        @endif
    </x-card>
</div>
@endsection
