@extends('layouts.layout')
@section('page_heading','Измени корисника')
@section('section')

<div class="col-span-12 lg:col-span-10 max-w-2xl">
    <form method="POST" action="{{ route('user.update', $user->id) }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Име *</label>
                    <input type="text" name="name" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ $user->name }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Email *</label>
                    <input type="email" name="email" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" value="{{ $user->email }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Нова лозинка (празно ако не мењате)</label>
                    <input type="password" name="password" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" minlength="8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Потврди лозинку</label>
                    <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Улога *</label>
                    <select name="role" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Админ</option>
                        <option value="professor" {{ $user->role == 'professor' ? 'selected' : '' }}>Професор</option>
                        <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>Студент</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-3 border-t border-secondary-200 bg-secondary-50 flex gap-2">
                <x-button variant="primary" size="md" type="submit">Сачувај</x-button>
                <x-button variant="secondary-soft" size="md" href="{{ route('user.index') }}">Откажи</x-button>
            </div>
        </div>
    </form>
</div>
@endsection
