@extends('layouts.layout')

@section('page_heading', 'Преглед документације кандидата')

@section('section')
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Кандидат</h3>
            </div>
            <div class="panel-body">
                <div class="clearfix" style="margin-bottom: 15px;">
                    <a href="{{ route('kandidat.documents.incomplete') }}" class="btn btn-default btn-sm pull-right">Назад на непотпуну документацију</a>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <p><strong>{{ $kandidat->imeKandidata }} {{ $kandidat->prezimeKandidata }}</strong></p>
                        <p>ЈМБГ: {{ $kandidat->jmbg }}</p>
                        <p>Студијски програм: {{ $kandidat->program?->naziv ?? '-' }}</p>
                        <p>Тип студија: {{ $kandidat->tipStudija?->naziv ?? '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <ul class="list-group">
                            <li class="list-group-item">Укупно докумената: <strong>{{ $summary['total'] }}</strong></li>
                            <li class="list-group-item">Обавезних докумената: <strong>{{ $completion['required_count'] }}</strong></li>
                            <li class="list-group-item">Комплетност: <strong>{{ $completion['completion_percentage'] }}%</strong></li>
                            <li class="list-group-item">На чекању: <strong>{{ $summary['pending'] }}</strong></li>
                            <li class="list-group-item">Одобрено: <strong>{{ $summary['approved'] }}</strong></li>
                            <li class="list-group-item">Одбијено: <strong>{{ $summary['rejected'] }}</strong></li>
                            <li class="list-group-item">Тражи допуну: <strong>{{ $summary['needs_revision'] }}</strong></li>
                            <li class="list-group-item">Недостаје обавезних: <strong>{{ $completion['missing_count'] }}</strong></li>
                            <li class="list-group-item">Блокирано review-ом: <strong>{{ $completion['review_blocked_count'] }}</strong></li>
                        </ul>
                    </div>
                </div>
                @if($completion['missing_count'] > 0)
                    <hr>
                    <p><strong>Недостајућа обавезна документа:</strong></p>
                    <ul>
                        @foreach($completion['missing_documents'] as $missingDocument)
                            <li>{{ $missingDocument->naziv }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Документа</h3>
            </div>
            <div class="panel-body table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Документ</th>
                        <th>Група</th>
                        <th>Статус</th>
                        <th>Прегледао</th>
                        <th>Напомена</th>
                        <th>Акције</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($dokumenta as $attachment)
                        <tr>
                            <td>{{ $attachment->dokument?->naziv ?? 'Непознат документ' }}</td>
                            <td>{{ $attachment->dokument?->skolskaGodina_id ?? '-' }}</td>
                            <td>
                                <span class="label label-{{ match($attachment->review_status) {
                                    \App\Models\KandidatPrilozenaDokumenta::STATUS_APPROVED => 'success',
                                    \App\Models\KandidatPrilozenaDokumenta::STATUS_REJECTED => 'danger',
                                    \App\Models\KandidatPrilozenaDokumenta::STATUS_NEEDS_REVISION => 'warning',
                                    default => 'default',
                                } }}">
                                    {{ $attachment->review_status }}
                                </span>
                            </td>
                            <td>
                                {{ $attachment->reviewer?->name ?? '-' }}
                                @if($attachment->reviewed_at)
                                    <br>
                                    <small>{{ $attachment->reviewed_at->format('d.m.Y. H:i') }}</small>
                                @endif
                            </td>
                            <td>{{ $attachment->notes ?: '-' }}</td>
                            <td style="min-width: 280px;">
                                <form method="POST" action="{{ route('kandidat.documents.approve', [$kandidat, $attachment]) }}" style="margin-bottom: 8px;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">Одобри</button>
                                </form>

                                <form method="POST" action="{{ route('kandidat.documents.reject', [$kandidat, $attachment]) }}" style="margin-bottom: 8px;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="notes" class="form-control input-sm" placeholder="Разлог одбијања" value="{{ old('notes') }}" style="margin-bottom: 6px;">
                                    <button type="submit" class="btn btn-danger btn-sm">Одбиј</button>
                                </form>

                                <form method="POST" action="{{ route('kandidat.documents.needs-revision', [$kandidat, $attachment]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="notes" class="form-control input-sm" placeholder="Шта треба допунити" value="{{ old('notes') }}" style="margin-bottom: 6px;">
                                    <button type="submit" class="btn btn-warning btn-sm">Тражи допуну</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Кандидат нема евидентирана документа.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection