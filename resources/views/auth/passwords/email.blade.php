@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-secondary-200 bg-secondary-50">
            <h4 class="text-lg font-semibold text-secondary-900">Reset Password</h4>
        </div>
        <div class="p-6">
            @if (session('status'))
                <div class="rounded-lg bg-success-50 border border-success-200 p-4 mb-4 text-sm text-success-800" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ url('/password/email') }}">
                {!! csrf_field() !!}

                <div class="mb-4">
                    <label class="block text-sm font-medium text-secondary-700 mb-1">E-Mail Address</label>
                    <input type="email" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm @if($errors->has('email')) border-danger-300 @endif" name="email" value="{{ old('email') }}">
                    @if ($errors->has('email'))
                        <p class="mt-1 text-sm text-danger-600">{{ $errors->first('email') }}</p>
                    @endif
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-envelope mr-2"></i> Send Password Reset Link
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
