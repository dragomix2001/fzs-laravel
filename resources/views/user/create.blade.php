@extends('layouts.layout')
@section('page_heading','Нови корисник')
@section('section')

<div class="col-span-12 lg:col-span-10 max-w-2xl">
    <form method="POST" action="{{ route('user.store') }}">
        @csrf
        <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Име *</label>
                    <input type="text" name="name" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Email *</label>
                    <input type="email" name="email" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Лозинка *</label>
                    <input type="password" name="password" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required minlength="8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Потврди лозинку *</label>
                    <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Улога *</label>
                    <select name="role" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                        <option value="">Одаберите улогу</option>
                        <option value="admin">Админ</option>
                        <option value="professor">Професор</option>
                        <option value="student">Студент</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-3 border-t border-secondary-200 bg-secondary-50 flex gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">Креирај</button>
                <a href="{{ route('user.index') }}" class="inline-flex items-center px-4 py-2 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 text-sm font-medium rounded-lg transition-colors">Откажи</a>
            </div>
        </div>
    </form>
</div>
@endsection
