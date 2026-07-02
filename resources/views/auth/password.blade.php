@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-secondary-200 bg-secondary-50">
            <h4 class="text-lg font-semibold text-secondary-900">Ресетовање лозинке</h4>
        </div>
        <div class="p-6">
            @if (session('status'))
                <div class="rounded-lg bg-success-50 border border-success-200 p-4 mb-4 text-sm text-success-800" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            @if (count($errors) > 0)
                <div class="rounded-lg bg-danger-50 border border-danger-200 p-4 mb-4" role="alert">
                    <strong class="text-sm font-medium text-danger-800">Грешка!</strong>
                    <p class="text-sm text-danger-700 mt-1">Постоји проблем код вашег уноса.</p>
                    <ul class="mt-2 text-sm text-danger-600 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/password/email">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-secondary-700 mb-1">E-Mail Адреса</label>
                    <input type="email" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="email" value="{{ old('email') }}">
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                    Пошаљи линк за ресетовање лозинке
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
