@extends('layouts.layout')
@section('page_heading','Резултат пријаве испита')
@section('section')
    <div class="w-full lg:w-10/12">
        @if (count($errorArray) > 0)
            <x-card variant="danger">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Студенти код којих је дошло до грешке</h3>
                </x-slot:header>
                <x-table>
                    <thead>
                    <tr>
                        <th>Број индекса</th>
                        <th>Име</th>
                        <th>Презиме</th>
                        <th>ЈМБГ</th>
                        <th>Година студија</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($errorArray as $index => $kandidat)
                        <tr>
                            <td>{{$kandidat->brojIndeksa}}</td>
                            <td>{{$kandidat->imeKandidata}}</td>
                            <td>{{$kandidat->prezimeKandidata}}</td>
                            <td>{{$kandidat->jmbg}}</td>
                            <td>{{$kandidat->godinaStudija?->nazivRimski ?? '-'}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </x-table>
            </x-card>
        @endif
        @if (count($duplicateArray) > 0)
            <x-card variant="warning">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Студенти који су већ пријављени у датом року</h3>
                </x-slot:header>
                <x-table>
                    <thead>
                    <tr>
                        <th>Број индекса</th>
                        <th>Име</th>
                        <th>Презиме</th>
                        <th>ЈМБГ</th>
                        <th>Година студија</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($duplicateArray as $index => $kandidat)
                        <tr>
                            <td>{{$kandidat->brojIndeksa}}</td>
                            <td>{{$kandidat->imeKandidata}}</td>
                            <td>{{$kandidat->prezimeKandidata}}</td>
                            <td>{{$kandidat->jmbg}}</td>
                            <td>{{$kandidat->godinaStudija?->nazivRimski ?? '-'}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </x-table>
            </x-card>
        @endif
        <x-alert type="success">
            <strong>Студенти су успешно пријављени.</strong>
        </x-alert>
        <a href="{{"/"}}prijava/zaPredmet/{{ $predmetId }}" class="text-primary-600 hover:text-primary-800 underline">&lt;&lt; Назад на предмет</a>
    </div>
@endsection
