@extends('layouts.layout')
@section('page_heading','Детаљи кандидата')
@section('section')
    <div class="max-w-4xl">
        <x-card>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                @foreach($kandidat as $key => $value)
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-secondary-500">{{ $key }}</dt>
                        <dd class="mt-1 text-sm text-secondary-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>
    </div>
@endsection
