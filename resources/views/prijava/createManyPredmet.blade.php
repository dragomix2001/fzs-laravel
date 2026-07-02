@extends('layouts.layout')
@section('page_heading','Пријава испита за више кандидата')
@section('section')
    <div class="w-full lg:w-9/12">
        <div id="messages">
            @if (Session::get('errors'))
                <x-alert type="danger" :dismissible="true">
                    <x-slot:title>Грешка!</x-slot:title>
                    <ul>
                        @foreach (Session::get('errors')->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif
        </div>
        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold">Пријава за полагање испита</h3>
            </x-slot:header>
            <form id="formaKandidatiOdabir" action="{{"/"}}prijava/predmetVise" method="post">
                {{ csrf_field() }}

                <input type="hidden" name="predmet_id" id="predmet_id_hidden" value="{{ $predmet->id }}">

                <div class="w-full lg:w-1/2 mb-4">
                    <x-form-input name="predmet_display" label="Пријављујем се за полагање испита из предмета"
                                  value="{{ $predmet->naziv }}" disabled />
                </div>

                <hr class="my-6 border-secondary-200">

                <div class="w-full lg:w-4/5 mb-4">
                    <x-form-select name="profesor_id" label="Професор"
                                   :options="$profesor->mapWithKeys(fn($t) => [$t->id => $t->zvanje . ' ' . $t->ime . ' ' . $t->prezime])->toArray()" />
                </div>

                <hr class="my-6 border-secondary-200">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-form-select name="rok_id" label="Испитни рок"
                                   :options="$ispitniRok->pluck('naziv','id')->toArray()" />
                    <x-form-select name="tipPrijave_id" label="Тип пријаве"
                                   :options="$tipPrijave->pluck('naziv','id')->toArray()" />
                    <x-form-input name="formatDatum" label="Датум"
                                  value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}"
                                  class="dateMask" />
                    <x-form-input name="formatDatum2" label="Датум (резервни)"
                                  value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}"
                                  class="dateMask" />
                </div>

                <input type="hidden" name="datum" id="datum" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                <input type="hidden" name="datum2" id="datum2" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">

                <hr class="my-6 border-secondary-200">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-end">
                    <div>
                        <label for="studentSearch" class="block text-sm font-medium text-secondary-700 mb-1">Претрага студента (број индекса)</label>
                        <input type="text" id="studentSearch" class="w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 px-4 py-2 border" placeholder="Унесите број индекса за претрагу...">
                    </div>
                    <input type="hidden" id="addStudentList" name="addStudentList" value="">
                    <div>
                        <x-button variant="success" id="addStudentButton">Додај студента</x-button>
                    </div>
                </div>

                <x-table class="mt-4">
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

                <hr class="my-6 border-secondary-200">

                <div class="text-center">
                    <x-button variant="success" size="lg" type="submit" name="Submit2" value="Креирај пријаву и записник">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Креирај пријаву и записник
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script>
        // Pass PHP data to JS
        var studenti = @json($kandidatiJson);

        document.addEventListener('DOMContentLoaded', function () {
            var formatDatum = document.getElementById('formatDatum');
            if (formatDatum) {
                formatDatum.addEventListener('input', function () {
                    var date = moment(formatDatum.value, "DD.MM.YYYY");
                    document.getElementById('datum').value = date.isValid() ? date.format('YYYY-MM-DD') : '';
                });
            }

            var formatDatum2 = document.getElementById('formatDatum2');
            if (formatDatum2) {
                formatDatum2.addEventListener('input', function () {
                    var date = moment(formatDatum2.value, "DD.MM.YYYY");
                    document.getElementById('datum2').value = date.isValid() ? date.format('YYYY-MM-DD') : '';
                });
            }

            document.addEventListener('keypress', function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
            });

            // Keep jQuery autocomplete for student search
            // (jQuery UI autocomplete remains via jquery-ui-autocomplete.js)
            // The addStudentButton click and AJAX still use jQuery
        });
    </script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
    <script>
        $(document).ready(function () {
            var forma = $('#formaKandidatiOdabir');

            // Autocomplete za studente
            $("#studentSearch").autocomplete({
                source: function(request, response) {
                    var searchTerm = request.term.toLowerCase();
                    var matchedOptions = studenti.filter(function(s) {
                        return s.label.toLowerCase().indexOf(searchTerm) !== -1;
                    });
                    response(matchedOptions);
                },
                select: function(event, ui) {
                    $("#studentSearch").val(ui.item.label);
                    $("#addStudentList").val(ui.item.value);
                    return false;
                },
                minLength: 1
            });

            $('#addStudentButton').click(function () {
                addStudentToList();
                $('#studentSearch').val('');
                $('#addStudentList').val('');
            });

            $("#studentSearch").keypress(function(e){
                var k = e.keyCode || e.which;
                if (k == 13) {
                    e.preventDefault();
                    var selectedVal = $('#addStudentList').val();
                    if (selectedVal) {
                        addStudentToList();
                    }
                }
            });

            function addStudentToList() {
                var studentId = $('#addStudentList').val();
                if (!studentId) {
                    alert('Молимо изаберите студента из листе!');
                    return;
                }

                $.ajax({
                    url: '{{"/"}}prijava/vratiKandidataPoBroju',
                    type: 'post',
                    data: {
                        id: studentId,
                        _token: $('input[name=_token]').val()
                    },
                    success: function (result) {
                        $("#tabela tr:last").after(result);
                        $('#studentSearch').val('');
                        $('#addStudentList').val('');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });
            }
        });
    </script>
@endsection
