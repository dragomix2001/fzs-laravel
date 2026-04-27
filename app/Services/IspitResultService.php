<?php

namespace App\Services;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\StatusIspita;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Support\Collection;

class IspitResultService
{
    public function getZapisnikPregled(int $zapisnikId): array
    {
        $zapisnik = ZapisnikOPolaganjuIspita::findOrFail($zapisnikId);
        $zapisnikStudentIds = ZapisnikOPolaganju_Student::where('zapisnik_id', $zapisnikId)
            ->pluck('kandidat_id')
            ->all();

        $studenti = Kandidat::whereIn('id', $zapisnikStudentIds)->get();
        $studentiMap = $studenti->keyBy('id');

        $predmetProgramLookup = $this->buildPredmetProgramLookup($zapisnik->predmet_id, $studentiMap);
        $predmetProgramIdsByKandidat = $this->buildPredmetProgramIdsByKandidat($studentiMap, $predmetProgramLookup);

        $prijavaIds = $this->buildPrijavaIds($zapisnik->rok_id, $predmetProgramIdsByKandidat);
        $polozeniIspitIds = $this->buildPolozeniIspitIds($zapisnik->id, $predmetProgramIdsByKandidat);

        $studijskiProgrami = ZapisnikOPolaganju_StudijskiProgram::where('zapisnik_id', $zapisnikId)->get();
        $statusIspita = StatusIspita::all();
        $polozeniIspiti = PolozeniIspiti::with('kandidat:id,brojIndeksa,imeKandidata,prezimeKandidata')
            ->where('zapisnik_id', $zapisnikId)
            ->get()
            ->sortBy(function (PolozeniIspiti $ispit) {
                return $ispit->kandidat->brojIndeksa ?? '';
            })
            ->values();

        $kandidati = $this->getAvailableKandidatiForZapisnik($zapisnik, $studijskiProgrami);

        return compact('zapisnik', 'studenti', 'studijskiProgrami', 'statusIspita', 'polozeniIspiti', 'polozeniIspitIds', 'prijavaIds', 'kandidati');
    }

    public function savePolozeniIspiti(
        array $ispitIds,
        array $ocenePismeni,
        array $oceneUsmeni,
        array $konacneOcene,
        array $brojBodova,
        array $statusIspita
    ): int {
        $zapisnikId = 0;

        foreach ($ispitIds as $index => $ispitId) {
            $polozeniIspit = PolozeniIspiti::findOrFail($ispitId);
            $polozeniIspit->ocenaPismeni = $ocenePismeni[$index] ?? null;
            $polozeniIspit->ocenaUsmeni = $oceneUsmeni[$index] ?? null;
            $polozeniIspit->konacnaOcena = $konacneOcene[$index] ?? null;
            $polozeniIspit->brojBodova = $brojBodova[$index] ?? null;
            $polozeniIspit->statusIspita = $statusIspita[$index] ?? null;
            $polozeniIspit->indikatorAktivan = true;
            $polozeniIspit->save();

            $zapisnikId = $polozeniIspit->zapisnik_id;
        }

        return $zapisnikId;
    }

    public function updateZapisnikDetails(int $zapisnikId, array $data): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::findOrFail($zapisnikId);
        $zapisnik->vreme = $data['vreme'];
        $zapisnik->ucionica = $data['ucionica'];
        $zapisnik->datum = $data['datum'];
        $zapisnik->datum2 = $data['datum2'];
        $zapisnik->save();
    }

    private function buildPredmetProgramLookup(int $predmetId, Collection $studentiMap): Collection
    {
        $tipStudijaIds = $studentiMap->pluck('tipStudija_id')->unique()->values()->all();
        $studijskiProgramIds = $studentiMap->pluck('studijskiProgram_id')->unique()->values()->all();

        if ($tipStudijaIds === [] || $studijskiProgramIds === []) {
            return collect();
        }

        return PredmetProgram::where('predmet_id', $predmetId)
            ->whereIn('tipStudija_id', $tipStudijaIds)
            ->whereIn('studijskiProgram_id', $studijskiProgramIds)
            ->get()
            ->keyBy(function (PredmetProgram $item) {
                return $this->buildProgramKey($item->tipStudija_id, $item->studijskiProgram_id);
            });
    }

    private function buildPredmetProgramIdsByKandidat(Collection $studentiMap, Collection $predmetProgramLookup): array
    {
        $predmetProgramIdsByKandidat = [];

        foreach ($studentiMap as $kandidatId => $kandidat) {
            $predmetProgram = $predmetProgramLookup->get(
                $this->buildProgramKey($kandidat->tipStudija_id, $kandidat->studijskiProgram_id)
            );

            if ($predmetProgram !== null) {
                $predmetProgramIdsByKandidat[$kandidatId] = $predmetProgram->id;
            }
        }

        return $predmetProgramIdsByKandidat;
    }

    private function buildPrijavaIds(int $rokId, array $predmetProgramIdsByKandidat): array
    {
        if ($predmetProgramIdsByKandidat === []) {
            return [];
        }

        $prijave = PrijavaIspita::where('rok_id', $rokId)
            ->whereIn('kandidat_id', array_keys($predmetProgramIdsByKandidat))
            ->whereIn('predmet_id', array_values(array_unique($predmetProgramIdsByKandidat)))
            ->get()
            ->keyBy(function (PrijavaIspita $prijava) {
                return $prijava->kandidat_id.'_'.$prijava->predmet_id;
            });

        $prijavaIds = [];
        foreach ($predmetProgramIdsByKandidat as $kandidatId => $predmetProgramId) {
            $prijava = $prijave->get($kandidatId.'_'.$predmetProgramId);
            if ($prijava !== null) {
                $prijavaIds[$kandidatId] = $prijava->id;
            }
        }

        return $prijavaIds;
    }

    private function buildPolozeniIspitIds(int $zapisnikId, array $predmetProgramIdsByKandidat): array
    {
        if ($predmetProgramIdsByKandidat === []) {
            return [];
        }

        $polozeniIspiti = PolozeniIspiti::where('zapisnik_id', $zapisnikId)
            ->whereIn('kandidat_id', array_keys($predmetProgramIdsByKandidat))
            ->whereIn('predmet_id', array_values(array_unique($predmetProgramIdsByKandidat)))
            ->get()
            ->keyBy(function (PolozeniIspiti $ispit) {
                return $ispit->kandidat_id.'_'.$ispit->predmet_id;
            });

        $polozeniIspitIds = [];
        foreach ($predmetProgramIdsByKandidat as $kandidatId => $predmetProgramId) {
            $polozeniIspit = $polozeniIspiti->get($kandidatId.'_'.$predmetProgramId);
            if ($polozeniIspit !== null) {
                $polozeniIspitIds[$kandidatId] = $polozeniIspit->id;
            }
        }

        return $polozeniIspitIds;
    }

    private function getAvailableKandidatiForZapisnik(
        ZapisnikOPolaganjuIspita $zapisnik,
        Collection $studijskiProgrami
    ): Collection {
        $studijskiProgramIds = $studijskiProgrami
            ->map(function (ZapisnikOPolaganju_StudijskiProgram $studijskiProgram) {
                return $studijskiProgram->studijskiProgram_id ?? $studijskiProgram->StudijskiProgram_id;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($studijskiProgramIds === []) {
            return collect();
        }

        $programPairs = PredmetProgram::where('predmet_id', $zapisnik->predmet_id)
            ->whereIn('studijskiProgram_id', $studijskiProgramIds)
            ->get(['tipStudija_id', 'studijskiProgram_id'])
            ->unique(function (PredmetProgram $predmetProgram) {
                return $this->buildProgramKey($predmetProgram->tipStudija_id, $predmetProgram->studijskiProgram_id);
            })
            ->values();

        if ($programPairs->isEmpty()) {
            return collect();
        }

        return Kandidat::query()
            ->where(function ($query) use ($programPairs) {
                foreach ($programPairs as $programPair) {
                    $query->orWhere(function ($subQuery) use ($programPair) {
                        $subQuery->where('tipStudija_id', $programPair->tipStudija_id)
                            ->where('studijskiProgram_id', $programPair->studijskiProgram_id);
                    });
                }
            })
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();
    }

    private function buildProgramKey(int $tipStudijaId, int $studijskiProgramId): string
    {
        return $tipStudijaId.'_'.$studijskiProgramId;
    }
}
