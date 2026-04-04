@extends('layouts.layout')
@section('page_heading','Извештај о присуству')
@section('section')

<div class="col-sm-12 col-lg-10">
    <h2>Извештај о присуству студената</h2>
    
    <div class="mb-4">
        <a href="{{ route('prisustvo.index') }}" class="btn btn-default">Назад на евиденцију</a>
    </div>

    @if($prisanstva && $prisanstva->count() > 0)
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>Број индекса</th>
                    <th>Име и презиме</th>
                    <th>Евиденција присуства по недељама</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studenti as $student)
                    @if($prisanstva->has($student->id))
                    <tr>
                        <td>{{ $student->brojIndeksa }}</td>
                        <td>{{ $student->ime }} {{ $student->prezimeKandidata }}</td>
                        <td>
                            <ul class="list-unstyled mb-0">
                                @foreach($prisanstva[$student->id] as $prisanstvo)
                                    <li>
                                        <strong>Недеља {{ $prisanstvo->nastavnaNedelja->redni_broj }}:</strong>
                                        @if($prisanstvo->status == 'prisutan')
                                            <span class="label label-success">Присутан</span>
                                        @elseif($prisanstvo->status == 'odsutan')
                                            <span class="label label-danger">Одсутан</span>
                                        @elseif($prisanstvo->status == 'opravdano')
                                            <span class="label label-warning">Оправдано</span>
                                        @else
                                            <span class="label label-info">{{ ucfirst($prisanstvo->status) }}</span>
                                        @endif
                                        @if($prisanstvo->napomena)
                                            <small class="text-muted">({{ $prisanstvo->napomena }})</small>
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
    @else
        <div class="alert alert-info mt-4">Нема података о присуству за изабрани предмет.</div>
    @endif
</div>
@endsection
