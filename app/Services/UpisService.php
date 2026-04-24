<?php

namespace App\Services;

use App\Models\ArhivIndeksa;
use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\TipStudija;
use App\Models\UpisGodine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class UpisService
{
    public function __construct(private readonly DocumentReviewService $documentReviewService) {}

    public function registrujKandidata(int $id): void
    {
        $kandidat = Kandidat::find($id);

        $vecUpisan = UpisGodine::where([
            'kandidat_id' => $id,
            'tipStudija_id' => $kandidat->tipStudija_id])
            ->get();

        if (count($vecUpisan) > 0) {
            return;
        }

        $tipStudija = TipStudija::find($kandidat->tipStudija_id);
        $skrNaziv = $tipStudija ? $tipStudija->skrNaziv : null;

        if ($skrNaziv === 'OAS') {
            $upis = new UpisGodine;
            $upis->kandidat_id = $id;
            $upis->godina = 1;
            $upis->pokusaj = 1;
            $upis->tipStudija_id = $kandidat->tipStudija_id;
            $upis->studijskiProgram_id = $kandidat->studijskiProgram_id;
            if ($kandidat->godinaStudija_id == 1) {
                $upis->statusGodine_id = 1;
                $upis->skolskaGodina_id = $kandidat->skolskaGodinaUpisa_id;
                $upis->datumUpisa = Carbon::now();
            } else {
                $upis->statusGodine_id = 3;
                $upis->skolskaGodina_id = null;
                $upis->datumUpisa = null;
            }
            $upis->save();

            $upis = new UpisGodine;
            $upis->kandidat_id = $id;
            $upis->godina = 2;
            $upis->pokusaj = 1;
            $upis->tipStudija_id = $kandidat->tipStudija_id;
            $upis->studijskiProgram_id = $kandidat->studijskiProgram_id;
            if ($kandidat->godinaStudija_id == 2) {
                $upis->statusGodine_id = 1;
                $upis->skolskaGodina_id = $kandidat->skolskaGodinaUpisa_id;
                $upis->datumUpisa = Carbon::now();
            } else {
                $upis->statusGodine_id = 3;
                $upis->skolskaGodina_id = null;
                $upis->datumUpisa = null;
            }
            $upis->save();

            $upis = new UpisGodine;
            $upis->kandidat_id = $id;
            $upis->godina = 3;
            $upis->pokusaj = 1;
            $upis->tipStudija_id = $kandidat->tipStudija_id;
            $upis->studijskiProgram_id = $kandidat->studijskiProgram_id;
            if ($kandidat->godinaStudija_id == 3) {
                $upis->statusGodine_id = 1;
                $upis->skolskaGodina_id = $kandidat->skolskaGodinaUpisa_id;
                $upis->datumUpisa = Carbon::now();
            } else {
                $upis->statusGodine_id = 3;
                $upis->skolskaGodina_id = null;
                $upis->datumUpisa = null;
            }
            $upis->save();

            $upis = new UpisGodine;
            $upis->kandidat_id = $id;
            $upis->godina = 4;
            $upis->pokusaj = 1;
            $upis->tipStudija_id = $kandidat->tipStudija_id;
            $upis->studijskiProgram_id = $kandidat->studijskiProgram_id;
            if ($kandidat->godinaStudija_id == 4) {
                $upis->statusGodine_id = 1;
                $upis->skolskaGodina_id = $kandidat->skolskaGodinaUpisa_id;
                $upis->datumUpisa = Carbon::now();
            } else {
                $upis->statusGodine_id = 3;
                $upis->skolskaGodina_id = null;
                $upis->datumUpisa = null;
            }
            $upis->save();
        } elseif ($skrNaziv === 'MAS') {
            $upis = new UpisGodine;
            $upis->kandidat_id = $id;
            $upis->godina = 1;
            $upis->pokusaj = 1;
            $upis->tipStudija_id = $kandidat->tipStudija_id;
            $upis->studijskiProgram_id = $kandidat->studijskiProgram_id;
            $upis->statusGodine_id = 1;
            $upis->skolskaGodina_id = $kandidat->skolskaGodinaUpisa_id;
            $upis->datumUpisa = Carbon::now();
            $upis->datumPromene = Carbon::now();
            $upis->save();
        } elseif ($skrNaziv === 'DAS') {
            $upis = new UpisGodine;
            $upis->kandidat_id = $id;
            $upis->godina = 1;
            $upis->pokusaj = 1;
            $upis->tipStudija_id = $kandidat->tipStudija_id;
            $upis->studijskiProgram_id = $kandidat->studijskiProgram_id;
            $upis->statusGodine_id = 1;
            $upis->skolskaGodina_id = $kandidat->skolskaGodinaUpisa_id;
            $upis->datumUpisa = Carbon::now();
            $upis->datumPromene = Carbon::now();
            $upis->save();
        }
    }

    // Upis master studija za postojećeg kandidata
    public function upisMasterPostojeciKandidat(int $kandidatId, int $studijskiProgramId, int $skolskaGodinaUpisaId): int|false
    {
        $original = Kandidat::find($kandidatId);
        if ($original === null) {
            return false;
        }

        $this->ensureKandidatHasCompleteRequiredDocuments($original);

        $kandidat = $original->replicate();
        $tipStudija = TipStudija::find($kandidat->tipStudija_id);
        $tipMaster = TipStudija::where('skrNaziv', 'MAS')->first();
        if ($tipStudija && $tipStudija->skrNaziv === 'OAS' && $tipMaster) {
            $kandidat->tipStudija_id = $tipMaster->id;
            $kandidat->studijskiProgram_id = $studijskiProgramId;
            $kandidat->skolskaGodinaUpisa_id = $skolskaGodinaUpisaId;
            $kandidat->statusUpisa_id = Config::get('constants.statusi.upisan');
            $kandidat->brojIndeksa = null;
            $kandidat->save();
            $this->generisiBrojIndeksa($kandidat->id);
            $this->registrujKandidata($kandidat->id);

            return $kandidat->id;
        }

        return false;
    }

    public function upisiGodinu(int $id, int $godina, int $skolskaGodinaUpisaId): bool
    {
        $kandidat = Kandidat::find($id);
        $this->ensureKandidatHasCompleteRequiredDocuments($kandidat);
        $upis = UpisGodine::where([
            'kandidat_id' => $id,
            'godina' => $godina,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $upis->statusGodine_id = 1;
        $upis->skolskaGodina_id = $skolskaGodinaUpisaId;
        $upis->datumUpisa = Carbon::now();

        if (! $upis->save()) {
            return false;
        }

        $kandidat->godinaStudija_id = $godina;

        if (! $kandidat->save()) {
            return false;
        }

        if (empty($kandidat->brojIndeksa)) {
            $this->generisiBrojIndeksa($kandidat->id);
        }

        return true;
    }

    /**
     * Upiši studenta u godinu i ažuriraj prethodnu godinu kao položenu.
     */
    public function upisiStudentaGodinu(int $kandidatId, int $godina, int $pokusaj): void
    {
        $kandidat = Kandidat::find($kandidatId);
        $this->ensureKandidatHasCompleteRequiredDocuments($kandidat);

        if ($godina > 1) {
            $max = UpisGodine::where(['kandidat_id' => $kandidatId, 'godina' => $godina - 1])->max('pokusaj');
            $prethodnaGodina = UpisGodine::where([
                'kandidat_id' => $kandidatId,
                'godina' => $godina - 1,
                'pokusaj' => $max,
                'tipStudija_id' => $kandidat->tipStudija_id,
            ])->first();
            $prethodnaGodina->statusGodine_id = 5;
            $prethodnaGodina->save();
        }

        $upisaneGodine = UpisGodine::where([
            'kandidat_id' => $kandidatId,
            'godina' => $godina,
            'pokusaj' => $pokusaj,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $upisaneGodine->statusGodine_id = 1;
        $upisaneGodine->datumUpisa = Carbon::now();
        $upisaneGodine->save();

        $kandidat->godinaStudija_id = $godina;
        $kandidat->save();
    }

    /**
     * Obnovi godinu — zatvori prethodni pokušaj i kreiraj novi.
     */
    public function obnoviGodinu(int $kandidatId, int $godina, int $tipStudijaId): void
    {
        $kandidat = Kandidat::find($kandidatId);

        $poslednjiPokusaj = UpisGodine::where([
            'kandidat_id' => $kandidatId,
            'godina' => $godina,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->max('pokusaj');

        $prethodnaGodina = UpisGodine::where([
            'kandidat_id' => $kandidatId,
            'godina' => $godina,
            'pokusaj' => $poslednjiPokusaj,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $prethodnaGodina->statusGodine_id = 4;
        $prethodnaGodina->datumPromene = Carbon::now();
        $prethodnaGodina->save();

        $obnovaGodine = new UpisGodine;
        $obnovaGodine->kandidat_id = $kandidatId;
        $obnovaGodine->godina = $godina;
        $obnovaGodine->tipStudija_id = $tipStudijaId;
        $obnovaGodine->studijskiProgram_id = $kandidat->studijskiProgram_id;
        $obnovaGodine->pokusaj = $poslednjiPokusaj + 1;
        $obnovaGodine->statusGodine_id = 1;
        $obnovaGodine->datumUpisa = Carbon::now();
        $obnovaGodine->save();

        $kandidat->godinaStudija_id = $godina;
        $kandidat->save();
    }

    /**
     * Obriši obnovu godine (upis zapis).
     */
    public function obrisiObnovuGodine(int $upisId): void
    {
        UpisGodine::destroy($upisId);
    }

    /**
     * Poništi upis — postavi status na 3 (poništen).
     */
    public function ponistiUpis(int $upisId): void
    {
        $upis = UpisGodine::find($upisId);
        $upis->statusGodine_id = 3;
        $upis->save();
    }

    /**
     * Promeni status kandidata i aktivne godine.
     *
     * @param  array<string, int>  $statusi  Mapa naziva statusa na ID vrednosti
     */
    public function promeniStatus(int $kandidatId, int $statusId, int $godinaId, array $statusi): bool
    {
        $kandidat = Kandidat::find($kandidatId);

        if ($statusId == $statusi['upisan']) {
            $this->ensureKandidatHasCompleteRequiredDocuments($kandidat);
        }

        if ($statusId == $statusi['zavrsio'] || $statusId == $statusi['odustao'] || $statusId == $statusi['obnovio']) {
            // Samo ažuriraj datum statusa (bez promene statusUpisa_id)
        } elseif ($kandidat->statusUpisa_id == $statusi['odustao'] && $statusId == 1) {
            // Ponovo upiši ispisanog kandidata
            $kandidat->statusUpisa_id = $statusId;
            $kandidat->datumStatusa = Carbon::now();
            $kandidat->skolskaGodinaUpisa_id = $godinaId;
            $kandidat->save();
            $this->generisiBrojIndeksa($kandidat->id);

            return true; // Signal controller to Redirect::back() early
        } else {
            $kandidat->statusUpisa_id = $statusId;
        }

        $kandidat->datumStatusa = Carbon::now();

        if ($godinaId != 0) {
            $aktivnaGodina = UpisGodine::find($godinaId);
            $aktivnaGodina->statusGodine_id = $statusId;
            if ($statusId == $statusi['upisan']) {
                $aktivnaGodina->datumUpisa = Carbon::now();
            }
            $aktivnaGodina->datumPromene = Carbon::now();
            $aktivnaGodina->save();
        }

        $kandidat->save();

        return false;
    }

    /**
     * Sačuvaj izmenu godine (status, školska godina, datumi).
     *
     * @return int Kandidat ID za redirect
     */
    public function sacuvajIzmenuGodine(int $upisId, int $statusGodineId, int $skolskaGodinaId, ?string $datumUpisa, ?string $datumUpisaFormat, ?string $datumPromene, ?string $datumPromeneFormat): int
    {
        $upisGodine = UpisGodine::find($upisId);
        $upisGodine->statusGodine_id = $statusGodineId;
        $upisGodine->skolskaGodina_id = $skolskaGodinaId;

        $upisGodine->datumUpisa = (empty($datumUpisa) || empty($datumUpisaFormat)) ?
            null : $datumUpisa;

        $upisGodine->datumPromene = (empty($datumPromene) || empty($datumPromeneFormat)) ?
            null : $datumPromene;

        $saved = $upisGodine->save();
        if (! $saved) {
            throw new \RuntimeException('Дошло је до грешке при чувању измене године.');
        }

        return $upisGodine->kandidat_id;
    }

    public function generisiBrojIndeksa(int $kandidatId): void
    {
        $kandidat = Kandidat::find($kandidatId);

        $arhivIndeksa = ArhivIndeksa::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'skolskaGodinaUpisa_id' => $kandidat->skolskaGodinaUpisa_id,
        ])->first();

        if ($arhivIndeksa == null) {
            $prviZapis = new ArhivIndeksa;
            $prviZapis->tipStudija_id = $kandidat->tipStudija_id;
            $prviZapis->skolskaGodinaUpisa_id = $kandidat->skolskaGodinaUpisa_id;
            $prviZapis->indeks = 1;
            $prviZapis->save();
            $poslednjiBrojIndeksa = 0;
        } else {
            $poslednjiBrojIndeksa = $arhivIndeksa->indeks;
            $arhivIndeksa->indeks++;
            $arhivIndeksa->save();
        }

        $kandidati = Kandidat::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'skolskaGodinaUpisa_id' => $kandidat->skolskaGodinaUpisa_id,
        ])->pluck('brojIndeksa')->all();

        $postojeciIndeksi = array_map(function ($item) {
            return (int) substr($item, 1, 3);
        }, $kandidati);

        $noviBrojIndeksa = $poslednjiBrojIndeksa + 1;

        while (in_array($noviBrojIndeksa, $postojeciIndeksi)) {
            $noviBrojIndeksa++;
        }

        switch (strlen((string) $noviBrojIndeksa)) {
            case 1: $noviBrojIndeksa = '00'.$noviBrojIndeksa;
                break;
            case 2: $noviBrojIndeksa = '0'.$noviBrojIndeksa;
                break;
            case 3: break;
        }

        $skolskaGodina = SkolskaGodUpisa::find($kandidat->skolskaGodinaUpisa_id)->naziv;
        $brojIndeksa = $kandidat->tipStudija_id.$noviBrojIndeksa.'/'.substr($skolskaGodina, 0, 4);
        $kandidat->brojIndeksa = $brojIndeksa;
        $kandidat->save();
    }

    private function ensureKandidatHasCompleteRequiredDocuments(?Kandidat $kandidat): void
    {
        if ($kandidat === null) {
            throw new \RuntimeException('Кандидат није пронађен.');
        }

        if (! $this->documentReviewService->kandidatHasCompleteRequiredDocuments($kandidat)) {
            throw new \RuntimeException('Упис није могућ док сва обавезна документа не буду одобрена.');
        }
    }
}
