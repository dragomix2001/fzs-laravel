@extends('layouts.layout')
@section('page_heading','Архива записника о полагању испита')
@section('section')
    <div class="col-span-12">
        <div id="messages">
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
        </div>
        <br>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-8">
                <a href="{{"/"}}zapisnik/" class="inline-flex items-center px-4 py-2 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 text-sm font-medium rounded-lg transition-colors">
                    <i class="fa fa-backward mr-2"></i> Назад на преглед
                </a>
            </div>
            <br>
            <br>
            <br>
            <br>
            <div class="md:col-span-8">
                <form role="form" method="post" action="{{"/"}}zapisnik/arhivirajRok">
                    {{ csrf_field() }}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="md:col-span-4">
                            <label for="rok_id" class="block text-sm font-medium text-secondary-700 mb-1">Архивирај записнике за испитни рок</label>
                            <select class="form-input" id="rok_id" name="rok_id">
                                @if(!empty($aktivniIspitniRok))
                                    @foreach($aktivniIspitniRok as $tip)
                                        <option value="{{$tip->id}}" {{ (!empty($rok_id) && $rok_id == $tip->id) ? 'selected' : '' }}>{{$tip->naziv}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label for="submit" class="block text-sm font-medium text-secondary-700 mb-1 invisible">&nbsp;</label>
                            <input type="submit" id="submit" class="w-full px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer" value="Архивирај">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr class="my-4 border-secondary-200">
        <x-table id="tabela">
            <thead>
            <tr>
                <th>Предмет</th>
                <th>Испитни рок</th>
                <th>Професор</th>
                <th>Датум</th>
                <th>Број студената</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($arhiviraniZapisnici as $index => $zapisnik)
                <tr>
                    <td>{{$zapisnik->predmet?->naziv ?? '-'}}</td>
                    <td>{{$zapisnik->ispitniRok?->naziv ?? '-'}}</td>
                    <td>{{($zapisnik->profesor?->ime ?? '') . " " . ($zapisnik->profesor?->prezime ?? '')}}</td>
                    <td>{{\Carbon\Carbon::parse($zapisnik->datum)->format('d.m.Y.')}}</td>
                    <td>{{$zapisnik->studenti_count}}</td>
                    <td>
                        <div class="flex gap-1">
                            <a class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded-lg transition-colors" href="{{"/"}}zapisnik/pregled/{{ $zapisnik->id }}">Преглед полагања</a>
                            <a class="inline-flex items-center px-3 py-1.5 bg-danger-600 hover:bg-danger-500 text-white text-xs font-medium rounded-lg transition-colors" href="{{"/"}}zapisnik/delete/{{ $zapisnik->id }}"
                               onclick="return confirm('Да ли сте сигурни да желите да обришете овај записник?');">Бриши</a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </x-table>
        <br>
        <br>
    </div>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
@endsection
