@extends('layouts.layout')
@section('page_heading','Записник о полагању испита')
@section('section')
    <div class="col-span-12">
        <div id="messages">
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
        <br>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6">
                <x-button variant="primary" size="md" href="{{"/"}}zapisnik/create/">
                    <span class="fa fa-plus mr-2"></span> Нов записник
                </x-button>
                <x-button variant="warning" size="md" href="{{"/"}}zapisnik/arhiva/">
                    <i class="fa fa-archive mr-2"></i> Архива
                </x-button>
            </div>
        </div>
        <hr class="my-4 border-secondary-200">
        <h4 class="text-base font-semibold text-secondary-800 mb-3">Филтрирање записника</h4>
        <form role="form" method="get" action="{{"/"}}zapisnik">
            {{ csrf_field() }}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-3">
                    <label for="filter_predmet_id" class="block text-sm font-medium text-secondary-700 mb-1">Предмет</label>
                    <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="filter_predmet_id" name="filter_predmet_id">
                        <option value=""></option>
                    @foreach($predmeti as $item)
                            <option value="{{$item->id}}" {{ (!empty(app('request')->input('filter_predmet_id')) && app('request')->input('filter_predmet_id') == $item->id) ? 'selected' : '' }}>{{ $item->naziv }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label for="filter_rok_id" class="block text-sm font-medium text-secondary-700 mb-1">Испитни рок</label>
                    <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="filter_rok_id" name="filter_rok_id">
                        <option value=""></option>
                        @if(!empty($aktivniIspitniRok))
                            @foreach($aktivniIspitniRok as $tip)
                                <option value="{{$tip->id}}" {{ (!empty(app('request')->input('filter_rok_id')) && app('request')->input('filter_rok_id') == $tip->id) ? 'selected' : '' }}>{{$tip->naziv}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label for="filter_profesor_id" class="block text-sm font-medium text-secondary-700 mb-1">Професор</label>
                    <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm auto-combobox" id="filter_profesor_id" name="filter_profesor_id">
                        <option value=""></option>
                        @foreach($profesori as $item)
                            <option value="{{$item->id}}" {{ (!empty(app('request')->input('filter_profesor_id')) && app('request')->input('filter_profesor_id') == $item->id) ? 'selected' : '' }}>{{ $item->ime . " " . $item->prezime }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1 flex items-end">
                    <input type="submit" id="submit" class="w-full px-3 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer" value="Филтрирај">
                </div>
                <div class="md:col-span-2 flex items-end">
                    <x-button variant="danger" size="sm" href="{{"/"}}zapisnik/">
                        <i class="fa fa-close mr-2"></i> Поништи филтар
                    </x-button>
                </div>
            </div>
        </form>
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
            @foreach($zapisnici as $index => $zapisnik)
                <tr>
                    <td>{{$zapisnik->predmet?->naziv ?? '-'}}</td>
                    <td>{{$zapisnik->ispitniRok?->naziv ?? '-'}}</td>
                    <td>{{($zapisnik->profesor?->ime ?? '') . " " . ($zapisnik->profesor?->prezime ?? '')}}</td>
                    <td data-order="{{ \Carbon\Carbon::parse($zapisnik->datum)->timestamp }}">{{\Carbon\Carbon::parse($zapisnik->datum)->format('d.m.Y.')}}</td>
                    <td>{{$zapisnik->studenti_count}}</td>
                    <td>
                        <div>
                            <form target="_blank" action="{{"/"}}izvestaji/zapisnikStampa/{{$zapisnik->id}}" method="post" class="mb-0">
                                {{ csrf_field() }}
                                <div class="hidden">
                                    <input type="hidden" name="predmet" value="{{$zapisnik->predmet?->naziv ?? ''}}">
                                    <input type="hidden" name="rok" value="{{$zapisnik->ispitniRok?->naziv ?? ''}}">
                                    <input type="hidden" name="profesor"
                                           value="{{($zapisnik->profesor?->ime ?? '') . " " . ($zapisnik->profesor?->prezime ?? '')}}">
                                    <input type="hidden" name="id" value="{{$zapisnik->id}}">
                                </div>
                                <div class="flex gap-1 flex-wrap">
                                    <x-button variant="primary" size="xs" href="/zapisnik/{{ $zapisnik->id }}">Преглед</x-button>
                                    <x-button variant="danger" size="xs" href="/zapisnik/{{ $zapisnik->id }}"
                                       onclick="return confirm('Да ли сте сигурни да желите да обришете овај записник?');">
                                        <div title="Брисање" class="p-0.5">
                                            <i class="fa fa-trash"></i>
                                        </div>
                                    </x-button>
                                    <x-button variant="warning" size="xs" href="/zapisnik/{{ $zapisnik->id }}">
                                        <div title="архива" class="p-0.5">
                                            <i class="fa fa-archive mr-1"></i> У архиву
                                        </div>
                                    </x-button>
                                </div>
                            </form>
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
    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
@endsection
