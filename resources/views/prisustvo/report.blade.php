@extends('layouts.layout')
@section('page_heading','Извештај о присуству')
@section('section')

<div class="col-span-12 lg:col-span-10">
    <h2 class="text-xl font-semibold text-secondary-900 mb-4">Извештај о присуству студената</h2>
    
    <div class="mb-4">
        <x-button variant="secondary-soft" size="md" href="{{ route('prisustvo.index') }}">Назад на евиденцију</x-button>
    </div>

    @if($prisanstva && $prisanstva->count() > 0)
        <div class="overflow-x-auto rounded-lg border border-secondary-200 shadow-sm mt-4">
            <table class="min-w-full divide-y divide-secondary-200">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Број индекса</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Име и презиме</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Евиденција присуства по недељама</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-secondary-200">
                    @foreach($studenti as $student)
                        @if($prisanstva->has($student->id))
                        <tr class="hover:bg-secondary-50">
                            <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->brojIndeksa }}</td>
                            <td class="px-4 py-3 text-sm text-secondary-700">{{ $student->ime }} {{ $student->prezimeKandidata }}</td>
                            <td class="px-4 py-3 text-sm">
                                <ul class="space-y-1 mb-0">
                                    @foreach($prisanstva[$student->id] as $prisanstvo)
                                        <li>
                                            <strong class="text-secondary-700">Недеља {{ $prisanstvo->nastavnaNedelja->redni_broj }}:</strong>
                                            @if($prisanstvo->status == 'prisutan')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700 ring-1 ring-inset ring-success-600/20">Присутан</span>
                                            @elseif($prisanstvo->status == 'odsutan')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700 ring-1 ring-inset ring-danger-600/20">Одсутан</span>
                                            @elseif($prisanstvo->status == 'opravdano')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-700 ring-1 ring-inset ring-warning-600/20">Оправдано</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700 ring-1 ring-inset ring-primary-600/20">{{ ucfirst($prisanstvo->status) }}</span>
                                            @endif
                                            @if($prisanstvo->napomena)
                                                <small class="text-secondary-500">({{ $prisanstvo->napomena }})</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-lg bg-primary-50 border border-primary-200 p-4 mt-4 text-sm text-primary-800" role="alert">Нема података о присуству за изабрани предмет.</div>
    @endif
</div>
@endsection
