<title>Извештаји</title>
@extends('layouts.layout')
@section('page_heading','Извештаји')
@section('section')

    <div class="border-b border-secondary-200">
        <nav class="flex gap-4">
            <a href="#pdfTab" id="tab-pdf" class="px-4 py-2 text-sm font-medium text-primary-600 border-b-2 border-primary-600" onclick="switchTab('pdf')">PDF</a>
            <a href="#excelTab" id="tab-excel" class="px-4 py-2 text-sm font-medium text-secondary-500 hover:text-secondary-700 border-b-2 border-transparent" onclick="switchTab('excel')">Excel</a>
        </nav>
    </div>

    <div id="pdfTab" class="tab-content">
        <div class="col-span-12">

            <div class="col-span-4">
                <form role="form" target="_blank" method="post" action="{{ url('/izvestaji/spisakZaSmer/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак студената по смеровима</div>
                        </x-slot:header>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="program" class="block text-sm font-medium text-secondary-700 mb-1">Студијски програм:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="program" name="program">
                                    @foreach($program as $program)
                                        <option value="{{$program->id}}">{{$program->naziv}}
                                            - {{$program->tipStudija->skrNaziv}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="godina" class="block text-sm font-medium text-secondary-700 mb-1">Година студија:</label>
                                <select id="godina" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="godina">
                                    <option value="1">Прва</option>
                                    <option value="2">Друга</option>
                                    <option value="3">Трећа</option>
                                    <option value="4">Четврта</option>
                                    <option value="5">Пета</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors mt-3">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>

                <form role="form" method="post" target="_blank" action="{{ url('/izvestaji/spisakPoGodini/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак по години</div>
                        </x-slot:header>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="godina" class="block text-sm font-medium text-secondary-700 mb-1">Година студија:</label>
                                <select id="godina" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="godina">
                                    <option value="1">Прва</option>
                                    <option value="2">Друга</option>
                                    <option value="3">Трећа</option>
                                    <option value="4">Четврта</option>
                                    <option value="5">Пета</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors mt-3">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>

                <form role="form" method="post" target="_blank" action="{{ url('/izvestaji/spisakPoProgramu/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак по програму</div>
                        </x-slot:header>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="program" class="block text-sm font-medium text-secondary-700 mb-1">Програм:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="program" name="program">
                                    @foreach($programS as $programS)
                                        <option value="{{$programS->id}}">{{$programS->naziv}}
                                            - {{$programS->tipStudija->skrNaziv}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors mt-3">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>
            </div>

            <div class="col-span-4">
                <form role="form" method="post" target="_blank"
                      action="{{ url('/izvestaji/spisakPoPredmetima/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак студената по предметима</div>
                        </x-slot:header>
                        <div>
                            <label for="predmet" class="block text-sm font-medium text-secondary-700 mb-1">Предмет:</label>
                            <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="predmet" name="predmet">
                                @foreach($predmeti as $predmet)
                                    <option value="{{$predmet->id}}">{{$predmet->naziv}}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors mt-3">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>

                <form role="form" method="post" target="_blank"
                      action="{{ url('/izvestaji/spisakDiplomiranih/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак дипломираних студената</div>
                        </x-slot:header>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>

                <form role="form" method="post" target="_blank" action="{{ url('/izvestaji/spisakPoSlavama/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак студената по славама</div>
                        </x-slot:header>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>

                <form role="form" method="post" target="_blank"
                      action="{{ url('/izvestaji/spisakPoProfesorima/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак предмета по професорима</div>
                        </x-slot:header>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>
            </div>

            <div class="col-span-4">
                <form role="form" target="_blank" method="post" action="{{ url('/izvestaji/nastavniPlan/') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Наставни план</div>
                        </x-slot:header>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="program" class="block text-sm font-medium text-secondary-700 mb-1">Студијски програм:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="program" name="program">
                                    @foreach($programPlan as $program)
                                        <option value="{{$program->id}}">{{$program->naziv}}
                                            - {{$program->tipStudija->skrNaziv}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="skolskaGodina_id" class="block text-sm font-medium text-secondary-700 mb-1">Школска година:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="skolskaGodina_id"
                                        name="godina">
                                    @foreach($skolskaGodina as $godina)
                                        <option value="{{$godina->id}}">{{$godina->naziv}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors mt-3">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>

                <form role="form" method="post" target="_blank"
                      action="{{ url('/izvestaji/spisakPoSmerovimaAktivni') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак свих активних студената</div>
                        </x-slot:header>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>

                <form role="form" method="post" target="_blank"
                      action="{{ url('/izvestaji/spisakPoSmerovimaOstali') }}">
                    {{csrf_field()}}

                    <x-card class="border-success-200 mb-4">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Списак свих студената - остало</div>
                        </x-slot:header>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                            <span class="fa fa-print mr-2"></span> Штампај
                        </button>
                    </x-card>
                </form>
            </div>
        </div>
    </div>

    <div id="excelTab" class="tab-content hidden">
        <h3 class="text-lg font-semibold text-secondary-800 my-4">Издвајање података у Excel табелу</h3>

        <div class="col-span-4">
            <form role="form" method="post" target="_blank" action="{{ url('/izvestaji/excelStampa/') }}">
                {{csrf_field()}}

                <x-card class="border-success-200">
                    <x-slot:header>
                        <div class="font-semibold text-secondary-800">Списак по програму</div>
                    </x-slot:header>
                    <div>
                        <label for="programE" class="block text-sm font-medium text-secondary-700 mb-1">Програм:</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="programE" name="programE">
                            @foreach($programE as $programE)
                                <option value="{{$programE->id}}">{{$programE->naziv}}
                                    - {{$programE->tipStudija->skrNaziv}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors mt-3">
                        <span class="fa fa-print mr-2"></span> Штампај
                    </button>
                </x-card>
            </form>
        </div>
    </div>

    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>

    <script>
        function switchTab(tab) {
            document.getElementById('pdfTab').classList.toggle('hidden', tab !== 'pdf');
            document.getElementById('excelTab').classList.toggle('hidden', tab !== 'excel');
            document.getElementById('tab-pdf').classList.toggle('border-primary-600', tab === 'pdf');
            document.getElementById('tab-pdf').classList.toggle('border-transparent', tab !== 'pdf');
            document.getElementById('tab-pdf').classList.toggle('text-primary-600', tab === 'pdf');
            document.getElementById('tab-pdf').classList.toggle('text-secondary-500', tab !== 'pdf');
            document.getElementById('tab-excel').classList.toggle('border-primary-600', tab === 'excel');
            document.getElementById('tab-excel').classList.toggle('border-transparent', tab !== 'excel');
            document.getElementById('tab-excel').classList.toggle('text-primary-600', tab === 'excel');
            document.getElementById('tab-excel').classList.toggle('text-secondary-500', tab !== 'excel');
        }

        // Activate PDF tab by default
        document.addEventListener('DOMContentLoaded', function() {
            switchTab('pdf');
        });
    </script>

@endsection
