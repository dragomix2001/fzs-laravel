@extends('layouts.layout')
@section('page_heading','Преглед документације кандидата')
@section('section')
    <div class="space-y-6">
        @if(session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger">{{ session('error') }}</x-alert>
        @endif

        @if($errors->any())
            <x-alert type="danger">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <x-card>
            <x-slot:header>Кандидат</x-slot:header>
            <div class="mb-4 flex justify-end">
                <a href="{{ route('kandidat.documents.incomplete') }}" class="text-sm text-primary-600 hover:text-primary-800">← Назад на непотпуну документацију</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <p class="font-semibold text-lg">{{ $kandidat->imeKandidata }} {{ $kandidat->prezimeKandidata }}</p>
                    <p class="text-sm text-secondary-600">ЈМБГ: {{ $kandidat->jmbg }}</p>
                    <p class="text-sm text-secondary-600">Студијски програм: {{ $kandidat->program?->naziv ?? '-' }}</p>
                    <p class="text-sm text-secondary-600">Тип студија: {{ $kandidat->tipStudija?->naziv ?? '-' }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm">Укупно докумената: <strong>{{ $summary['total'] }}</strong></p>
                    <p class="text-sm">Обавезних: <strong>{{ $completion['required_count'] }}</strong></p>
                    <p class="text-sm">Комплетност: <strong>{{ $completion['completion_percentage'] }}%</strong></p>
                    <p class="text-sm">На чекању: <strong>{{ $summary['pending'] }}</strong></p>
                    <p class="text-sm">Одобрено: <strong>{{ $summary['approved'] }}</strong></p>
                    <p class="text-sm">Одбијено: <strong>{{ $summary['rejected'] }}</strong></p>
                    <p class="text-sm">Тражи допуну: <strong>{{ $summary['needs_revision'] }}</strong></p>
                    <p class="text-sm">Недостаје обавезних: <strong>{{ $completion['missing_count'] }}</strong></p>
                    <p class="text-sm">Блокирано review-ом: <strong>{{ $completion['review_blocked_count'] }}</strong></p>
                </div>
            </div>
            @if($completion['missing_count'] > 0)
                <div class="mt-4">
                    <p class="font-medium text-sm mb-1">Недостајућа обавезна документа:</p>
                    <ul class="list-disc list-inside text-sm text-secondary-600">
                        @foreach($completion['missing_documents'] as $missingDocument)
                            <li>{{ $missingDocument->naziv }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-card>

        <x-card>
            <x-slot:header>Документа</x-slot:header>
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Документ</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Група</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Прегледао</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Напомена</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Акције</th>
                    </tr>
                </x-slot:header>
                @forelse($dokumenta as $attachment)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $attachment->dokument?->naziv ?? 'Непознат документ' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $attachment->dokument?->skolskaGodina_id ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :variant="match($attachment->review_status) {
                                \App\Models\KandidatPrilozenaDokumenta::STATUS_APPROVED => 'success',
                                \App\Models\KandidatPrilozenaDokumenta::STATUS_REJECTED => 'danger',
                                \App\Models\KandidatPrilozenaDokumenta::STATUS_NEEDS_REVISION => 'warning',
                                default => 'secondary',
                            }">{{ $attachment->review_status }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $attachment->reviewer?->name ?? '-' }}
                            @if($attachment->reviewed_at)
                                <br><span class="text-xs text-secondary-400">{{ $attachment->reviewed_at->format('d.m.Y. H:i') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $attachment->notes ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="space-y-2">
                                <form method="POST" action="{{ route('kandidat.documents.approve', [$kandidat, $attachment]) }}">
                                    @csrf @method('PATCH')
                                    <x-button type="submit" variant="success" size="sm">Одобри</x-button>
                                </form>
                                <form method="POST" action="{{ route('kandidat.documents.reject', [$kandidat, $attachment]) }}" class="space-y-1">
                                    @csrf @method('PATCH')
                                    <x-form-input name="notes" placeholder="Разлог одбијања" :value="old('notes')" />
                                    <x-button type="submit" variant="danger" size="sm">Одбиј</x-button>
                                </form>
                                <form method="POST" action="{{ route('kandidat.documents.needs-revision', [$kandidat, $attachment]) }}" class="space-y-1">
                                    @csrf @method('PATCH')
                                    <x-form-input name="notes" placeholder="Шта треба допунити" :value="old('notes')" />
                                    <x-button type="submit" variant="warning" size="sm">Тражи допуну</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm text-secondary-500">Кандидат нема евидентирана документа.</td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
@endsection
