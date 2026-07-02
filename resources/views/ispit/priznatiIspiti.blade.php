@extends('layouts.layout')
@section('page_heading','Признати испити')
@section('section')
    <div class="col-span-10">
        @if (Session::get('errors'))
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 mb-4" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-red-800">Грешка!</h4>
                        <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                            @foreach (Session::get('errors')->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
            <div class="mb-4">
                <a class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors" href="/prijava/zaStudenta/{{ $kandidat->id }}">
                    <i class="fa fa-arrow-left mr-2"></i> Назад на студента
                </a>
            </div>
        <div>
            <h4 class="text-lg font-semibold text-secondary-800 mb-3">Подаци о студенту</h4>
            <ul class="divide-y divide-secondary-200 border border-secondary-200 rounded-lg overflow-hidden">
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Број Индекса:
                    <strong>
                        {{ $kandidat->brojIndeksa }}
                    </strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Име (име родитеља) презиме:
                    <strong>
                        {{ $kandidat->imeKandidata . " (" . $kandidat->imePrezimeJednogRoditelja . ") " . $kandidat->prezimeKandidata }}
                    </strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">ЈМБГ:
                    <strong>
                        {{ $kandidat->jmbg }}
                    </strong>
                </li>
                @if(!empty($kandidat->datumRodjenja))
                    <li class="px-4 py-3 bg-white text-sm text-secondary-700">Датум рођења:
                        <strong>
                            {{ \Carbon\Carbon::parse($kandidat->datumRodjenja)->format('d.m.Y') }}
                        </strong>
                    </li>
                @endif
            </ul>
        </div>
        <x-card class="border-primary-200">
            <x-slot:header>
                <div class="font-semibold text-secondary-800">Признати испити за студента</div>
            </x-slot:header>
            <form id="formaPredmetOdabir" action="{{"/"}}storePriznatiIspiti" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="kandidat_id" value="{{$kandidat->id}}">
                <x-table id="tabela">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Предмет</th>
                        <th>Семестар</th>
                        <th>ЕСПБ</th>
                        <th>Тип предмета</th>
                        <th>Коначна оцена</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($predmetProgram as $index => $predmet)
                        <tr>
                            <td>
                                <input type="checkbox" id="predmetId" name="predmetId[{{ $index }}]"
                                       value="{{ $predmet->id }}" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td>{{$predmet->predmet?->naziv ?? '-'}}</td>
                            <td>{{$predmet->semestar}}</td>
                            <td>{{$predmet->espb}}</td>
                            <td>{{$predmet->tipPredmeta?->naziv ?? '-'}}</td>
                            <td>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm konacnaOcena" data-index="{{ $index }}"
                                        name="konacnaOcena[{{ $index }}]">
                                    <option value=""></option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </x-table>
                <div class="text-center mt-4">
                    <div id="sacuvaj" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white text-base font-medium rounded-lg transition-colors cursor-pointer">
                        <i class="fa fa-save mr-2"></i> Сачувај
                    </div>
                </div>
            </form>
        </x-card>
        <br>
        <br>
    </div>
    <script>
        var forma = $('#formaPredmetOdabir');

        $('#sacuvaj').click(function () {
            forma.submit();
        });
    </script>
@endsection
