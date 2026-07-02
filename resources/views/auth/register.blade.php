@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-secondary-200 bg-secondary-50">
            <h4 class="text-lg font-semibold text-secondary-900">Регистрација</h4>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ url('/register') }}">
                {!! csrf_field() !!}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Корисничко Име</label>
                        <input type="text" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @if($errors->has('name')) border-danger-300 @endif" name="name" value="{{ old('name') }}">
                        @if ($errors->has('name'))
                            <p class="mt-1 text-sm text-danger-600">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">E-Mail Адреса</label>
                        <input type="email" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @if($errors->has('email')) border-danger-300 @endif" name="email" value="{{ old('email') }}">
                        @if ($errors->has('email'))
                            <p class="mt-1 text-sm text-danger-600">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Лозинка</label>
                        <input type="password" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @if($errors->has('password')) border-danger-300 @endif" name="password">
                        @if ($errors->has('password'))
                            <p class="mt-1 text-sm text-danger-600">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Поновљена Лозинка</label>
                        <input type="password" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @if($errors->has('password_confirmation')) border-danger-300 @endif" name="password_confirmation">
                        @if ($errors->has('password_confirmation'))
                            <p class="mt-1 text-sm text-danger-600">{{ $errors->first('password_confirmation') }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <x-button variant="primary" size="md" type="submit">
                        <i class="fas fa-user mr-2"></i> Регистрација
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
