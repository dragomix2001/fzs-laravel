@extends('layouts.layout')
@section('page_heading','Записник о полагању испита')
@section('section')
    <style>
        .ui-autocomplete {
            z-index: 2147483647 !important;
        }
    </style>
    <div class="col-span-10">
        {{--Modal za dodavanje studenata POCETAK--}}
        <div id="myModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="myModalLabel">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" onclick="closeModal('myModal')"></div>
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl z-10">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-secondary-200">
                        <h4 class="text-lg font-semibold text-secondary-800">Додавање студената</h4>
                        <button type="button" class="text-secondary-400 hover:text-secondary-600 text-xl leading-none" onclick="closeModal('myModal')">&times;</x-button>
                    </div>
                    <div class="px-6 py-4">
                        <form action="{{"/"}}zapisnik/pregled/dodajStudenta" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="zapisnikId" value="{{$zapisnik->id}}">

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <div class="md:col-span-4">
                                    <label for="addStudentList" class="block text-sm font-medium text-secondary-700 mb-1">Студенти</label>
                                    <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="addStudentList" name="addStudentList">
                                        <option value="0"></option>
                                        @foreach($kandidati as $index => $kandidat)
                                            <option value="{{$kandidat->id}}">{{$kandidat->brojIndeksa}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <input type="button" value="Додај" name="button" id="addStudentButton"
                                           class="w-full px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
                                </div>
                            </div>
                            <x-table id="tabela">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Број индекса</th>
                                    <th>Име и презиме</th>
                                    <th>Година студија</th>
                                </tr>
                                </thead>
                                <tbody id="addStudentTableBody">

                                </tbody>
                            </x-table>
                            <div class="flex gap-2 mt-4">
                                <button type="button" class="px-4 py-2 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 text-sm font-medium rounded-lg transition-colors" onclick="closeModal('myModal')">Затвори</x-button>
                                <input type="submit" class="px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer" value="Додај">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{--Modal za dodavanje studenata KRAJ--}}
        {{--Modal za izmenu podataka POCETAK--}}
        <div id="myModal2" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="myModalLabel">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" onclick="closeModal('myModal2')"></div>
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl z-10">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-secondary-200">
                        <h4 class="text-lg font-semibold text-secondary-800">Измена</h4>
                        <button type="button" class="text-secondary-400 hover:text-secondary-600 text-xl leading-none" onclick="closeModal('myModal2')">&times;</button>
                    </div>
                    <div class="px-6 py-4">
                        <form action="{{"/"}}zapisnik/pregled/izmeniPodatke" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="zapisnikId" value="{{$zapisnik->id}}">
                            <input type="hidden" id="datum" name="datum" value="{{$zapisnik->datum}}">
                            <input type="hidden" id="datum2" name="datum2" value="{{$zapisnik->datum2}}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="ucionica" class="block text-sm font-medium text-secondary-700 mb-1">Учионица</label>
                                    <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="ucionica" id="ucionica">
                                </div>
                                <div>
                                    <label for="vreme" class="block text-sm font-medium text-secondary-700 mb-1">Време</label>
                                    <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm timeMask" name="vreme" id="vreme">
                                </div>
                                <div>
                                    <label for="formatDatum" class="block text-sm font-medium text-secondary-700 mb-1">Датум</label>
                                    <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask" name="formatDatum" id="formatDatum">
                                </div>
                                <div>
                                    <label for="formatDatum2" class="block text-sm font-medium text-secondary-700 mb-1">Датум 2</label>
                                    <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask" name="formatDatum2" id="formatDatum2">
                                </div>
                            </div>
                            <div class="mt-4">
                                <input type="submit" class="px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer" value="Сачувај">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{--Modal za izmenu podataka KRAJ--}}
        <div id="messages">
            @if (Session::get('errors'))
                <div class="rounded-lg bg-danger-50 border border-danger-200 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-danger-800">Грешка!</h4>
                            <ul class="mt-1 text-sm text-danger-700 list-disc list-inside">
                                @foreach (Session::get('errors')->all() as $error)
                                    <li>{!! $error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            @if (Session::get('flash-error'))
                <div class="rounded-lg bg-danger-50 border border-danger-200 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-danger-800">
                                <strong>Грешка!</strong>
                                @if(Session::get('flash-error') === 'create')
                                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6">
                <h3 class="text-lg font-semibold text-secondary-800">Предмет: {{ $zapisnik->predmet?->naziv ?? '-' }}</h3>
                <h4 class="text-base text-secondary-700">Испитни рок: {{ $zapisnik->ispitniRok?->naziv ?? '-' }}</h4>
                <h4 class="text-base text-secondary-700">Професор: {{ ($zapisnik->profesor?->ime ?? '') . " " . ($zapisnik->profesor?->prezime ?? '') }}</h4>
            </div>
            <div class="md:col-span-4 mt-4">
                <h4 class="text-base text-secondary-700">Време полагања: {{ substr($zapisnik->vreme, 0, -3) }}</h4>
                <h4 class="text-base text-secondary-700">Учионица: {{ $zapisnik->ucionica }}</h4>
                <h4 class="text-base text-secondary-700">Датум: {{ ($zapisnik->datum == null ? '' : \Carbon\Carbon::parse($zapisnik->datum)->format('d.m.Y.')) . ' / ' . ($zapisnik->datum2 == null ? '' : \Carbon\Carbon::parse($zapisnik->datum2)->format('d.m.Y.')) }}</h4>
            </div>
            <div class="md:col-span-2 mt-4">
                <form target="_blank" action="{{"/"}}izvestaji/zapisnikStampa/{{$zapisnik->id}}" method="post">
                    {{ csrf_field() }}
                    <div>
                        <input type="hidden" name="predmet" value="{{$zapisnik->predmet?->naziv ?? ''}}">
                        <input type="hidden" name="rok" value="{{$zapisnik->ispitniRok?->naziv ?? ''}}">
                        <input type="hidden" name="profesor"
                               value="{{($zapisnik->profesor?->ime ?? '') . " " . ($zapisnik->profesor?->prezime ?? '')}}">
                        <input type="hidden" name="id" value="{{$zapisnik->id}}">
                        <input type="submit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer" value="Штампа записника">
                    </div>
                </form>
            </div>
            <div class="md:col-span-2 mt-4">
                <button type="button" name="edit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors" onclick="openModal('myModal2')">
                    <i class="fa fa-pencil-square-o mr-2"></i> Измени време/учионицу
                </button>
            </div>
        </div>
        <hr class="my-4 border-secondary-200">
        @if(!empty($polozeniIspiti))
            <form action="{{"/"}}zapisnik/polozeniIspit" method="post">
                {{ csrf_field() }}
                <x-table>
                    <thead>
                    <tr>
                        <th>Ред бр.</th>
                        <th>Број индекса</th>
                        <th>Име и презиме</th>
                        <th>Поени</th>
                        <th>Оцена број</th>
                        <th>Оцена словима</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1 ?>
                    @foreach($polozeniIspiti as $index => $ispit)
                        <tr>
                            <td>{{$i}}
                                <input type="hidden" id="ispit_id" name="ispit_id[{{ $index }}]"
                                       value="{{ $ispit->id }}">
                                <input type="hidden" id="zapisnik_id" name="zapisnik_id[{{ $index }}]"
                                       value="{{ $zapisnik->id }}">
                                 <input type="hidden" id="prijava_id" name="prijava_id[{{ $index }}]"
                                         value="{{ $prijavaIds[$ispit->kandidat?->id] ?? '' }}">
                                 <input type="hidden" id="kandidat_id" name="kandidat_id[{{ $index }}]"
                                         value="{{ $ispit->kandidat?->id }}">
                                <input type="hidden" id="predmet_id" name="predmet_id"
                                       value="{{ $zapisnik->predmet_id }}">
                            </td>
                            <td>{{$ispit->kandidat?->brojIndeksa ?? '-'}}</td>
                            <td>{{($ispit->kandidat?->imeKandidata ?? '') . " " . ($ispit->kandidat?->prezimeKandidata ?? '')}}</td>
                            <td>
                                <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm brojBodova w-20"
                                       id="brojBodova"
                                       name="brojBodova[{{ $index }}]"
                                       data-index="{{ $index }}"
                                       value="{{ $ispit->indikatorAktivan == 1 ? $ispit->brojBodova : "" }}">
                            </td>
                            <td>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm konacnaOcena" data-index="{{ $index }}"
                                        name="konacnaOcena[{{ $index }}]">
                                    <option value="0"></option>
                                    <option value="5" {{ $ispit->konacnaOcena == 5 ? 'selected' : "" }}>5</option>
                                    <option value="6" {{ $ispit->konacnaOcena == 6 ? 'selected' : "" }}>6</option>
                                    <option value="7" {{ $ispit->konacnaOcena == 7 ? 'selected' : "" }}>7</option>
                                    <option value="8" {{ $ispit->konacnaOcena == 8 ? 'selected' : "" }}>8</option>
                                    <option value="9" {{ $ispit->konacnaOcena == 9 ? 'selected' : "" }}>9</option>
                                    <option value="10" {{ $ispit->konacnaOcena == 10 ? 'selected' : "" }}>10
                                    </option>
                                </select>
                            </td>
                            <td>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm konacnaOcenaSlovima" data-index="{{ $index }}"
                                        name="konacnaOcenaSlovima" disabled>
                                    <option value="0"></option>
                                    <option value="5" {{ $ispit->konacnaOcena == 5 ? 'selected' : "" }}>пет</option>
                                    <option value="6" {{ $ispit->konacnaOcena == 6 ? 'selected' : "" }}>шест
                                    </option>
                                    <option value="7" {{ $ispit->konacnaOcena == 7 ? 'selected' : "" }}>седам
                                    </option>
                                    <option value="8" {{ $ispit->konacnaOcena == 8 ? 'selected' : "" }}>осам
                                    </option>
                                    <option value="9" {{ $ispit->konacnaOcena == 9 ? 'selected' : "" }}>девет
                                    </option>
                                    <option value="10" {{ $ispit->konacnaOcena == 10 ? 'selected' : "" }}>десет
                                    </option>
                                </select>
                            </td>
                            <td>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm statusIspita" data-index="{{ $index }}"
                                        name="statusIspita[{{$index}}]">
                                    <option value="0"></option>
                                    @foreach($statusIspita as $index => $status)
                                        <option value="{{$status->id}}" {{ $ispit->statusIspita == $status->id ? 'selected' : "" }}>{{$status->naziv}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <x-button variant="danger" size="sm" href="/zapisnik/{{ $ispit->id }}/{{ $ispit->kandidat?->id }}/delete"
                                   onclick="return confirm('Да ли сте сигурни да желите да обришете овог студента?');">
                                    <div title="Брисање">
                                        <span class="fa fa-trash"></span>
                                    </div>
                                </x-button>
                            </td>
                        </tr>
                        <?php $i++ ?>
                    @endforeach
                    </tbody>
                </x-table>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-4">
                    <div class="md:col-span-10 text-center">
                        <x-button variant="primary" size="md" type="submit">
                            <span class="fa fa-save mr-2"></span> Сачувај
                        </button>
                    </div>
                    <div class="md:col-span-2">
                        <x-button variant="primary" size="lg" type="button" onclick="openModal('myModal')">
                            <span class="fa fa-plus mr-2"></span> Додај студента
                        </button>
                    </div>
                </div>
            </form>
        @endif
        <br>
        <br>
    </div>
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

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
                if(ocena > 5){
                    $('.statusIspita[data-index='+ indeks +']').val(1);
                }else{
                    $('.statusIspita[data-index='+ indeks +']').val(2);
                }
            });

            $('.konacnaOcena').change(function () {
                var indeks = $(this).data('index');
                $('.konacnaOcenaSlovima[data-index=' + indeks + ']').val($('.konacnaOcena[data-index=' + indeks + ']').val());
            });

            $('#addStudentButton').click(function () {
                addStudentToList();
            });

            $(".custom-combobox-input").keypress(function (e) {
                var k = e.keyCode || e.which;
                if (k == 13) {
                    e.preventDefault();
                    console.log('input prevented');
                    addStudentToList();
                }
            });

            $(window).keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    console.log('prevented');
                }
            });

            function addStudentToList() {
                $.ajax({
                    url: '{{"/"}}prijava/vratiKandidataPoBroju',
                    type: 'post',
                    data: {
                        id: $('#addStudentList').val(),
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



        var formatDatum = $("#formatDatum");
        formatDatum.datepicker({
            dateFormat: 'dd.mm.yy.',
            altField: "#datum",
            altFormat: "yy-mm-dd"
        });

        formatDatum.on('input', function () {
            var date = moment(formatDatum.val(), "dd.mm.yy");
            $("#datum").val(date.format('YYYY-MM-DD'));
        });

        var formatDatum2 = $("#formatDatum2");
        formatDatum2.datepicker({
            dateFormat: 'dd.mm.yy.',
            altField: "#datum2",
            altFormat: "yy-mm-dd"
        });

        formatDatum2.on('input', function () {
            var date = moment(formatDatum2.val(), "dd.mm.yy");
            $("#datum2").val(date.format('YYYY-MM-DD'));
        });

    </script>
    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
