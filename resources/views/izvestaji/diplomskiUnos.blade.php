<title>Дипломски рад - пријава</title>
@extends('layouts.layout')
@section('page_heading','Дипломски рад - пријава')
@section('section')
    @if($diplomski !== null)
    <div class="col-span-12">

        <div class="col-span-9">
            <a href="/izvestaji/potvrdeStudent/{{$student->id}}" class="text-primary-600 hover:text-primary-500 text-sm mb-4 inline-block">&#60;&#60;Назад на потврде</a><br/><br/>
            <form role="form" method="post" action="{{ url('/izvestaji/diplomskiAdd/') }}">
                {{csrf_field()}}
                <input type="hidden" name="id" value="{{$student->id}}">

                <x-card class="border-success-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            <label for="program" class="block text-sm font-medium text-secondary-700 mb-1">Студијски програм:</label>
                            <input name="program" type="text" value="{{$program->naziv}}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" disabled="disabled">
                        </div>
                        <div class="md:col-span-2">
                            <label for="predmet" class="block text-sm font-medium text-secondary-700 mb-1">Предмет:</label>
                            <input type="hidden" id="predmetHidden" name="predmetHidden" value="{{$diplomski->predmet->naziv}}">
                            <input type="hidden" id="predmetIdHidden" name="predmetIdHidden" value="{{$diplomski->predmet->id}}">
                            <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="predmet" name="predmet">
                                @foreach($predmeti as $predmet)
                                    <option value="{{$predmet->predmet->id}}">{{$predmet->predmet->naziv}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div
                            <label for="ocenaOpis" class="block text-sm font-medium text-secondary-700 mb-1">Оцена описно:</label>
                            <input name="ocenaOpis" value="{{$diplomski->ocenaOpis}}" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        </div>
                        <div
                            <label for="ocenaBroj" class="block text-sm font-medium text-secondary-700 mb-1">Оцена бројчано:</label>
                            <input name="ocenaBroj" value="{{$diplomski->ocenaBroj}}" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        </div>
                        <div
                            <label for="naziv" class="block text-sm font-medium text-secondary-700 mb-1">Тема:</label>
                            <input name="naziv" value="{{$diplomski->naziv}}" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        </div>
                        <div
                            <label for="mentor_id" class="block text-sm font-medium text-secondary-700 mb-1">Ментор:</label>
                            <input type="hidden" id="mentorHidden" name="mentorHidden" value="{{$diplomski->mentor->ime}}  {{$diplomski->mentor->prezime}}">
                            <input type="hidden" id="mentorIdHidden" name="mentorIdHidden" value="{{$diplomski->mentor->id}}">
                            <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="mentor_id" name="mentor_id">
                                @foreach($profesori as $profesori)
                                    <option value="{{$profesori->id}}">{{$profesori->ime}} {{$profesori->prezime}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div
                            <label for="clan_id" class="block text-sm font-medium text-secondary-700 mb-1">Члан комисије:</label>
                            <input type="hidden" id="clanHidden" name="clanHidden" value="{{$diplomski->clan->ime}}  {{$diplomski->clan->prezime}}">
                            <input type="hidden" id="clanIdHidden" name="clanIdHidden" value="{{$diplomski->clan->id}}">
                            <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="clan_id" name="clan_id">
                                @foreach($clan as $clan)
                                    <option value="{{$clan->id}}">{{$clan->ime}} {{$clan->prezime}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div
                            <label for="predsednik_id" class="block text-sm font-medium text-secondary-700 mb-1">Председник комисије:</label>
                            <input type="hidden" id="predsednikHidden" name="predsednikHidden" value="{{$diplomski->predsednik->ime}}  {{$diplomski->predsednik->prezime}}">
                            <input type="hidden" id="predsednikIdHidden" name="predsednikIdHidden" value="{{$diplomski->predsednik->id}}">
                            <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="predsednik_id" name="predsednik_id">
                                @foreach($predsednik as $predsednik)
                                    <option value="{{$predsednik->id}}">{{$predsednik->ime}} {{$predsednik->prezime}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div
                            <label for="datumPrijave" class="block text-sm font-medium text-secondary-700 mb-1">Датум пријаве:</label>
                            <input name="datumPrijave" id="datumPrijave" value="{{ date('d.m.Y.',strtotime($diplomski->datumPrijave)) }}" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask">
                        </div>
                        <div
                            <label for="datumOdbrane" class="block text-sm font-medium text-secondary-700 mb-1">Датум одбране:</label>
                            <input name="datumOdbrane" id="datumOdbrane" value="{{ date('d.m.Y.',strtotime($diplomski->datumOdbrane)) }}" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask">
                        </div>
                    </div>
                    <hr class="my-4 border-secondary-200">
                    <div
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">Сачувај</button>
                    </div>
                </x-card>
            </form>
        </div>
    </div>

    @else

        <div class="col-span-12">

            <div class="col-span-9">
                <form role="form" method="post" action="{{ url('/izvestaji/diplomskiAdd/') }}">
                    {{csrf_field()}}
                    <input type="hidden" name="id" value="{{$student->id}}">

                    <x-card class="border-success-200">
                        <x-slot:header>
                            <div class="font-semibold text-secondary-800">Дипломски рад - пријава</div>
                        </x-slot:header>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                <label for="program" class="block text-sm font-medium text-secondary-700 mb-1">Студијски програм:</label>
                                <input name="program" type="text" value="{{$program->naziv}}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" disabled="disabled">
                            </div>
                            <div class="md:col-span-2">
                                <label for="predmet" class="block text-sm font-medium text-secondary-700 mb-1">Предмет:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="predmet" name="predmet">
                                    @foreach($predmeti as $predmet)
                                        <option value="{{$predmet->predmet->id}}">{{$predmet->predmet->naziv}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div
                                <label for="ocenaOpis" class="block text-sm font-medium text-secondary-700 mb-1">Оцена описно:</label>
                                <input name="ocenaOpis" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                            <div
                                <label for="ocenaBroj" class="block text-sm font-medium text-secondary-700 mb-1">Оцена бројчано:</label>
                                <input name="ocenaBroj" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                            <div
                                <label for="naziv" class="block text-sm font-medium text-secondary-700 mb-1">Тема:</label>
                                <input name="naziv" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                            <div
                                <label for="mentor_id" class="block text-sm font-medium text-secondary-700 mb-1">Ментор:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="mentor_id" name="mentor_id">
                                    @foreach($profesori as $profesori)
                                        <option value="{{$profesori->id}}">{{$profesori->ime}} {{$profesori->prezime}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div
                                <label for="clan_id" class="block text-sm font-medium text-secondary-700 mb-1">Члан комисије:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="clan_id" name="clan_id">
                                    @foreach($clan as $clan)
                                        <option value="{{$clan->id}}">{{$clan->ime}} {{$clan->prezime}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div
                                <label for="predsednik_id" class="block text-sm font-medium text-secondary-700 mb-1">Председник комисије:</label>
                                <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="predsednik_id" name="predsednik_id">
                                    @foreach($predsednik as $predsednik)
                                        <option value="{{$predsednik->id}}">{{$predsednik->ime}} {{$predsednik->prezime}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div
                                <label for="datumPrijave" class="block text-sm font-medium text-secondary-700 mb-1">Датум пријаве:</label>
                                <input id="datumPrijave" name="datumPrijave" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask">
                            </div>
                            <div
                                <label for="datumOdbrane" class="block text-sm font-medium text-secondary-700 mb-1">Датум одбране:</label>
                                <input id="datumOdbrane" name="datumOdbrane" type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask">
                            </div>
                        </div>
                        <hr class="my-4 border-secondary-200">
                        <div
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">Сачувај</button>
                        </div>
                    </x-card>
                </form>
            </div>
        </div>

        @endif

    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>

    <script>
        $(document).ready(function () {
            $('#mentor_id').combobox('autocomplete', $("#mentorHidden").val());
            $('#clan_id').combobox('autocomplete', $("#clanHidden").val());
            $('#predsednik_id').combobox('autocomplete', $("#predsednikHidden").val());
            $('#predmet').combobox('autocomplete', $("#predmetHidden").val());

            var formatDatum = $("#datumOdbrane");
            formatDatum.datepicker({
                dateFormat: 'dd.mm.yy.',
                altFormat: "yy-mm-dd"
            });

            var formatDatum = $("#datumPrijave");
            formatDatum.datepicker({
                dateFormat: 'dd.mm.yy.',
                altFormat: "yy-mm-dd"
            });

        });
    </script>

@endsection
