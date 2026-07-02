@extends('layouts.layout')
@section('page_heading','Архива школарине')
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
                        {{ $kandidat->godinaStudija->naziv }}
                    </strong>
                </li>
            </ul>
        </div>
        <hr class="my-4 border-secondary-200">
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
                                @if(Session::get('flash-error') === 'update')
                                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                                @elseif(Session::get('flash-error') === 'delete')
                                    Дошло је до грешке при брисању података! Молимо вас покушајте поново.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @elseif(Session::get('flash-success'))
                <div class="rounded-lg bg-success-50 border border-success-200 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-success-800">
                                <strong>Успех!</strong>
                                @if(Session::get('flash-success') === 'update')
                                    Подаци о школарини су успешно сачувани.
                                @elseif(Session::get('flash-success') === 'delete')
                                    Подаци о школарини су успешно обрисани.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <x-card class="border-success-200">
            <x-slot:header>
                <div class="font-semibold text-secondary-800">Школарине</div>
            </x-slot:header>

            <div class="mb-4">
                <a href="{{"/"}}skolarina/dodavanje/{{$kandidat->id}}" class="inline-flex items-center px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors">
                    <span class="fa fa-plus mr-2"></span> Нова школарина
                </a>
            </div>

            <x-table>
                <thead>
                <tr>
                    <th>Тип студија</th>
                    <th>Година студија</th>
                    <th>Износ</th>
                    <th>Број уплата</th>
                    <th>Преостало дуговање</th>
                    <th>Датум</th>
                    <th>Коментар</th>
                    <th>Измена</th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($sveSkolarine))
                    @foreach($sveSkolarine as $index => $item)
                        <tr>
                            <td>{{$item->tipStudija?->naziv ?? '-'}}</td>
                            <td>{{$item->godinaStudija?->naziv ?? '-'}}</td>
                            <td>{{number_format($item->iznos, 2, ',', '.') . " RSD"}}</td>
                            <td>{{$item->uplate->count()}}</td>
                            <td>{{number_format(($item->iznos - $item->uplate->sum('iznos')), 2, ',', '.') . " RSD"}}</td>
                            <td>{{$item->updated_at->format('d.m.Y.')}}</td>
                            <td>{{$item->komentar}}</td>
                            <td>
                                <div class="flex gap-1">
                                    <x-button variant="primary" size="xs" href="/skolarina/arhiva/{{ $item->id }}"
                                       title="Преглед">
                                        <span class="fa fa-eye"></span>
                                    </x-button>
                                    <x-button variant="warning" size="xs" href="/skolarina/arhiva/{{ $item->id }}/edit"
                                       title="Измена">
                                        <span class="fa fa-edit"></span>
                                    </x-button>
                                    <x-button variant="danger" size="xs" href="/skolarina/arhiva/{{ $item->id }}"
                                       onclick="return confirm('Да ли сте сигурни да желите да обришете податке?');"
                                       title="Брисање">
                                        <span class="fa fa-trash"></span>
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </x-table>
        </x-card>
    </div>
    <br>
    <br>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
@endsection
