@extends('layouts.layout')
@section('page_heading','Преглед студената')
@section('section')

<div class="col-span-12 lg:col-span-10">
<h2 class="text-xl font-semibold text-secondary-900 mb-4">Преглед студената</h2>

    <form method="GET" action="{{ route('dashboard.studenti') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Студијски програм</label>
                <select name="program_id" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" onchange="this.form.submit()">
                    <option value="">-- Сви програми --</option>
                    @foreach($programi as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Година уписа</label>
                <select name="godina_id" class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" onchange="this.form.submit()">
                    <option value="">-- Све године --</option>
                    @foreach($godine as $godina)
                        <option value="{{ $godina->id }}" {{ $godinaId == $godina->id ? 'selected' : '' }}>
                            {{ $godina->godina }}/{{ $godina->godina + 1 }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-secondary-200 shadow-sm mt-4">
        <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број индекса</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Име и презиме</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Студијски програм</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Година уписа</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Email</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-secondary-200">
                @forelse($studenti as $student)
                <tr class="hover:bg-secondary-50">
                    <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->brojIndeksa }}</td>
                    <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->ime }} {{ $student->prezimeKandidata }}</td>
                    <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->studijskiProgram->naziv ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->godinaUpisa->godina ?? '-' }}/{{ ($student->godinaUpisa->godina ?? 0) + 1 }}</td>
                    <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->email }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-3 text-sm text-secondary-500 text-center">Нема студената</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <x-button variant="secondary-soft" size="md" href="{{ route('dashboard.index') }}">Назад</x-button>
    </div>
</div>
@endsection
