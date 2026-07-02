@extends('layouts.layout')
@section('page_heading','Корисник')
@section('section')

<div class="col-span-12 lg:col-span-10">
    <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
        <div class="p-4">
            <table class="min-w-full divide-y divide-secondary-200">
                <tbody class="divide-y divide-secondary-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-secondary-700 w-1/4">ID</th>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-secondary-700">Име</th>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-secondary-700">Email</th>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-secondary-700">Улога</th>
                        <td class="px-4 py-3 text-sm">
                            @switch($user->role)
                                @case('admin')<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700 ring-1 ring-inset ring-danger-600/20">Админ</span>@break
                                @case('professor')<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700 ring-1 ring-inset ring-primary-600/20">Професор</span>@break
                                @case('student')<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700 ring-1 ring-inset ring-cyan-600/20">Студент</span>@break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-secondary-700">Статус</th>
                        <td class="px-4 py-3 text-sm">
                            @if(isset($user->active) && $user->active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700 ring-1 ring-inset ring-success-600/20">Активан</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-700 ring-1 ring-inset ring-secondary-600/20">Неактиван</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-secondary-700">Креиран</th>
                        <td class="px-4 py-3 text-sm text-secondary-700">{{ $user->created_at->format('d.m.Y. H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-secondary-200 bg-secondary-50 flex gap-2">
            <a href="{{ route('user.index') }}" class="inline-flex items-center px-4 py-2 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 text-sm font-medium rounded-lg transition-colors">Назад</a>
            <a href="{{ route('user.edit', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">Измени</a>
        </div>
    </div>
</div>
@endsection
