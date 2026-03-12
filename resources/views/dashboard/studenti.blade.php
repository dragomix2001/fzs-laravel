@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Преглед студената</h2>

    <form method="GET" action="{{ route('dashboard.studenti') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Студијски програм</label>
                <select name="program_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Сви програми --</option>
                    @foreach($programi as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Година уписа</label>
                <select name="godina_id" class="form-control" onchange="this.form.submit()">
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

    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Број индекса</th>
                <th>Име и презиме</th>
                <th>Студијски програм</th>
                <th>Година уписа</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse($studenti as $student)
            <tr>
                <td>{{ $student->brojIndeksa }}</td>
                <td>{{ $student->ime }} {{ $student->prezimeKandidata }}</td>
                <td>{{ $student->studijskiProgram->naziv ?? '-' }}</td>
                <td>{{ $student->godinaUpisa->godina ?? '-' }}/{{ ($student->godinaUpisa->godina ?? 0) + 1 }}</td>
                <td>{{ $student->email }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Нема студената</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">Назад</a>
    </div>
</div>
@endsection
