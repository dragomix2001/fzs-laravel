@extends('layouts.layout')
@section('page_heading','Унос присуства')
@section('section')

<div class="col-span-12 lg:col-span-10">
    <h2 class="text-xl font-semibold text-secondary-900 mb-4">Унос присуства на настави</h2>

    <form method="GET" action="{{ route('prisustvo.create') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Предмет</label>
                <select name="predmet" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" onchange="this.form.submit()">
                    <option value="">-- Изаберите предмет --</option>
                    @foreach($predmeti as $predmet)
                        <option value="{{ $predmet->id }}" {{ request('predmet') == $predmet->id ? 'selected' : '' }}>
                            {{ $predmet->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <noscript><x-button variant="primary" size="md" type="submit" class="w-full">Учитај студенте</x-button></noscript>
            </div>
        </div>
    </form>

    @if(!empty($studenti) && count($studenti) > 0)
        <form method="POST" action="{{ route('prisustvo.store') }}">
            @csrf
            <input type="hidden" name="predmet_id" value="{{ request('predmet') }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Наставна недеља</label>
                    <select name="nastavna_nedelja_id" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" required>
                        <option value="">-- Изаберите недељу --</option>
                        @foreach($nedelje as $nedelja)
                            <option value="{{ $nedelja->id }}">Недеља {{ $nedelja->redni_broj }} ({{ $nedelja->datum_pocetka }} - {{ $nedelja->datum_kraja }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-secondary-200 shadow-sm mt-4">
                <table class="min-w-full divide-y divide-secondary-200">
                    <thead class="bg-secondary-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Изабери</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број индекса</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Име и презиме</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Статус</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Напомена</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-200">
                        @foreach($studenti as $student)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" checked class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->brojIndeksa }}</td>
                            <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->ime }} {{ $student->prezimeKandidata }}</td>
                            <td class="px-4 py-3">
                                <select name="status[{{ $student->id }}]" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    <option value="prisutan">Присутан</option>
                                    <option value="odsutan">Одсутан</option>
                                    <option value="opravdano">Оправдано</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="napomena[{{ $student->id }}]" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Напомена...">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2">
                <x-button variant="success" size="md">Сачувај присуство</x-button>
                <x-button variant="secondary-soft" size="md" href="{{ route('prisustvo.index') }}">Одустани</x-button>
            </div>
        </form>
    @else
        @if(request('predmet'))
            <div class="rounded-lg bg-primary-50 border border-primary-200 p-4 mt-4 text-sm text-primary-800" role="alert">Нема студената за изабрани предмет.</div>
        @endif
    @endif
</div>
@endsection
