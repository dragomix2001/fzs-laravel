<?php

namespace App\Services;

use App\ArhivIndeksa;
use App\Kandidat;
use App\Models\TipStudija;
use App\SkolskaGodUpisa;
use App\UpisGodine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class UpisService
{
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
        $kandidat = Kandidat::find($kandidatId)->replicate();
        if (! empty($kandidat)) {
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

        return false;
    }

    public function upisiGodinu(int $id, int $godina, int $skolskaGodinaUpisaId): bool
    {
        $kandidat = Kandidat::find($id);
        $upis = UpisGodine::where([
            'kandidat_id' => $id,
            'godina' => $godina,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $upis->statusGodine_id = 1;
        $upis->skolskaGodina_id = $skolskaGodinaUpisaId;
        $upis->datumUpisa = Carbon::now();
        $saved1 = $upis->save();

        if ($saved1) {
            $kandidat->godinaStudija_id = $godina;
            $saved2 = $kandidat->save();
        } else {
            return false;
        }

        if (empty($kandidat->brojIndeksa)) {
            $this->generisiBrojIndeksa($kandidat->id);
        }

        if ($saved1 && $saved2) {
            return true;
        } else {
            return false;
        }
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

        switch (strlen($noviBrojIndeksa)) {
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
}
