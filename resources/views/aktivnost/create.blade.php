@extends('layouts.layout')
@section('page_heading','Нова активност')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Додавање нове активности</h2>

    <form action="{{ route('aktivnost.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-select label="Предмет" name="predmet_id" required
                           :options="collect(['' => '-- Изаберите предмет --'] + $predmeti->pluck('naziv','id')->toArray())->toArray()" />

            <x-form-input label="Назив активности" name="naziv" type="text" required />

            <x-form-select label="Тип активности" name="tip" required
                           :options="['' => '-- Изаберите тип --', 'kolokvijum' => 'Колоквијум', 'seminarski' => 'Семинарски рад', 'prisustvo' => 'Присуство', 'aktivnost' => 'Активност на часу', 'ostalo' => 'Остало']" />

            <div>
                <label for="max_bodova" class="block text-sm font-medium text-secondary-700 mb-1">Максимално бодова <span class="text-danger-600">*</span></label>
                <input type="number" name="max_bodova" id="max_bodova" required min="1" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <x-form-input label="Датум" name="datum" type="date" required />
        </div>

        <div class="mt-6 flex gap-2">
            <x-button variant="success">Сачувај</x-button>
            <x-button variant="secondary-soft" size="md" href="{{ route('aktivnost.index') }}">Одустани</x-button>
        </div>
    </form>
</div>
@endsection
