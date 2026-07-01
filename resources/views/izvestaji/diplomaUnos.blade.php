<title>Додавање уверења</title>
@extends('layouts.layout')
@section('page_heading','Додавање уверења')
@section('section')

    @if($diploma !== null)

        <div class="col-span-9">
            <a href="/izvestaji/potvrdeStudent/{{$student->id}}" class="text-primary-600 hover:text-primary-500 text-sm mb-4 inline-block">&#60;&#60;Назад на потврде</a><br/><br/>
            <form role="form" method="post" action="{{ url('/izvestaji/diplomaAdd') }}">
                {{csrf_field()}}
                <input type="hidden" name="id" value="{{$student->id}}">

                <x-card class="border-success-200">
                    <x-slot:header>
                        <div class="font-semibold text-secondary-800">Додавање уверења</div>
                    </x-slot:header>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="broj" class="block text-sm font-medium text-secondary-700 mb-1">Број:</label>
                            <input value="{{$diploma->broj}}" name="broj" type="text" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="datumOdbrane" class="block text-sm font-medium text-secondary-700 mb-1">Датум:</label>
                            <input id="datumOdbrane" value="{{ date('d.m.Y.',strtotime($diploma->datumOdbrane)) }}"
                                   name="datumOdbrane" type="text"
                                   class="form-input dateMask">
                        </div>
                        <div class="form-group">
                            <label for="lice" class="block text-sm font-medium text-secondary-700 mb-1">Ментор:</label>
                            <input type="hidden" id="liceHidden" name="liceHidden"
                                   value="{{$diploma->potpis->ime}}  {{$diploma->potpis->prezime}}">
                            <input type="hidden" id="liceIdHidden" name="liceIdHidden" value="{{$diploma->potpis->id}}">
                            <select class="form-input auto-combobox" id="lice" name="lice">
                                @foreach($profesori as $profesori)
                                    <option value="{{$profesori->id}}">{{$profesori->ime}} {{$profesori->prezime}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="funkcija" class="block text-sm font-medium text-secondary-700 mb-1">Фукција:</label>
                            <input value="{{$diploma->funkcija}}" name="funkcija" type="text" class="form-input">
                        </div>
                    </div>
                    <hr class="my-4 border-secondary-200">
                    <div class="form-group">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">Сачувај</button>
                    </div>
                </x-card>
            </form>
        </div>
    @else
        <div class="col-span-9">
            <form role="form" method="post" action="{{ url('/izvestaji/diplomaAdd') }}">
                {{csrf_field()}}
                <input type="hidden" name="id" value="{{$student->id}}">

                <x-card class="border-success-200">
                    <x-slot:header>
                        <div class="font-semibold text-secondary-800">Додавање дипломе</div>
                    </x-slot:header>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="broj" class="block text-sm font-medium text-secondary-700 mb-1">Број:</label>
                            <input name="broj" type="text" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="datumOdbrane" class="block text-sm font-medium text-secondary-700 mb-1">Датум:</label>
                            <input id="datumOdbrane" name="datumOdbrane" type="text" class="form-input dateMask">
                        </div>
                        <div class="form-group">
                            <label for="lice" class="block text-sm font-medium text-secondary-700 mb-1">Ментор:</label>
                            <select class="form-input auto-combobox" id="lice" name="lice">
                                @foreach($profesori as $profesori)
                                    <option value="{{$profesori->id}}">{{$profesori->ime}} {{$profesori->prezime}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="funkcija" class="block text-sm font-medium text-secondary-700 mb-1">Фукција:</label>
                            <input name="funkcija" type="text" class="form-input">
                        </div>
                    </div>
                    <hr class="my-4 border-secondary-200">
                    <div class="form-group">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">Сачувај</button>
                    </div>
                </x-card>
            </form>
        </div>
    @endif

    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>

    <script>
        $(document).ready(function () {
            $('#lice').combobox('autocomplete', $("#liceHidden").val());

            $("#datumOdbrane").datepicker({
                dateFormat: 'dd.mm.yy.'
            });

        });
    </script>

@endsection
