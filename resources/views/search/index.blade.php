@extends('layouts.layout')
@section('page_heading','Претрага')
@section('section')
    <div class="col-span-12 lg:col-span-10">
        <x-card>
            <x-slot:title>Критеријум за претрагу</x-slot:title>
            <form method="get" action="{{ url('/pretraga') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Претрага студената (име, презиме, број индекса, ЈМБГ)</label>
                        <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="pretraga" name="pretraga" value="{{ $request->pretraga ?? '' }}">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Претрага предмета (назив, шифра)</label>
                        <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" id="pretraga_predmet" name="pretraga_predmet" value="{{ $request->pretraga_predmet ?? '' }}">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Студијски програм</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="studijski_program_id">
                            <option value="">-- Сви програми --</option>
                            @foreach($studijskiProgrami as $program)
                                <option value="{{ $program->id }}" {{ isset($request->studijski_program_id) && $request->studijski_program_id == $program->id ? 'selected' : '' }}>
                                    {{ $program->naziv }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Година студија</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="godina_studija_id">
                            <option value="">-- Све године --</option>
                            @foreach($godineStudija as $godina)
                                <option value="{{ $godina->id }}" {{ isset($request->godina_studija_id) && $request->godina_studija_id == $godina->id ? 'selected' : '' }}>
                                    {{ $godina->naziv }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Статус</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="status_upisa_id">
                            <option value="">-- Сви статуси --</option>
                            @foreach($statusi as $status)
                                <option value="{{ $status->id }}" {{ isset($request->status_upisa_id) && $request->status_upisa_id == $status->id ? 'selected' : '' }}>
                                    {{ $status->naziv }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-secondary-700">Школска година</label>
                        <select class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" name="skolska_godina_id">
                            <option value="">-- Све године --</option>
                            @foreach($skolskeGodine as $godina)
                                <option value="{{ $godina->id }}" {{ isset($request->skolska_godina_id) && $request->skolska_godina_id == $godina->id ? 'selected' : '' }}>
                                    {{ $godina->naziv }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i> Тражи
                    </button>
                    <a href="{{ url('/pretraga') }}" class="inline-flex items-center px-4 py-2 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-undo mr-2"></i> Ресетуј
                    </a>
                </div>
            </form>
        </x-card>
        
        @if(isset($studenti) || isset($predmeti))
            @if(isset($studenti) && $studenti->count() > 0)
            <x-card>
                <x-slot:title>Резултати претраге - Студенти ({{ $studenti->count() }})</x-slot:title>
                <x-table>
                    <thead>
                        <tr>
                            <th>Број Индекса</th>
                            <th>Име</th>
                            <th>Презиме</th>
                            <th>Програм</th>
                            <th>Година</th>
                            <th>Статус</th>
                            <th>Акције</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studenti as $kandidat)
                            <tr>
                                <td>{{$kandidat->brojIndeksa}}</td>
                                <td>{{$kandidat->imeKandidata}}</td>
                                <td>{{$kandidat->prezimeKandidata}}</td>
                                <td>{{ optional($kandidat->program)->naziv }}</td>
                                <td>{{ optional($kandidat->godinaStudija)->naziv }}</td>
                                <td>{{ optional($kandidat->statusUpisa)->naziv }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        <a class="inline-flex items-center px-2.5 py-1.5 bg-warning-500 hover:bg-warning-400 text-white text-xs font-medium rounded transition-colors" href="{{"/"}}{{ $kandidat->tipStudija_id == 1 ? 'kandidat' : 'master' }}/{{ $kandidat->id }}/edit">
                                            <i class="fas fa-edit mr-1"></i>
                                        </a>
                                        <a class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded transition-colors" href="{{"/"}}student/{{ $kandidat->id }}/upis">
                                            Статус
                                        </a>
                                        <a class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded transition-colors" href="{{"/"}}prijava/zaStudenta/{{ $kandidat->id }}">
                                            Испити
                                        </a>
                                        <a class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded transition-colors" href="{{"/"}}izvestaji/potvrdeStudent/{{$kandidat->id}}">
                                            Потврде
                                        </a>
                                        <a class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded transition-colors" href="{{"/"}}skolarina/{{$kandidat->id}}">
                                            Школарина
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </x-card>
            @elseif(isset($studenti))
            <div class="flex items-center gap-2 bg-cyan-50 border border-cyan-200 text-cyan-800 px-4 py-3 rounded-lg text-sm">
                <i class="fas fa-info-circle text-cyan-500"></i>
                Нема резултата за претрагу студената.
            </div>
            @endif
            
            @if(isset($predmeti) && $predmeti->count() > 0)
            <x-card>
                <x-slot:title>Резултати претраге - Предмети ({{ $predmeti->count() }})</x-slot:title>
                <x-table>
                    <thead>
                        <tr>
                            <th>Шифра</th>
                            <th>Назив</th>
                            <th>ЕСПБ</th>
                            <th>Акције</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($predmeti as $predmet)
                            <tr>
                                <td>{{$predmet->sifraPredmeta}}</td>
                                <td>{{$predmet->naziv}}</td>
                                <td>{{$predmet->espb}}</td>
                                <td>
                                    <a class="inline-flex items-center px-2.5 py-1.5 bg-warning-500 hover:bg-warning-400 text-white text-xs font-medium rounded transition-colors" href="{{"/"}}predmet/{{ $predmet->id }}/edit">
                                        <i class="fas fa-edit mr-1"></i> Измени
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </x-card>
            @elseif(isset($predmeti))
            <div class="flex items-center gap-2 bg-cyan-50 border border-cyan-200 text-cyan-800 px-4 py-3 rounded-lg text-sm">
                <i class="fas fa-info-circle text-cyan-500"></i>
                Нема резултата за претрагу предмета.
            </div>
            @endif
        @endif
    </div>
@endsection
