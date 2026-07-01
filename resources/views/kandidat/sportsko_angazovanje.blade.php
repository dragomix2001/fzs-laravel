@extends('layouts.layout')
@section('page_heading','Спортско ангажовање')
@section('section')
    <div class="max-w-2xl mx-auto space-y-6">
        @if(session('flash-success'))
            <x-alert type="success">{{ session('flash-success') }}</x-alert>
        @endif

        <x-card>
            <x-slot:header>Додај спортско ангажовање</x-slot:header>
            <form method="post" action="{{ url('/kandidat/sportsko-angazovanje') }}" class="space-y-4">
                @csrf
                <x-form-select name="sport" label="Спорт" :options="$sportovi->pluck('naziv', 'id')->toArray()" required />
                <x-form-input name="klub" label="Клуб" required />
                <x-form-input name="uzrast" label="Узраст (од - до)" placeholder="нпр. 12-16" />
                <div class="flex justify-end pt-4">
                    <x-button type="submit">Додај</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
