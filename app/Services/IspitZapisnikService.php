<?php

namespace App\Services;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\ZapisnikOPolaganjuIspita;

class IspitZapisnikService
{
    public function getZapisniciForIndex(array $filters): array
    {
        $query = ZapisnikOPolaganjuIspita::query()
            ->with([
                'predmet:id,naziv',
                'ispitniRok:id,naziv',
                'profesor:id,ime,prezime',
            ])
            ->withCount('studenti')
            ->where(['arhiviran' => false]);

        if (! empty($filters['filter_predmet_id'])) {
            $query = $query->where(['predmet_id' => $filters['filter_predmet_id']]);
        }
        if (! empty($filters['filter_rok_id'])) {
            $query = $query->where(['rok_id' => $filters['filter_rok_id']]);
        }
        if (! empty($filters['filter_profesor_id'])) {
            $query = $query->where(['profesor_id' => $filters['filter_profesor_id']]);
        }

        $zapisnici = $query->get();
        $predmeti = Predmet::all();
        $profesori = Profesor::all();
        $aktivniIspitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        return compact('zapisnici', 'predmeti', 'profesori', 'aktivniIspitniRok');
    }

    public function getCreateZapisnikData(): array
    {
        $aktivniIspitniRok = AktivniIspitniRokovi::all();
        if (count($aktivniIspitniRok->all()) == 0) {
            $aktivniIspitniRok = null;
        }

        $predmeti = Predmet::all();
        $profesori = Profesor::all();

        return compact('aktivniIspitniRok', 'predmeti', 'profesori');
    }

    public function getZapisnikPredmetData(int $rokId): array
    {
        $prijava = PrijavaIspita::where([
            'rok_id' => $rokId,
        ])->select('predmet_id', 'profesor_id')->get();

        $predmetProgramIds = array_unique($prijava->pluck('predmet_id')->all());
        $predmetId = PredmetProgram::whereIn('id', $predmetProgramIds)
            ->pluck('predmet_id')
            ->unique()
            ->all();
        $profesorId = array_unique($prijava->pluck('profesor_id')->all());

        $profesori = Profesor::whereIn('id', $profesorId)->exists()
            ? Profesor::whereIn('id', $profesorId)->get()
            : Profesor::all();

        $predmeti = Predmet::whereIn('id', $predmetId)->get();

        return ['predmeti' => $predmeti, 'profesori' => $profesori];
    }

    public function getZapisnikStudenti(int $predmetId, int $rokId, int $profesorId): array
    {
        $predmetProgramIds = PredmetProgram::where('predmet_id', $predmetId)->pluck('id');

        $prijava = PrijavaIspita::whereIn('predmet_id', $predmetProgramIds)
            ->where([
                'rok_id' => $rokId,
                'profesor_id' => $profesorId,
            ])->get();

        $prijavaId = $prijava->isEmpty() ? null : $prijava->first()->id;
        $studentiId = $prijava->pluck('kandidat_id')->all();

        $message = count($studentiId) == 0
            ? '<div class="alert alert-dismissable alert-info"><strong>Обавештење: </strong> Нема студената пријављених за испит.</div>'
            : '';

        return [
            'message' => $message,
            'kandidati' => Kandidat::whereIn('id', $studentiId)->select(['id', 'brojIndeksa', 'imeKandidata', 'prezimeKandidata'])->get(),
            'prijavaId' => $prijavaId,
        ];
    }

    public function getArhiviraniZapisnici(): array
    {
        $arhiviraniZapisnici = ZapisnikOPolaganjuIspita::query()
            ->with([
                'predmet:id,naziv',
                'ispitniRok:id,naziv',
                'profesor:id,ime,prezime',
            ])
            ->withCount('studenti')
            ->where(['arhiviran' => true])
            ->get();
        $aktivniIspitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        return compact('arhiviraniZapisnici', 'aktivniIspitniRok');
    }

    public function arhivirajZapisnik(int $id): void
    {
        $zapsinik = ZapisnikOPolaganjuIspita::find($id);
        $zapsinik->arhiviran = true;
        $zapsinik->save();
    }

    public function arhivirajZapisnikeZaRok(int $rokId): void
    {
        $zapsinici = ZapisnikOPolaganjuIspita::where(['rok_id' => $rokId])->get();

        foreach ($zapsinici as $zapsinik) {
            $zapsinik->arhiviran = true;
            $zapsinik->save();
        }
    }
}
