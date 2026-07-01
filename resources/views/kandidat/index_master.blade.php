@extends('layouts.layout')
@section('page_heading','Преглед кандидата за мастер студије')
@section('section')
    <div class="space-y-6">
        <div id="messages">
            @if (Session::get('flash-error'))
                <x-alert type="danger" title="Грешка!">
                    @if(Session::get('flash-error') === 'update')
                        Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                    @elseif(Session::get('flash-error') === 'delete')
                        Дошло је до грешке при брисању података! Молимо вас покушајте поново.
                    @elseif(Session::get('flash-error') === 'upis')
                        Дошло је до грешке при упису кандидата! Молимо вас проверите да ли је кандидат уплатио школарину и покушајте поново.
                    @endif
                </x-alert>
            @elseif(Session::get('flash-success'))
                <x-alert type="success" title="Успех!">
                    @if(Session::get('flash-success') === 'update')
                        Подаци о кандидату су успешно сачувани.
                    @elseif(Session::get('flash-success') === 'delete')
                        Подаци о кандидату су успешно обрисани.
                    @elseif(Session::get('flash-success') === 'upis')
                        Упис кандидата је успешно извршен.
                    @endif
                </x-alert>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($studijskiProgrami as $program)
                <a href="?studijskiProgramId={{ $program->id }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                   {{ Request::input('studijskiProgramId') == $program->id
                       ? 'bg-primary-600 text-white shadow-sm'
                       : 'bg-secondary-100 text-secondary-700 hover:bg-secondary-200' }}">
                    {{ $program->naziv }}
                </a>
            @endforeach
        </div>

        <form id="formaKandidatiOdabir" action="" method="post">
            @csrf
            <x-card>
                <x-table>
                    <x-slot:header>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider w-10"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Име</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Презиме</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">ЈМБГ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Измена</th>
                        </tr>
                    </x-slot:header>
                    @foreach($kandidati as $index => $kandidat)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" id="odabir" name="odabir[{{ $index }}]" value="{{ $kandidat->id }}"
                                       class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">{{ $kandidat->imeKandidata }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">{{ $kandidat->prezimeKandidata }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">{{ $kandidat->jmbg }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex gap-2">
                                    <a href="{{"/"}}master/{{ $kandidat->id }}/edit" class="inline-flex items-center p-2 text-warning-600 hover:text-warning-800 hover:bg-warning-50 rounded-lg transition-colors" title="Измена">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>
                                    <a href="{{"/"}}master/{{ $kandidat->id }}/delete"
                                       onclick="return confirm('Да ли сте сигурни да желите да обришете податке овог кандидата?');"
                                       class="inline-flex items-center p-2 text-danger-600 hover:text-danger-800 hover:bg-danger-50 rounded-lg transition-colors" title="Брисање">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </a>
                                    <a href="{{"/"}}kandidat/{{ $kandidat->id }}/upis" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-success-700 bg-success-50 hover:bg-success-100 rounded-lg transition-colors">Упис кандидата</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        </form>

        <x-card>
            <x-slot:header>За одабране кандидате</x-slot:header>
            <div class="flex gap-3">
                <div id="masovniUpis" class="cursor-pointer inline-flex items-center px-4 py-2 bg-success-600 text-white font-medium rounded-lg hover:bg-success-700 transition-colors">
                    Изврши упис
                </div>
            </div>
        </x-card>
    </div>

    @push('scripts')
    <script>
        document.getElementById('masovniUpis')?.addEventListener('click', function() {
            var forma = document.getElementById('formaKandidatiOdabir');
            if (forma) {
                forma.action = "{{"/"}}master/masovniUpis";
                forma.submit();
            }
        });
    </script>
    @endpush
@endsection
