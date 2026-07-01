@extends('layouts.layout')
@section('page_heading',"Статус студента")
@section('section')
    <div class="w-full">

        {{--Modal za upis na master studije POCETAK--}}
        <div class="fixed inset-0 z-50 hidden" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="document.getElementById('myModal').classList.add('hidden')"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full">
                    <form action="{{"/"}}student/{{ $kandidat->id }}/upisMasterStudija">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-secondary-200">
                            <h4 class="text-lg font-semibold text-secondary-900" id="myModalLabel">Упис на мастер студије</h4>
                            <button type="button" class="text-secondary-400 hover:text-secondary-600 text-2xl leading-none" onclick="document.getElementById('myModal').classList.add('hidden')">&times;</button>
                        </div>
                        <div class="px-6 py-4">
                            {{ csrf_field() }}
                            <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $kandidat->id }}">

                            <x-form-select label="Студијски програм" name="StudijskiProgram"
                                           :options="$studijskiProgram->pluck('naziv','id')->toArray()"
                                           :selected="$kandidat->studijskiProgram_id" />
                            <x-form-select label="Школска година уписа" name="SkolskaGodinaUpisa"
                                           :options="$skolskaGodinaUpisa->pluck('naziv','id')->toArray()" />
                        </div>
                        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-secondary-200">
                            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm font-medium hover:bg-gray-300" onclick="document.getElementById('myModal').classList.add('hidden')">Затвори</button>
                            <x-button variant="success">Изврши упис</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{--Modal za upis na master studije KRAJ--}}

        <div>
            <h4>Подаци о студенту &nbsp;
                <a class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600" href="{{"/"}}{{ $kandidat->tipStudija_id == 1 ? 'kandidat' : 'master' }}/{{ $kandidat->id }}/edit">
                    <span class="fa fa-edit" title="Измена"></span>
                </a>
            </h4>
            <ul class="divide-y divide-secondary-200 border border-secondary-200 rounded-lg overflow-hidden mt-2">
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Број Индекса:
                    <strong>{{ $kandidat->brojIndeksa }}</strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Име (име родитеља) презиме:
                    <strong>{{ $kandidat->imeKandidata . " (" . $kandidat->imePrezimeJednogRoditelja . ") " . $kandidat->prezimeKandidata }}</strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">ЈМБГ:
                    <strong>{{ $kandidat->jmbg }}</strong>
                </li>
                @if(!empty($kandidat->datumRodjenja))
                    <li class="px-4 py-3 bg-white text-sm text-secondary-700">Датум рођења:
                        <strong>{{ $kandidat->datumRodjenja->format('d.m.Y') }}</strong>
                    </li>
                @endif
            </ul>
        </div>

        <x-card variant="primary" class="mt-4">
            <x-slot:header>
                <h3 class="text-lg font-semibold text-white">Статус студија</h3>
            </x-slot:header>
            <h3 class="text-center text-xl">
                <strong>Тренутни статус:
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-secondary-100 text-secondary-800">{{ $kandidat->statusUpisa->naziv }}</span>
                </strong>
            </h3>
            <div class="mt-4">
                @if($kandidat->statusUpisa_id == Config::get('constants.statusi.odustao'))
                <div class="mb-4">
                    <label for="skolskaGodinaPonovnogUpisa" class="block text-sm font-medium text-secondary-700 mb-1">Школска година:</label>
                    <select name="skolskaGodinaPonovnogUpisa" id="skolskaGodinaPonovnogUpisa" class="block w-1/3 rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @foreach($skolskaGodinaUpisa as $item)
                            <option value="{{$item->id}}">{{$item->naziv}}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <a href="/student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.upisan') }}/0" class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium" id="buttonPonovnogUpisa">Упиши са новим бројем индекса</a>
                </div>
                @else
                    <a href="/student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.nijeupisan') }}/0" class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium">Врати на статус кандидата</a>
                    @if($kandidat->statusUpisa_id != Config::get('constants.statusi.diplomirao'))
                        <a href="/student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.diplomirao') }}/0" class="inline-flex items-center text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm font-medium ml-2">Дипломирао</a>
                    @endif
                    @if($kandidat->tipStudija_id == 1)
                        <button type="button" class="inline-flex items-center text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm font-medium ml-2" onclick="document.getElementById('myModal').classList.remove('hidden')">
                            Упис на мастер студије
                        </button>
                    @endif
                @endif
            </div>
        </x-card>

        <div class="mt-4">
            <x-card variant="primary">
                <x-slot:header>
                    <h3 class="text-lg font-semibold text-white">Година студија</h3>
                </x-slot:header>
                @if(!$masterStudije->isEmpty())
                    <h4 class="text-md font-semibold mb-2">Мастер студије</h4>
                    <x-table>
                        <thead>
                        <tr>
                            <th>Година</th>
                            <th>Покушај</th>
                            <th>Статус</th>
                            <th>Датум уписа</th>
                            <th>Датум промене</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($masterStudije as $godina)
                            <tr @if($godina->pokusaj > 1) class="bg-yellow-50" @else class="bg-blue-50" @endif>
                                <td>{{ $godina->godina }}</td>
                                <td>{{ $godina->pokusaj }}</td>
                                <td>
                                    @if($godina->statusGodine_id == 1)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 2)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 3)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 4)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 5)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 6)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 7)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @endif
                                </td>
                                <td>@if(!empty($godina->datumUpisa)){{$godina->datumUpisa->format('d.m.Y.')}}@endif</td>
                                <td>@if(!empty($godina->datumPromene)){{$godina->datumPromene->format('d.m.Y.')}}@endif</td>
                                <td>
                                    <div class="inline-flex gap-1 flex-wrap">
                                        @if($godina->statusGodine_id == 1)
                                            <a class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/ponistiUpis?upisId={{ $godina->id }}">
                                                <i class="fa fa-ban"></i> Поништи упис
                                            </a>
                                            <a class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.zavrsio') }}/{{$godina->id}}">
                                                <i class="fa fa-check"></i> Завршио годину
                                            </a>
                                        @elseif($godina->statusGodine_id == 3)
                                            <a class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/upisiStudenta?godina={{ $godina->godina }}&pokusaj={{ $godina->pokusaj }}">Уписао годину
                                            </a>
                                        @endif

                                        @if($godina->statusGodine_id == Config::get('constants.statusi.zamrzao'))
                                            <a href="/student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.upisan') }}/{{$godina->id}}" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Одмрзни годину</a>
                                        @elseif($godina->statusGodine_id == Config::get('constants.statusi.upisan'))
                                            <a href="/student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.zamrzao') }}/{{$godina->id}}" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Замрзни годину</a>
                                        @endif

                                        @if($godina->pokusaj == 1 && ($godina->statusGodine_id == 1 || $godina->statusGodine_id == 4))
                                            <a class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600"
                                               href="{{"/"}}student/{{ $kandidat->id }}/obnova?godina={{ $godina->godina }}&tipStudijaId={{ $godina->tipStudija_id }}">
                                                Обнови годину
                                            </a>
                                        @elseif($godina->pokusaj > 1)
                                            <a class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/obrisiObnovu?upisId={{ $godina->id }}"
                                               onclick="return confirm('Да ли сте сигурни да желите да обришете податке?');">
                                                <span class="fa fa-trash" style="margin: 3px"></span>
                                            </a>
                                        @endif
                                        <a class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600"
                                           href="{{"/"}}student/{{ $godina->id }}/izmenaGodine">
                                            <span class="fa fa-edit" title="Измена"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </x-table>
                @endif

                @if(!$osnovneStudije->isEmpty())
                    <h4 class="text-md font-semibold mb-2 mt-4">Основне студије</h4>
                    <x-table>
                        <thead>
                        <tr>
                            <th>Година</th>
                            <th>Покушај</th>
                            <th>Статус</th>
                            <th>Датум уписа</th>
                            <th>Датум промене</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($osnovneStudije as $godina)
                            <tr @if($godina->pokusaj > 1) class="bg-yellow-50" @else class="bg-blue-50" @endif>
                                <td>{{ $godina->godina }}</td>
                                <td>{{ $godina->pokusaj }}</td>
                                <td>
                                    @if($godina->statusGodine_id == 1)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 2)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 3)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 4)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 5)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 6)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @elseif($godina->statusGodine_id == 7)
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>{{$godina->status?->naziv ?? '-'}}</span>
                                    @endif
                                </td>
                                <td>@if(!empty($godina->datumUpisa)){{$godina->datumUpisa->format('d.m.Y.')}}@endif</td>
                                <td>@if(!empty($godina->datumPromene)){{$godina->datumPromene->format('d.m.Y.')}}@endif</td>
                                <td>
                                    <div class="inline-flex gap-1 flex-wrap">
                                        @if($godina->statusGodine_id == 1)
                                            <a class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/ponistiUpis?upisId={{ $godina->id }}">
                                                <i class="fa fa-ban"></i> Поништи упис
                                            </a>
                                            <a class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.zavrsio') }}/{{$godina->id}}">
                                                <i class="fa fa-check"></i> Завршио годину
                                            </a>
                                        @elseif($godina->statusGodine_id == 3)
                                            <a class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/upisiStudenta?godina={{ $godina->godina }}&pokusaj={{ $godina->pokusaj }}">Уписао годину
                                            </a>
                                        @endif

                                        @if($godina->statusGodine_id == Config::get('constants.statusi.zamrzao'))
                                            <a href="/student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.upisan') }}/{{$godina->id}}" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Одмрзни годину</a>
                                        @elseif($godina->statusGodine_id == Config::get('constants.statusi.upisan'))
                                            <a href="/student/{{ $kandidat->id }}/status/{{ Config::get('constants.statusi.zamrzao') }}/{{$godina->id}}" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Замрзни годину</a>
                                        @endif

                                        @if($godina->pokusaj == 1 && ($godina->statusGodine_id == 1 || $godina->statusGodine_id == 4))
                                            <a class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600"
                                               href="{{"/"}}student/{{ $kandidat->id }}/obnova?godina={{ $godina->godina }}&tipStudijaId={{ $godina->tipStudija_id }}">
                                                Обнови годину
                                            </a>
                                        @elseif($godina->pokusaj > 1)
                                            <a class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700"
                                               href="{{"/"}}student/{{ $kandidat->id }}/obrisiObnovu?upisId={{ $godina->id }}"
                                               onclick="return confirm('Да ли сте сигурни да желите да обришете податке?');">
                                                <span class="fa fa-trash" style="margin: 3px"></span>
                                            </a>
                                        @endif
                                        <a class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600"
                                           href="{{"/"}}student/{{ $godina->id }}/izmenaGodine">
                                            <span class="fa fa-edit" title="Измена"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </x-table>
                @endif
            </x-card>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
    <script>
        function replaceAt(string, index, character) {
            return string.substr(0, index) + character + string.substr(index+character.length);
        }

        $('#skolskaGodinaPonovnogUpisa').change(function () {
            var button = $('#buttonPonovnogUpisa');
            var href = button.attr('href');
            href = replaceAt(href, 20, $('#skolskaGodinaPonovnogUpisa').val());
            button.attr('href', href);
        });
    </script>
@endsection
