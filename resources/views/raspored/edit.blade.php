@extends('layouts.layout')
@section('page_heading','Измени час у распореду')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Измени час у распореду</h2>

    <form method="POST" action="{{ route('raspored.update', $raspored->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-select label="Предмет *" name="predmet_id" required
                           :options="$predmeti->pluck('naziv','id')->toArray()"
                           :selected="$raspored->predmet_id" />
            <x-form-select label="Професор *" name="profesor_id" required
                           :options="$profesori->mapWithKeys(function($p) { return [$p->id => $p->ime.' '.$p->prezime]; })->toArray()"
                           :selected="$raspored->profesor_id" />

            <x-form-select label="Студијски програм *" name="studijski_program_id" required
                           :options="$studijskiProgrami->pluck('naziv','id')->toArray()"
                           :selected="$raspored->studijski_program_id" />
            <x-form-select label="Година студија *" name="godina_studija_id" required
                           :options="$godineStudija->pluck('naziv','id')->toArray()"
                           :selected="$raspored->godina_studija_id" />

            <x-form-select label="Семестар *" name="semestar_id" required
                           :options="$semestri->pluck('naziv','id')->toArray()"
                           :selected="$raspored->semestar_id" />
            <x-form-select label="Школска година *" name="skolska_godina_id" required
                           :options="$skolskeGodine->mapWithKeys(function($g) { return [$g->id => $g->godina.'/'.($g->godina+1)]; })->toArray()"
                           :selected="$raspored->skolska_godina_id" />

            <x-form-select label="Облик наставе *" name="oblik_nastave_id" required
                           :options="$obliciNastave->pluck('naziv','id')->toArray()"
                           :selected="$raspored->oblik_nastave_id" />
            <x-form-select label="Дан *" name="dan" required
                           :options="['1' => 'Понедељак', '2' => 'Уторак', '3' => 'Среда', '4' => 'Четвртак', '5' => 'Петак', '6' => 'Субота', '7' => 'Недеља']"
                           :selected="(string)$raspored->dan" />

            <x-form-input label="Време од *" name="vreme_od" type="time" :value="$raspored->vreme_od" required />
            <x-form-input label="Време до *" name="vreme_do" type="time" :value="$raspored->vreme_do" required />
            <x-form-input label="Просторија" name="prostorija" type="text" :value="$raspored->prostorija" placeholder="нпр. Сала 1" />
            <x-form-input label="Група" name="grupa" type="text" :value="$raspored->grupa" placeholder="нпр. А, Б" />
        </div>

        <div class="mt-6 flex gap-2">
            <x-button variant="primary">Сачувај</x-button>
            <x-button variant="secondary-soft" size="md" href="{{ route('raspored.index') }}">Откажи</x-button>
        </div>
    </form>
</div>
@endsection
