<?php

namespace App\Services;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\Kandidat;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Collection;

/**
 * DiplomskiPrijavaService — CRUD operations for all three diplomski registration stages:
 * tema (topic), odbrana (defense) and polaganje (final examination).
 *
 * Extracted from PrijavaService to enforce single-responsibility per domain area.
 *
 * @see PrijavaController
 */
class DiplomskiPrijavaService
{
    // -------------------------------------------------------------------------
    // region DIPLOMSKI RAD - TEMA
    // -------------------------------------------------------------------------

    /**
     * Get data for the diplomski tema create form.
     *
     * @return array{kandidat: Kandidat, profesor: Collection<int, Profesor>, predmeti: Collection<int, PredmetProgram>}
     */
    public function getDiplomskiTemaData(int $kandidatId): array
    {
        $kandidat = Kandidat::findOrFail($kandidatId);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();

        return compact('kandidat', 'profesor', 'predmeti');
    }

    /**
     * Store a new diplomski tema.
     */
    public function storeDiplomskiTema(array $data): DiplomskiPrijavaTeme
    {
        $prijavaTeme = new DiplomskiPrijavaTeme($data);
        $prijavaTeme->save();

        return $prijavaTeme;
    }

    /**
     * Get data for the diplomski tema edit form.
     *
     * @return array{kandidat: Kandidat, profesor: Collection<int, Profesor>, predmeti: Collection<int, PredmetProgram>, diplomskiRadTema: DiplomskiPrijavaTeme|null}
     */
    public function getEditDiplomskiTemaData(int $kandidatId): array
    {
        $kandidat = Kandidat::findOrFail($kandidatId);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        return compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema');
    }

    /**
     * Update an existing diplomski tema.
     */
    public function updateDiplomskiTema(int $temaId, array $data, bool $indikatorOdobreno): DiplomskiPrijavaTeme
    {
        $prijavaTeme = DiplomskiPrijavaTeme::findOrFail($temaId);
        $prijavaTeme->fill($data);
        $prijavaTeme->indikatorOdobreno = (bool) $indikatorOdobreno;
        $prijavaTeme->save();

        return $prijavaTeme;
    }

    /**
     * Delete diplomski tema for a kandidat.
     */
    public function deleteDiplomskiTema(int $kandidatId): Kandidat
    {
        $kandidat = Kandidat::findOrFail($kandidatId);

        DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->firstOrFail()->delete();

        return $kandidat;
    }

    // -------------------------------------------------------------------------
    // endregion
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // region DIPLOMSKI RAD - ODBRANA
    // -------------------------------------------------------------------------

    /**
     * Get data for the diplomski odbrana create form.
     * Returns null for diplomskiRadTema if no tema exists yet.
     *
     * @return array{kandidat: Kandidat, profesor: Collection<int, Profesor>, predmeti: Collection<int, PredmetProgram>, diplomskiRadTema: DiplomskiPrijavaTeme|null}
     */
    public function getDiplomskiOdbranaData(int $kandidatId): array
    {
        $kandidat = Kandidat::findOrFail($kandidatId);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        return compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema');
    }

    /**
     * Store a new diplomski odbrana.
     */
    public function storeDiplomskiOdbrana(array $data): DiplomskiPrijavaOdbrane
    {
        $prijavaOdbrane = new DiplomskiPrijavaOdbrane($data);
        $prijavaOdbrane->save();

        return $prijavaOdbrane;
    }

    /**
     * Get data for the diplomski odbrana edit form.
     *
     * @return array{kandidat: Kandidat, profesor: Collection<int, Profesor>, predmeti: Collection<int, PredmetProgram>, diplomskiRadTema: DiplomskiPrijavaTeme|null, diplomskiRadOdbrana: DiplomskiPrijavaOdbrane|null}
     */
    public function getEditDiplomskiOdbranaData(int $kandidatId): array
    {
        $kandidat = Kandidat::findOrFail($kandidatId);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $diplomskiRadOdbrana = DiplomskiPrijavaOdbrane::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        return compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema', 'diplomskiRadOdbrana');
    }

    /**
     * Update an existing diplomski odbrana.
     */
    public function updateDiplomskiOdbrana(int $odbranaId, array $data, bool $indikatorOdobreno): DiplomskiPrijavaOdbrane
    {
        $prijavaOdbrane = DiplomskiPrijavaOdbrane::findOrFail($odbranaId);
        $prijavaOdbrane->fill($data);
        $prijavaOdbrane->indikatorOdobreno = (bool) $indikatorOdobreno;
        $prijavaOdbrane->save();

        return $prijavaOdbrane;
    }

    /**
     * Delete diplomski odbrana for a kandidat.
     */
    public function deleteDiplomskiOdbrana(int $kandidatId): Kandidat
    {
        $kandidat = Kandidat::findOrFail($kandidatId);

        DiplomskiPrijavaOdbrane::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->firstOrFail()->delete();

        return $kandidat;
    }

    // -------------------------------------------------------------------------
    // endregion
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // region DIPLOMSKI RAD - POLAGANJE
    // -------------------------------------------------------------------------

    /**
     * Get data for the diplomski polaganje create form.
     *
     * @return array{kandidat: Kandidat, profesor: Collection<int, Profesor>, predmeti: Collection<int, PredmetProgram>, diplomskiRadTema: DiplomskiPrijavaTeme|null, ispitniRok: Collection<int, AktivniIspitniRokovi>}
     */
    public function getDiplomskiPolaganjeData(int $kandidatId): array
    {
        $kandidat = Kandidat::findOrFail($kandidatId);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        return compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema', 'ispitniRok');
    }

    /**
     * Store a new diplomski polaganje.
     */
    public function storeDiplomskiPolaganje(array $data): DiplomskiPolaganje
    {
        $prijavaPolaganje = new DiplomskiPolaganje($data);
        $prijavaPolaganje->save();

        return $prijavaPolaganje;
    }

    /**
     * Get data for the diplomski polaganje edit form.
     *
     * @return array{kandidat: Kandidat, profesor: Collection<int, Profesor>, predmeti: Collection<int, PredmetProgram>, diplomskiRadTema: DiplomskiPrijavaTeme|null, diplomskiRadPolaganje: DiplomskiPolaganje|null, ispitniRok: Collection<int, AktivniIspitniRokovi>}
     */
    public function getEditDiplomskiPolaganjeData(int $kandidatId): array
    {
        $kandidat = Kandidat::findOrFail($kandidatId);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $diplomskiRadPolaganje = DiplomskiPolaganje::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        return compact(
            'kandidat', 'profesor', 'predmeti',
            'diplomskiRadTema', 'diplomskiRadPolaganje', 'ispitniRok'
        );
    }

    /**
     * Update an existing diplomski polaganje.
     */
    public function updateDiplomskiPolaganje(int $polaganjeId, array $data): DiplomskiPolaganje
    {
        $prijavaPolaganje = DiplomskiPolaganje::findOrFail($polaganjeId);
        $prijavaPolaganje->fill($data);
        $prijavaPolaganje->save();

        return $prijavaPolaganje;
    }

    /**
     * Delete diplomski polaganje for a kandidat.
     */
    public function deleteDiplomskiPolaganje(int $kandidatId): Kandidat
    {
        $kandidat = Kandidat::findOrFail($kandidatId);

        DiplomskiPolaganje::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->firstOrFail()->delete();

        return $kandidat;
    }

    // -------------------------------------------------------------------------
    // endregion
    // -------------------------------------------------------------------------
}
