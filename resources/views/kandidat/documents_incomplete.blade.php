@extends('layouts.layout')

@section('page_heading', 'Кандидати са непотпуном документацијом')

@section('section')
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Преглед кандидата</h3>
            </div>
            <div class="panel-body table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Кандидат</th>
                        <th>Програм</th>
                        <th>Тип студија</th>
                        <th>Одобрено</th>
                        <th>Недостаје</th>
                        <th>Блокирано review-ом</th>
                        <th>Комплетност</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['kandidat']->imeKandidata }} {{ $row['kandidat']->prezimeKandidata }}</strong><br>
                                <small>ЈМБГ: {{ $row['kandidat']->jmbg }}</small>
                            </td>
                            <td>{{ $row['kandidat']->program?->naziv ?? '-' }}</td>
                            <td>{{ $row['kandidat']->tipStudija?->skrNaziv ?? '-' }}</td>
                            <td>{{ $row['completion']['approved_required_count'] }}/{{ $row['completion']['required_count'] }}</td>
                            <td>{{ $row['completion']['missing_count'] }}</td>
                            <td>{{ $row['completion']['review_blocked_count'] }}</td>
                            <td>{{ $row['completion']['completion_percentage'] }}%</td>
                            <td>
                                <a href="{{ route('kandidat.documents.review', $row['kandidat']) }}" class="btn btn-primary btn-sm">
                                    Преглед документације
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Нема кандидата са непотпуном документацијом.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection