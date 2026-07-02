@extends('layouts.layout')
@section('page_heading','Унос школарине')
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
                <div class="font-semibold text-secondary-800">Унос школарине</div>
            </x-slot:header>
            <form role="form" method="post" action="{{"/"}}skolarina/store">
                {{ csrf_field() }}

                <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $kandidat->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-form-input
                            name="iznos"
                            label="Износ"
                            value=""
                            required />
                    </div>

                    <div>
                        <x-form-input
                            name="komentar"
                            label="Коментар"
                            value=""
                            list="predlozi" />
                    </div>
                    <datalist id="predlozi">
                        <option value="Редовна школарина">
                        <option value="Обнова године">
                        <option value="Школарина са попустом">
                    </datalist>

                    <div>
                        <x-form-select
                            name="tipStudija_id"
                            label="Тип студија"
                            :selected="$kandidat->tipStudija_id">
                            @foreach($tipStudija as $item)
                                <option value="{{ $item->id }}" {{ $kandidat->tipStudija_id == $item->id ? 'selected' : '' }}>{{ $item->naziv }}</option>
                            @endforeach
                        </x-form-select>
                    </div>

                    <div>
                        <x-form-select
                            name="godinaStudija_id"
                            label="Година студија"
                            :selected="$kandidat->godinaStudija_id">
                            @foreach($godinaStudija as $item)
                                <option value="{{ $item->id }}" {{ $kandidat->godinaStudija_id == $item->id ? 'selected' : '' }}>{{ $item->naziv }}</option>
                            @endforeach
                        </x-form-select>
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
@endsection
