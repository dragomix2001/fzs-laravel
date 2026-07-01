@extends('layouts.layout')
@section('page_heading','Индекс')
@section('section')
    <div class="max-w-4xl mx-auto space-y-6">
        @if(session('flash-success'))
            <x-alert type="success">{{ session('flash-success') }}</x-alert>
        @endif

        <x-card>
            <x-slot:header>Подаци о индексу</x-slot:header>
            <form method="post" action="{{ url('/kandidat/indeks') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form-input name="brojIndeksa" label="Број индекса" required value="{{ old('brojIndeksa', $indeks->brojIndeksa ?? '') }}" />
                    <x-form-input name="godinaUpisa" label="Година уписа" required value="{{ old('godinaUpisa', $indeks->godinaUpisa ?? '') }}" />
                </div>
                <div class="flex justify-end pt-4">
                    <x-button type="submit">Сачувај</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
