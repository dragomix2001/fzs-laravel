@extends('layouts.layout')
@section('page_heading','Унос кандидата - друга страна')
@section('section')
    <div class="max-w-5xl mx-auto space-y-6">
        <form role="form" method="post" action="{{ url('/kandidat') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="page" value="2"/>
            <input type="hidden" name="insertedId" value="{{ $insertedId }}"/>

            <x-card>
                <x-slot:header>Само за прву годину</x-slot:header>
                <div class="space-y-4">
                    @foreach(['prvi'=>'1.', 'drugi'=>'2.', 'treci'=>'3.', 'cetvrti'=>'4.'] as $key => $label)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-select name="{{ $key }}Razred" label="{{ $label }} разред"
                            :options="$opstiUspehSrednjaSkola->pluck('naziv', 'id')->toArray()" />
                        <x-form-input name="SrednjaOcena{{ ucfirst($key) == 'Cetvrti' ? '4' : ($key == 'prvi' ? '1' : ($key == 'drugi' ? '2' : ($key == 'treci' ? '3' : '')) ) }}" label="Средња оцена" />
                    </div>
                    @endforeach

                    <hr class="border-secondary-200">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-select name="OpstiUspehSrednjaSkola" label="Општи успех средња школа"
                            :options="$opstiUspehSrednjaSkola->pluck('naziv', 'id')->toArray()" />
                        <x-form-input name="SrednjaOcenaSrednjaSkola" label="Средња оцена средња школа" />
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Спортско ангажовање</x-slot:header>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">р.б.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Спорт</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Клуб</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Узраст (од - до)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Број година</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-secondary-200">
                            @foreach([1, 2, 3] as $i)
                            <tr>
                                <td class="px-4 py-3 text-sm text-secondary-900">{{ $i }}.</td>
                                <td class="px-4 py-3">
                                    <select name="sport{{ $i }}" id="sport{{ $i }}"
                                            class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="0">НЕМА</option>
                                        @foreach($sport as $item)
                                            <option value="{{$item->id}}">{{$item->naziv}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3"><input type="text" name="klub{{ $i }}" id="klub{{ $i }}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                                <td class="px-4 py-3"><input type="text" name="uzrast{{ $i }}" id="uzrast{{ $i }}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                                <td class="px-4 py-3"><input type="text" name="godine{{ $i }}" id="godine{{ $i }}" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <x-form-input name="VisinaKandidata" label="Висина кандидата (cm)" />
                    <x-form-input name="TelesnaTezinaKandidata" label="Телесна тежина кандидата (kg)" />
                </div>
            </x-card>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <x-card>
                    <x-slot:header>ДОКУМЕНТА - за упис на I ГОДИНУ СТУДИЈА</x-slot:header>
                    <div class="space-y-3">
                        @foreach($dokumentiPrvaGodina as $i=>$dokument)
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="dokumentiPrva[{{ $i }}]" value="{{$dokument->id}}"
                                   class="mt-1 rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <div class="flex-1">
                                <label class="text-sm text-secondary-700">{{ $dokument->naziv }}</label>
                                <input type="file" class="block w-full text-sm text-secondary-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 mt-1" name="documentUploadsPrva[{{ $dokument->id }}]" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </x-card>

                <x-card>
                    <x-slot:header>ДОКУМЕНТА - за упис на II, III и IV ГОДИНУ СТУДИЈА и прелазак са другог факултета</x-slot:header>
                    <div class="space-y-3">
                        @foreach($dokumentiOstaleGodine as $i=>$dokument)
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="dokumentiDruga[{{ $i }}]" value="{{$dokument->id}}"
                                   class="mt-1 rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            <div class="flex-1">
                                <label class="text-sm text-secondary-700">{{ $dokument->naziv }}</label>
                                <input type="file" class="block w-full text-sm text-secondary-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 mt-1" name="documentUploadsDruga[{{ $dokument->id }}]" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <x-card>
                <x-slot:header>Остало</x-slot:header>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form-input name="BrojBodovaTest" label="Број бодова тест" />
                    <x-form-input name="BrojBodovaSkola" label="Број бодова школа" />
                    <x-form-input name="ukupniBrojBodova" label="Укупни број бодова" />
                    <x-form-input name="UpisniRok" label="Уписни рок" />
                </div>
                <div class="flex justify-center pt-4">
                    <x-button type="submit" size="lg">Даље</x-button>
                </div>
            </x-card>
        </form>
    </div>

    @push('scripts')
    <script src="{{"/"}}js/kandidat-create-part-2.js"></script>
    @endpush
@endsection
