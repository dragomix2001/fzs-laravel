@extends('layouts.layout')
@section('page_heading','Додај испите')
@section('section')
    <div class="w-full lg:w-10/12">
        <div class="mb-4">
            <a class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium" href="/prijava/zaStudenta/{{ $kandidat->id }}">Назад на студента</a>
        </div>
        <div>
            <h4>Подаци о студенту &nbsp;
                <a class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600"
                   href="{{"/"}}{{ $kandidat->tipStudija_id == 1 ? 'kandidat' : 'master' }}/{{ $kandidat->id }}/edit">
                    <span class="fa fa-edit" title="Измена"></span>
                </a>
            </h4>
            <ul class="divide-y divide-secondary-200 border border-secondary-200 rounded-lg overflow-hidden mt-2">
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Број Индекса:
                    <strong>{{ $kandidat->brojIndeksa }}</strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Име (име родитеља) презиме:
                    <strong>{{ $kandidat->imeKandidata . " (" . $kandidat->imePrezimeJednogRoditelja . ") " . $kandidat->prezimeKandidata }}</strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">ЈМБГ:
                    <strong>{{ $kandidat->jmbg }}</strong>
                </li>
                @if(!empty($kandidat->datumRodjenja))
                    <li class="px-4 py-3 bg-white text-sm text-secondary-700">Датум рођења:
                        <strong>{{ $kandidat->datumRodjenja->format('d.m.Y') }}</strong>
                    </li>
                @endif
            </ul>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-4">
            <div class="md:col-span-4">
                <label for="addIspitList" class="block text-sm font-medium text-secondary-700 mb-1">Испити</label>
                <select class="auto-combobox block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" id="addIspitList" name="addIspitList">
                    <option value=""></option>
                    @foreach($ispiti as $index => $ispit)
                        <option value="{{$ispit->id}}">{{$ispit->predmet?->naziv ?? '-'}}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-1 flex items-end">
                <input type="button" value="Додај" name="button" id="addIspitButton"
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 cursor-pointer">
            </div>
            <div class="md:col-span-10 mt-4">
                <form action="{{"/"}}prijava/dodajPolozeneIspite" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="kandidat_id" value="{{$kandidat->id}}">
                    <x-table id="tabela">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Назив</th>
                            <th>Оцена</th>
                        </tr>
                        </thead>
                        <tbody id="addIspitTableBody">

                        </tbody>
                    </x-table>
                    <div class="mt-4">
                        <x-button variant="success">Сачувај испите</x-button>
                    </div>
                </form>
            </div>
        </div>
        <div class="mt-4">
            <x-table>
                <thead>
                <tr>
                    <th>Назив</th>
                    <th>Оцена</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($polozeniIspiti as $ispit)
                    <tr>
                        <td>{{$ispit->predmet?->predmet?->naziv ?? '-'}}</td>
                        <td>{{$ispit->konacnaOcena}}</td>
                        <td>
                            <a class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700" href="{{"/"}}deletePrivremeniIspit/{{$ispit->id}}"
                               onclick="return confirm('Да ли сте сигурни да желите да обришете податке?');">
                                <span title="Брисање"><i class="fa fa-trash"></i></span>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </x-table>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('.brojBodova').on('input', function (e) {
                var indeks = $(this).data('index');
                var brojBodova = $(this).val();
                var ocena = 0;
                switch (true) {
                    case (brojBodova == 0):
                        ocena = 0;
                        break;
                    case (brojBodova <= 50):
                        ocena = 5;
                        break;
                    case (brojBodova >= 51 && brojBodova <= 60):
                        ocena = 6;
                        break;
                    case (brojBodova >= 61 && brojBodova <= 70):
                        ocena = 7;
                        break;
                    case (brojBodova >= 71 && brojBodova <= 80):
                        ocena = 8;
                        break;
                    case (brojBodova >= 81 && brojBodova <= 90):
                        ocena = 9;
                        break;
                    case (brojBodova >= 91 && brojBodova <= 100):
                        ocena = 10;
                        break;
                    default:
                        ocena = 0;
                        break;
                }
                $('.konacnaOcena[data-index=' + indeks + ']').val(ocena);
                $('.konacnaOcenaSlovima[data-index=' + indeks + ']').val(ocena);
            });

            $('.konacnaOcena').change(function () {
                var indeks = $(this).data('index');
                $('.konacnaOcenaSlovima[data-index=' + indeks + ']').val($('.konacnaOcena[data-index=' + indeks + ']').val());
            });

            $('#addIspitButton').click(function () {
                addIspitToList();
            });

            $(".custom-combobox-input").keypress(function (e) {
                var k = e.keyCode || e.which;
                if (k == 13) {
                    e.preventDefault();
                    console.log('input prevented');
                    addIspitToList();
                }
            });

            $(window).keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    console.log('prevented');
                }
            });

            function addIspitToList() {
                $.ajax({
                    url: '{{"/"}}prijava/vratiIspitPoId',
                    type: 'post',
                    data: {
                        id: $('#addIspitList').val(),
                        _token: $('input[name=_token]').val()
                    },
                    success: function (result) {
                        $("#tabela tr:last").after(result);
                        $(".custom-combobox-input").val("");
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });
            }
        });
    </script>
    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
