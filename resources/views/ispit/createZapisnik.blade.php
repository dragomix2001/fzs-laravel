@extends('layouts.layout')
@section('page_heading','Записник о полагању испита')
@section('section')
    <div class="col-span-10">
        {{-- GRESKE --}}
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
        @if (Session::get('flash-error'))
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 mb-4" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            <strong>Грешка!</strong>
                            @if(Session::get('flash-error') === 'create')
                                Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
        <x-card class="border-primary-200">
            <x-slot:header>
                <div class="font-semibold text-secondary-800">Записник о полагању испита</div>
            </x-slot:header>
            <form role="form" method="post" action="{{ url('/zapisnik/storeZapisnik') }}">
                {{ csrf_field() }}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <label for="rok_id" class="block text-sm font-medium text-secondary-700 mb-1">Испитни рок</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="rok_id" name="rok_id">
                            @if(!empty($aktivniIspitniRok))
                                @foreach($aktivniIspitniRok as $tip)
                                    <option value="{{$tip->id}}" {{ (!empty($rok_id) && $rok_id == $tip->id) ? 'selected' : '' }}>{{$tip->naziv}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="predmet_id" class="block text-sm font-medium text-secondary-700 mb-1">Предмет</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="predmet_id" name="predmet_id">
                            @foreach($predmeti as $item)
                                <option value="{{$item->id}}">{{ $item->naziv }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                    <div class="md:col-span-2">
                        <label for="profesor_id" class="block text-sm font-medium text-secondary-700 mb-1">Професор</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="profesor_id" name="profesor_id">
                            @foreach($profesori as $item)
                                <option value="{{$item->id}}">{{ $item->ime . " " . $item->prezime }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" id="ajaxSubmitPrijava" class="w-full px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i> Прикажи студенте
                        </button>
                    </div>
                    <div class="flex items-end">
                        <button type="button" id="addStudentLink" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-user-plus mr-2"></i> Додај студента
                        </button>
                    </div>
                </div>
                <hr class="my-4 border-secondary-200">

                <input type="hidden" id="prijavaIspita_id" name="prijavaIspita_id" value="">

                <input type="hidden" id="datum" name="datum" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                <input type="hidden" id="datum2" name="datum2" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">

                <h3 class="text-base font-semibold text-secondary-800 mb-3">Студенти који су пријавили испит у испитном року</h3>

                <hr class="my-4 border-secondary-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="formatDatum" class="block text-sm font-medium text-secondary-700 mb-1">Датум</label>
                        <input type="text" id="formatDatum" name="formatDatum" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask"
                               value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}">
                    </div>
                    <div>
                        <label for="formatDatum2" class="block text-sm font-medium text-secondary-700 mb-1">Датум 2</label>
                        <input type="text" id="formatDatum2" name="formatDatum2" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask"
                               value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}">
                    </div>
                    <div>
                        <label for="vreme" class="block text-sm font-medium text-secondary-700 mb-1">Време</label>
                        <input type="text" id="vreme" name="vreme" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="ucionica" class="block text-sm font-medium text-secondary-700 mb-1">Учионица</label>
                        <input type="text" id="ucionica" name="ucionica" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    </div>
                </div>

                <x-table id="tabela" class="mt-4">
                    <thead>
                    <tr>
                        <th>Полагао</th>
                        <th>Број Индекса</th>
                        <th>Име и презиме</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </x-table>

                <div id="messageEmpty">
                </div>

                <div class="text-center mt-4">
                    <button type="submit" name="Submit" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white text-base font-medium rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Сачувај
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    <script>
        $(document).ready(function() {
            console.log('Document ready - jQuery version: ' + $.fn.jquery);
            
            $('#addStudentLink').click(function(){
                console.log('Add student clicked, predmet_id: ' + $('#predmet_id').val());
                window.location = "/prijava/zaPredmet/" + $('#predmet_id').val();
            });

            $('#rok_id').change(function () {
                console.log('Rok changed');
                var rok = $('#rok_id');
                $.ajax({
                    url: '/zapisnik/vratiZapisnikPredmet',
                    method: 'get',
                    data: {
                        rokId: rok.val()
                    },
                    success: function (result) {
                        console.log('Predmeti loaded:', result);
                        var selectList = $('#predmet_id');
                        selectList.empty();
                        $.each(result['predmeti'], function () {
                            selectList.append($("<option />").val(this.id).text(this.naziv));
                        });
                        selectList = $('#profesor_id');
                        selectList.empty();
                        $.each(result['profesori'], function () {
                            selectList.append($("<option />").val(this.id).text(this.ime + ' ' + this.prezime));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading predmeti:', error);
                    }
                });
            });

            $('#ajaxSubmitPrijava').click(function () {
                console.log('Show students clicked');
                var rok = $('#rok_id').val();
                var predmet = $('#predmet_id').val();
                var profesor = $('#profesor_id').val();
                
                console.log('rok:', rok, 'predmet:', predmet, 'profesor:', profesor);
                
                $.ajax({
                    url: '/zapisnik/vratiZapisnikStudenti',
                    method: 'get',
                    data: {
                        rok_id: rok,
                        predmet_id: predmet,
                        profesor_id: profesor
                    },
                    success: function (result) {
                        console.log('Studenti loaded:', result);

                        if(result['message'].length > 0){
                            $('#messageEmpty').html(result['message']);
                        }else{
                            $('#messageEmpty').html("");
                        }
                        $("#tabela tbody").empty();
                        $.each(result['kandidati'], function (e) {
                            $('#tabela tbody').append('<tr><td>' + '<input type="checkbox" name="odabir[' + this.id + ']" value="' + this.id + '" checked>' +
                                    '</td><td>' + this.brojIndeksa +
                                    '</td><td>' + this.imeKandidata + ' ' + this.prezimeKandidata + '</td></tr>');
                        });
                        $('#prijavaIspita_id').val(result['prijavaId']);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading studenti:', error);
                    }
                });
            });

            // Datepicker
            $("#formatDatum").datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#datum",
                altFormat: "yy-mm-dd"
            });

            $("#formatDatum2").datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#datum2",
                altFormat: "yy-mm-dd"
            });

            // Prevent form submission on Enter
            $(window).keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection
