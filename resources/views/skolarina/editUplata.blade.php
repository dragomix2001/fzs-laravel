@extends('layouts.layout')
@section('page_heading','Измена уплате')
@section('section')
    <div class="col-span-9">
        {{--GRESKE--}}
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
        <x-card class="border-success-200">
            <x-slot:header>
                <div class="font-semibold text-secondary-800">Измена уплате</div>
            </x-slot:header>
            <form role="form" method="post" action="{{"/"}}uplata/store">
                {{ csrf_field() }}

                <input type="hidden" name="id" id="id" value="{{ $uplata->id }}">
                <input type="hidden" name="skolarina_id" id="skolarina_id" value="{{ $skolarina->id }}">
                <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $kandidat->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-form-input
                            name="iznos"
                            label="Износ"
                            value="{{ $uplata->iznos }}"
                            required />
                    </div>

                    <div>
                        <x-form-input
                            name="naziv"
                            label="Назив"
                            value="{{ $uplata->naziv }}" />
                    </div>

                    <div>
                        <label for="formatDatum" class="block text-sm font-medium text-secondary-700 mb-1">Датум уплате</label>
                        <input id="formatDatum" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dateMask" type="text" name="formatDatum"
                               value="{{ $uplata->datum->format('d.m.Y.') }}"/>
                        <input type="hidden" name="datum" id="datum" value="{{ $uplata->datum }}">
                    </div>
                </div>

                <hr class="my-4 border-secondary-200">

                <div class="text-center mt-4">
                    <x-button variant="primary" size="md" type="submit">
                        <span class="fa fa-save mr-2"></span> Сачувај
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
    <script>
        $(function () {
            $("#formatDatum").datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#datum",
                altFormat: "yy-mm-dd"
            });
        });
    </script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
