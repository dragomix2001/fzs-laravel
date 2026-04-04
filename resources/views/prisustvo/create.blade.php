@extends('layouts.layout')
@section('page_heading','Унос присуства')
@section('section')

<div class="col-sm-12 col-lg-10">
    <h2>Унос присуства на настави</h2>

    <form method="GET" action="{{ route('prisustvo.create') }}" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <label>Предмет</label>
                <select name="predmet" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Изаберите предмет --</option>
                    @foreach($predmeti as $predmet)
                        <option value="{{ $predmet->id }}" {{ request('predmet') == $predmet->id ? 'selected' : '' }}>
                            {{ $predmet->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label>&nbsp;</label>
                <noscript><button type="submit" class="btn btn-primary btn-block">Учитај студенте</button></noscript>
            </div>
        </div>
    </form>

    @if(!empty($studenti) && count($studenti) > 0)
        <form method="POST" action="{{ route('prisustvo.store') }}">
            @csrf
            <input type="hidden" name="predmet_id" value="{{ request('predmet') }}">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label>Наставна недеља</label>
                    <select name="nastavna_nedelja_id" class="form-control" required>
                        <option value="">-- Изаберите недељу --</option>
                        @foreach($nedelje as $nedelja)
                            <option value="{{ $nedelja->id }}">Недеља {{ $nedelja->redni_broj }} ({{ $nedelja->datum_pocetka }} - {{ $nedelja->datum_kraja }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Изабери</th>
                        <th>Број индекса</th>
                        <th>Име и презиме</th>
                        <th>Статус</th>
                        <th>Напомена</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studenti as $student)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" checked>
                        </td>
                        <td>{{ $student->brojIndeksa }}</td>
                        <td>{{ $student->ime }} {{ $student->prezimeKandidata }}</td>
                        <td>
                            <select name="status[{{ $student->id }}]" class="form-control">
                                <option value="prisutan">Присутан</option>
                                <option value="odsutan">Одсутан</option>
                                <option value="opravdano">Оправдано</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="napomena[{{ $student->id }}]" class="form-control" placeholder="Напомена...">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Сачувај присуство</button>
                <a href="{{ route('prisustvo.index') }}" class="btn btn-default">Одустани</a>
            </div>
        </form>
    @else
        @if(request('predmet'))
            <div class="alert alert-info mt-4">Нема студената за изабрани предмет.</div>
        @endif
    @endif
</div>
@endsection
