@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-secondary-200 bg-secondary-50">
            <h4 class="text-lg font-semibold text-secondary-900">{{ __('API Documentation') }}</h4>
        </div>
        <div class="p-6">
            <p class="text-secondary-600">{{ __('API documentation coming soon.') }}</p>
        </div>
    </div>
</div>
@endsection
