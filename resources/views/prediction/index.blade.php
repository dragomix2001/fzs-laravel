@extends('layouts.layout')

@section('title', 'AI Предикција - Факултет за спорт')

@section('page_heading', 'AI Предикција успеха')

@section('section')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <p class="text-muted">
                AI предикција анализира перформансе студената и предвиђа вероватноћу успеха.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('prediction.statistics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar me-2"></i>
                Статистика класе
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Студенти
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Студент</th>
                            <th>Email</th>
                            <th class="text-center">Акције</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>
                                <strong>{{ $student->prezimeKandidata }} {{ $student->imeKandidata }}</strong>
                            </td>
                            <td>{{ $student->email }}</td>
                            <td class="text-center">
                                <a href="{{ route('prediction.student', $student->id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Анализирај
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
