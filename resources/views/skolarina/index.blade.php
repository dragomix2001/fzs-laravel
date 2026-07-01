@extends('layouts.layout')
@section('page_heading','Школарина')
@section('section')
    <div class="col-span-12">
        <div>
            <h4 class="text-lg font-semibold text-secondary-800 mb-3">Подаци о студенту</h4>
            <ul class="divide-y divide-secondary-200 border border-secondary-200 rounded-lg overflow-hidden">
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Број Индекса:
                    <strong>
                        {{ $kandidat->brojIndeksa }}
                    </strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Име (име родитеља) презиме:
                    <strong>
                        {{ $kandidat->imeKandidata . " (" . $kandidat->imePrezimeJednogRoditelja . ") " . $kandidat->prezimeKandidata }}
                    </strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Тип Студија:
                    <strong>
                        {{ $kandidat->tipStudija->naziv }}
                    </strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Студијски програм:
                    <strong>
                        {{ $kandidat->program->naziv }}
                    </strong>
                </li>
                <li class="px-4 py-3 bg-white text-sm text-secondary-700">Година студија:
                    <strong>
                        {{ $kandidat->godinaStudija?->naziv ?? '-' }}
                    </strong>
                </li>
                @if(!empty($trenutnaSkolarina))
                    <li class="px-4 py-3 bg-white text-sm text-secondary-700">Година на коју се односи школарина:
                        <strong>
                            {{ ($trenutnaSkolarina->godinaStudija?->naziv ?? '-') . " - " . $trenutnaSkolarina->komentar}}
                        </strong>
                    </li>
                @endif
            </ul>
        </div>
        @if(!empty($trenutnaSkolarina))
            <div class="flex gap-2 mt-4">
                <a href="{{"/"}}skolarina/izmena/{{$trenutnaSkolarina->id}}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                    <span class="fa fa-edit mr-2"></span> Измена школарине
                </a>
                <a href="{{"/"}}skolarina/arhiva/{{$kandidat->id}}" class="inline-flex items-center px-4 py-2 bg-warning-500 hover:bg-warning-400 text-white text-sm font-medium rounded-lg transition-colors">
                    <span class="fa fa-list mr-2"></span> Архива школарине
                </a>
            </div>
        @else
            <div class="form-group text-center mt-4">
                <a href="{{"/"}}skolarina/dodavanje/{{$kandidat->id}}" class="inline-flex items-center px-6 py-3 bg-success-600 hover:bg-success-500 text-white text-base font-medium rounded-lg transition-colors">
                    <span class="fa fa-plus mr-2"></span> Унос школарине
                </a>
                <a href="{{"/"}}skolarina/arhiva/{{$kandidat->id}}" class="inline-flex items-center px-6 py-3 bg-warning-500 hover:bg-warning-400 text-white text-base font-medium rounded-lg transition-colors">
                    <span class="fa fa-list mr-2"></span> Архива школарине
                </a>
            </div>
        @endif
        <hr class="my-4 border-secondary-200">
        @if(!empty($trenutnaSkolarina))
            <div id="skolarina" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="rounded-lg border border-primary-200 overflow-hidden">
                    <div class="bg-primary-600 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa fa-university fa-3x text-white opacity-75"></i>
                            </div>
                            <div class="ml-auto text-right">
                                <div class="text-2xl font-bold text-white">{{number_format($trenutnaSkolarina->iznos, 2, ',', '.')}}</div>
                                <div class="text-primary-100 text-sm">RSD</div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-white flex justify-between items-center text-sm text-secondary-600">
                        <span>Износ: {{$trenutnaSkolarina->komentar}}</span>
                        <i class="fa fa-comment-o text-secondary-400"></i>
                    </div>
                </div>
                <div class="rounded-lg border border-success-200 overflow-hidden">
                    <div class="bg-success-600 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa fa-check-square fa-3x text-white opacity-75"></i>
                            </div>
                            <div class="ml-auto text-right">
                                <div class="text-2xl font-bold text-white">{{number_format($uplacenIznos, 2, ',', '.')}}</div>
                                <div class="text-success-100 text-sm">RSD</div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-white flex justify-between items-center text-sm text-secondary-600">
                        <span>Уплаћено</span>
                        <i class="fa fa-check-circle text-success-400"></i>
                    </div>
                </div>
                <div class="rounded-lg border border-danger-200 overflow-hidden">
                    <div class="bg-danger-600 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa fa-credit-card fa-3x text-white opacity-75"></i>
                            </div>
                            <div class="ml-auto text-right">
                                <div class="text-2xl font-bold text-white">{{number_format($preostaliIznos, 2, ',', '.')}}</div>
                                <div class="text-danger-100 text-sm">RSD</div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-white flex justify-between items-center text-sm text-secondary-600">
                        <span>Дуговање</span>
                        <i class="fa fa-exclamation text-danger-400"></i>
                    </div>
                </div>
            </div>
            <x-card class="border-success-200">
                <x-slot:header>
                    <div class="font-semibold text-secondary-800">Уплате</div>
                </x-slot:header>

                <div class="mb-4">
                    <a href="{{"/"}}skolarina/uplata/{{$trenutnaSkolarina->id}}" class="inline-flex items-center px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors">
                        <span class="fa fa-plus mr-2"></span> Нова уплата
                    </a>
                </div>

                <x-table>
                    <thead>
                    <tr>
                        <th>Студент</th>
                        <th>Износ</th>
                        <th>Датум</th>
                        <th>Назив</th>
                        <th>Измена</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(!empty($trenutneUplate))
                        @foreach($trenutneUplate as $index => $uplata)
                            <tr>
                                <td>{{$uplata->kandidat->imeKandidata . " " . $uplata->kandidat->prezimeKandidata}}</td>
                                <td>{{number_format($uplata->iznos, 2, ',', '.')}}</td>
                                <td>{{$uplata->datum->format('d.m.Y.')}}</td>
                                <td>{{$uplata->naziv}}</td>
                                <td>
                                    <div class="flex gap-1">
                                        <a class="inline-flex items-center px-3 py-1.5 bg-warning-500 hover:bg-warning-400 text-white text-xs font-medium rounded-lg transition-colors"
                                           href="{{"/"}}skolarina/uplata/edit/{{$uplata->id}}"
                                           title="Измена">
                                            <span class="fa fa-edit"></span>
                                        </a>
                                        <a class="inline-flex items-center px-3 py-1.5 bg-danger-600 hover:bg-danger-500 text-white text-xs font-medium rounded-lg transition-colors"
                                           href="{{"/"}}skolarina/uplata/delete/{{$uplata->id}}"
                                           onclick="return confirm('Да ли сте сигурни да желите да обришете податке?');"
                                           title="Брисање">
                                            <span class="fa fa-trash"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </x-table>
            </x-card>
        @endif
    </div>
    <br>
    <br>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
@endsection
