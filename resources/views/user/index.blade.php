@extends('layouts.layout')
@section('page_heading','Корисници')
@section('section')

<div class="col-span-12 lg:col-span-10">
    <div class="mb-4">
        <a href="{{ route('user.create') }}" class="inline-flex items-center px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors">Додај корисника</a>
    </div>

    <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Име</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Улога</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Акције</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-secondary-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $user->id }}</td>
                        <td class="px-4 py-3 text-sm text-secondary-900 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-sm">
                            @switch($user->role)
                                @case('admin')<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700 ring-1 ring-inset ring-danger-600/20">Админ</span>@break
                                @case('professor')<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700 ring-1 ring-inset ring-primary-600/20">Професор</span>@break
                                @case('student')<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700 ring-1 ring-inset ring-cyan-600/20">Студент</span>@break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if(isset($user->active) && $user->active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700 ring-1 ring-inset ring-success-600/20">Активан</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-700 ring-1 ring-inset ring-secondary-600/20">Неактиван</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex gap-1 flex-wrap">
                                <a href="{{ route('user.show', $user->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-medium rounded-lg transition-colors">Прикажи</a>
                                <a href="{{ route('user.edit', $user->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded-lg transition-colors">Измени</a>
                                <a href="{{ route('user.toggle', $user->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-warning-500 hover:bg-warning-400 text-white text-xs font-medium rounded-lg transition-colors">
                                    {{ isset($user->active) && $user->active ? 'Деактивирај' : 'Активирај' }}
                                </a>
                                <form method="POST" action="{{ route('user.destroy', $user->id) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-danger-600 hover:bg-danger-500 text-white text-xs font-medium rounded-lg transition-colors" onclick="return confirm('Да ли сте сигурни?')">Обриши</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
