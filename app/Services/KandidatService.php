<?php

namespace App\Services;

use App\DTOs\KandidatData;
use App\GodinaStudija;
use App\Jobs\MassEnrollmentJob;
use App\Kandidat;
use App\KandidatPrilozenaDokumenta;
use App\KrsnaSlava;
use App\Opstina;
use App\OpstiUspeh;
use App\PrijavaIspita;
use App\PrilozenaDokumenta;
use App\SkolskaGodUpisa;
use App\Sport;
use App\SportskoAngazovanje;
use App\StatusGodine;
use App\StatusStudiranja;
use App\StudijskiProgram;
use App\TipStudija;
use App\UpisGodine;
use App\UspehSrednjaSkola;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KandidatService
{
    public function __construct(protected UpisService $upisService) {}

    public function getAll(array $filters = [])
    {
        $query = Kandidat::query();

        if (! empty($filters['tipStudija_id'])) {
            $query->where('tipStudija_id', $filters['tipStudija_id']);
        }

        if (! empty($filters['statusUpisa_id'])) {
            $query->where('statusUpisa_id', $filters['statusUpisa_id']);
        }

        if (! empty($filters['studijskiProgram_id'])) {
            $query->where('studijskiProgram_id', $filters['studijskiProgram_id']);
        }

        return $query->get();
    }

    /**
     * Get kandidat by ID
     */
    public function findById(int $id): ?Kandidat
    {
        return Kandidat::find($id);
    }

    /**
     * Get active studijski program for osnovne studije
     */
    public function getActiveStudijskiProgramOsnovne(): ?int
    {
        return Cache::remember('active_studijski_program_osnovne', 3600, function () {
            return StudijskiProgram::where(['tipStudija_id' => 1, 'indikatorAktivan' => 1])->value('id');
        });
    }

    /**
     * Get studijski programi for tip studija
     */
    public function getStudijskiProgrami(int $tipStudijaId): mixed
    {
        return StudijskiProgram::where('tipStudija_id', $tipStudijaId)->get();
    }

    /**
     * Get all dropdown data for kandidat create form
     */
    public function getDropdownData(): array
    {
        return [
            'mestoRodjenja' => Opstina::all(),
            'krsnaSlava' => KrsnaSlava::all(),
            'mestoZavrseneSkoleFakulteta' => Opstina::all(),
            'opstiUspehSrednjaSkola' => OpstiUspeh::all(),
            'uspehSrednjaSkola' => UspehSrednjaSkola::all(),
            'sportskoAngazovanje' => SportskoAngazovanje::all(),
            'prilozeniDokumentPrvaGodina' => PrilozenaDokumenta::all(),
            'statusaUpisaKandidata' => StatusStudiranja::all(),
            'studijskiProgram' => StudijskiProgram::where('tipStudija_id', '1')->get(),
            'tipStudija' => TipStudija::all(),
            'godinaStudija' => GodinaStudija::all(),
            'skolskeGodineUpisa' => SkolskaGodUpisa::all(),
        ];
    }

    /**
     * Get dropdown data for master form
     */
    public function getDropdownDataMaster(): array
    {
        return [
            'mestoRodjenja' => Opstina::all(),
            'krsnaSlava' => KrsnaSlava::all(),
            'opstiUspehSrednjaSkola' => OpstiUspeh::all(),
            'uspehSrednjaSkola' => UspehSrednjaSkola::all(),
            'sportskoAngazovanje' => SportskoAngazovanje::all(),
            'prilozeniDokumentPrvaGodina' => PrilozenaDokumenta::all(),
            'statusaUpisaKandidata' => StatusStudiranja::all(),
            'studijskiProgram' => StudijskiProgram::where(['tipStudija_id' => 2, 'indikatorAktivan' => 1])->get(),
            'tipStudija' => TipStudija::all(),
            'godinaStudija' => GodinaStudija::all(),
            'skolskeGodineUpisa' => SkolskaGodUpisa::all(),
            'dokumentaMaster' => PrilozenaDokumenta::where('skolskaGodina_id', '3')->get(),
        ];
    }

    /**
     * Handle image upload for kandidat. Saves image and updates kandidat.slika.
     */
    public function handleImageUpload(Kandidat $kandidat, $file): void
    {
        if ($file->isValid() && substr($file->getMimeType(), 0, 5) === 'image') {
            $extension = $file->getClientOriginalExtension();
            $imageName = 'slika'.$kandidat->id;

            $oldImages = collect(Storage::disk('uploads')->files('images'))
                ->filter(fn ($f) => str_starts_with(basename($f), $imageName.'.'));

            foreach ($oldImages as $old) {
                Storage::disk('uploads')->delete($old);
            }

            $kandidat->slika = $imageName.'.'.$extension;
            $kandidat->save();

            Storage::disk('uploads')->putFileAs('images', $file, $imageName.'.'.$extension);
        }
    }

    /**
     * Handle image upload for new kandidat (no existing file to delete).
     */
    public function handleNewImageUpload(Kandidat $kandidat, $file): void
    {
        if ($file->isValid() && substr($file->getMimeType(), 0, 5) === 'image') {
            $imageName = 'slika'.$kandidat->id.'.'.$file->getClientOriginalExtension();
            $kandidat->slika = $imageName;
            $kandidat->save();

            Storage::disk('uploads')->putFileAs('images', $file, $imageName);
        }
    }

    /**
     * Handle PDF upload for kandidat.
     */
    public function handlePdfUpload(Kandidat $kandidat, $file): void
    {
        if ($file->isValid() && $file->getMimeType() === 'application/pdf') {
            $extension = $file->getClientOriginalExtension();
            $pdfName = 'diplomski'.$kandidat->id;

            $oldPdfs = collect(Storage::disk('uploads')->files('pdf'))
                ->filter(fn ($f) => str_starts_with(basename($f), $pdfName.'.'));

            foreach ($oldPdfs as $old) {
                Storage::disk('uploads')->delete($old);
            }

            $kandidat->diplomski = $pdfName.$extension;
            $kandidat->save();

            Storage::disk('uploads')->putFileAs('pdf', $file, $pdfName.$extension);
        }
    }

    /**
     * Delete kandidat image from storage.
     */
    public function deleteKandidatImage(Kandidat $kandidat): void
    {
        if (! empty($kandidat->slika) && Storage::disk('uploads')->exists("images/{$kandidat->slika}")) {
            Storage::disk('uploads')->delete("images/{$kandidat->slika}");
        }
    }

    public function storeKandidatPage1(KandidatData $data, Request $request): Kandidat
    {
        $kandidat = new Kandidat;
        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        } else {
            $kandidat->uplata = 0;
        }

        $kandidat->statusUpisa_id = 3;
        $kandidat->datumStatusa = Carbon::now();

        if (date_create_from_format('d.m.Y.', $request->DatumRodjenja)) {
            $kandidat->datumRodjenja = date_create_from_format('d.m.Y.', $request->DatumRodjenja);
        }

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->krsnaSlava_id = $request->KrsnaSlava;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;
        $kandidat->imePrezimeJednogRoditelja = $request->ImePrezimeJednogRoditelja;
        $kandidat->kontaktTelefonRoditelja = $request->KontaktTelefonRoditelja;
        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = 1;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $kandidat->godinaStudija_id = $data->godinaStudijaId;

        $kandidat->save();

        if ($request->hasFile('imageUpload')) {
            $this->handleNewImageUpload($kandidat, $request->file('imageUpload'));
        }

        return $kandidat;
    }

    /**
     * Store kandidat page 2 (grades, sports, documents, scores).
     */
    public function storeKandidatPage2(Request $request): Kandidat
    {
        $kandidat = Kandidat::find($request->insertedId);

        $prviRazred = new UspehSrednjaSkola;
        $prviRazred->kandidat_id = $request->insertedId;
        $prviRazred->opstiUspeh_id = $request->prviRazred;
        $prviRazred->srednja_ocena = $request->SrednjaOcena1;
        $prviRazred->RedniBrojRazreda = 1;
        $prviRazred->save();

        $drugiRazred = new UspehSrednjaSkola;
        $drugiRazred->kandidat_id = $request->insertedId;
        $drugiRazred->opstiUspeh_id = $request->drugiRazred;
        $drugiRazred->srednja_ocena = $request->SrednjaOcena2;
        $drugiRazred->RedniBrojRazreda = 2;
        $drugiRazred->save();

        $treciRazred = new UspehSrednjaSkola;
        $treciRazred->kandidat_id = $request->insertedId;
        $treciRazred->opstiUspeh_id = $request->treciRazred;
        $treciRazred->srednja_ocena = $request->SrednjaOcena3;
        $treciRazred->RedniBrojRazreda = 3;
        $treciRazred->save();

        $cetvrtiRazred = new UspehSrednjaSkola;
        $cetvrtiRazred->kandidat_id = $request->insertedId;
        $cetvrtiRazred->opstiUspeh_id = $request->cetvrtiRazred;
        $cetvrtiRazred->srednja_ocena = $request->SrednjaOcena4;
        $cetvrtiRazred->RedniBrojRazreda = 4;
        $cetvrtiRazred->save();

        $kandidat->opstiUspehSrednjaSkola_id = $request->OpstiUspehSrednjaSkola;
        $kandidat->srednjaOcenaSrednjaSkola = $request->SrednjaOcenaSrednjaSkola;

        if ($request->sport1 != 0) {
            $sport1 = new SportskoAngazovanje;
            $sport1->sport_id = $request->sport1;
            $sport1->kandidat_id = $request->insertedId;
            $sport1->nazivKluba = $request->klub1;
            $sport1->odDoGodina = $request->uzrast1;
            $sport1->ukupnoGodina = $request->godine1;
            $sport1->save();
        }

        if ($request->sport2 != 0) {
            $sport2 = new SportskoAngazovanje;
            $sport2->sport_id = $request->sport2;
            $sport2->kandidat_id = $request->insertedId;
            $sport2->nazivKluba = $request->klub2;
            $sport2->odDoGodina = $request->uzrast2;
            $sport2->ukupnoGodina = $request->godine2;
            $sport2->save();
        }

        if ($request->sport3 != 0) {
            $sport3 = new SportskoAngazovanje;
            $sport3->sport_id = $request->sport3;
            $sport3->kandidat_id = $request->insertedId;
            $sport3->nazivKluba = $request->klub3;
            $sport3->odDoGodina = $request->uzrast3;
            $sport3->ukupnoGodina = $request->godine3;
            $sport3->save();
        }

        $kandidat->visina = str_replace(',', '.', $request->VisinaKandidata);
        $kandidat->telesnaTezina = str_replace(',', '.', $request->TelesnaTezinaKandidata);

        if ($request->has('dokumentiPrva')) {
            foreach ($request->dokumentiPrva as $dokument) {
                $prilozenDokument = new KandidatPrilozenaDokumenta;
                $prilozenDokument->prilozenaDokumenta_id = $dokument;
                $prilozenDokument->kandidat_id = $request->insertedId;
                $prilozenDokument->indikatorAktivan = 1;
                $prilozenDokument->save();
            }
        }

        if ($request->has('dokumentiDruga')) {
            foreach ($request->dokumentiDruga as $dokument) {
                $prilozenDokument = new KandidatPrilozenaDokumenta;
                $prilozenDokument->prilozenaDokumenta_id = $dokument;
                $prilozenDokument->kandidat_id = $request->insertedId;
                $prilozenDokument->indikatorAktivan = 1;
                $prilozenDokument->save();
            }
        }

        $kandidat->brojBodovaTest = $request->BrojBodovaTest;
        $kandidat->brojBodovaSkola = $request->BrojBodovaSkola;
        $kandidat->ukupniBrojBodova = $request->ukupniBrojBodova;
        $kandidat->upisniRok = $request->UpisniRok;

        $kandidat->save();

        return $kandidat;
    }

    public function updateKandidat(int $id, KandidatData $data, Request $request): Kandidat
    {
        $kandidat = Kandidat::find($id);

        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        }

        if ($request->hasFile('imageUpload')) {
            $this->handleImageUpload($kandidat, $request->file('imageUpload'));
        }

        if ($request->hasFile('pdfUpload')) {
            $this->handlePdfUpload($kandidat, $request->file('pdfUpload'));
        }

        $kandidat->datumRodjenja = date_create_from_format('d.m.Y.', $request->DatumRodjenja);

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->krsnaSlava_id = $request->KrsnaSlava;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;
        $kandidat->imePrezimeJednogRoditelja = $request->ImePrezimeJednogRoditelja;
        $kandidat->kontaktTelefonRoditelja = $request->KontaktTelefonRoditelja;

        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = $data->tipStudijaId;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;
        $kandidat->godinaStudija_id = $data->godinaStudijaId;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $kandidat->statusUpisa_id = $request->statusUpisa_id;
        $kandidat->datumStatusa = empty($request->datumStatusa) ?
            Carbon::now() :
            date_create_from_format('d.m.Y.', $request->datumStatusa);

        try {
            $prviRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 1])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $prviRazred = new UspehSrednjaSkola;
        } finally {
            $prviRazred->kandidat_id = $id;
            $prviRazred->opstiUspeh_id = $request->prviRazred;
            $prviRazred->srednja_ocena = $request->SrednjaOcena1;
            $prviRazred->RedniBrojRazreda = 1;
            $prviRazred->save();
        }

        try {
            $drugiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 2])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $drugiRazred = new UspehSrednjaSkola;
        } finally {
            $drugiRazred->kandidat_id = $id;
            $drugiRazred->opstiUspeh_id = $request->drugiRazred;
            $drugiRazred->srednja_ocena = $request->SrednjaOcena2;
            $drugiRazred->RedniBrojRazreda = 2;
            $drugiRazred->save();
        }

        try {
            $treciRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 3])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $treciRazred = new UspehSrednjaSkola;
        } finally {
            $treciRazred->kandidat_id = $id;
            $treciRazred->opstiUspeh_id = $request->treciRazred;
            $treciRazred->srednja_ocena = $request->SrednjaOcena3;
            $treciRazred->RedniBrojRazreda = 3;
            $treciRazred->save();
        }

        try {
            $cetvrtiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 4])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $cetvrtiRazred = new UspehSrednjaSkola;
        } finally {
            $cetvrtiRazred->kandidat_id = $id;
            $cetvrtiRazred->opstiUspeh_id = $request->cetvrtiRazred;
            $cetvrtiRazred->srednja_ocena = $request->SrednjaOcena4;
            $cetvrtiRazred->RedniBrojRazreda = 4;
            $cetvrtiRazred->save();
        }

        $kandidat->opstiUspehSrednjaSkola_id = $request->OpstiUspehSrednjaSkola;
        $kandidat->srednjaOcenaSrednjaSkola = $request->SrednjaOcenaSrednjaSkola;

        $kandidat->visina = str_replace(',', '.', $request->VisinaKandidata);
        $kandidat->telesnaTezina = str_replace(',', '.', $request->TelesnaTezinaKandidata);

        KandidatPrilozenaDokumenta::where('kandidat_id', $id)->delete();

        if ($request->has('dokumentiPrva')) {
            foreach ($request->dokumentiPrva as $dokument) {
                $prilozenDokument = new KandidatPrilozenaDokumenta;
                $prilozenDokument->prilozenaDokumenta_id = $dokument;
                $prilozenDokument->kandidat_id = $id;
                $prilozenDokument->indikatorAktivan = 1;
                $prilozenDokument->save();
            }
        }

        if ($request->has('dokumentiDruga')) {
            foreach ($request->dokumentiDruga as $dokument) {
                $prilozenDokument = new KandidatPrilozenaDokumenta;
                $prilozenDokument->prilozenaDokumenta_id = $dokument;
                $prilozenDokument->kandidat_id = $id;
                $prilozenDokument->indikatorAktivan = 1;
                $prilozenDokument->save();
            }
        }

        $kandidat->brojBodovaTest = $request->BrojBodovaTest;
        $kandidat->brojBodovaSkola = $request->BrojBodovaSkola;
        $kandidat->ukupniBrojBodova = $request->ukupniBrojBodova;
        $kandidat->upisniRok = $request->UpisniRok;
        $kandidat->indikatorAktivan = $request->IndikatorAktivan;
        $kandidat->brojIndeksa = $data->brojIndeksa;

        $kandidat->save();

        return $kandidat;
    }

    /**
     * Store master kandidat.
     */
    public function storeMasterKandidat(Request $request): Kandidat
    {
        $kandidat = new Kandidat;
        $kandidat->imeKandidata = $request->ImeKandidata;
        $kandidat->prezimeKandidata = $request->PrezimeKandidata;
        $kandidat->jmbg = $request->JMBG;

        $kandidat->statusUpisa_id = 3;
        $kandidat->datumStatusa = Carbon::now();

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        } else {
            $kandidat->uplata = 0;
        }

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;

        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = 2;
        $kandidat->studijskiProgram_id = $request->StudijskiProgram;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;

        $kandidat->prosecnaOcena = str_replace(',', '.', $request->ProsecnaOcena);
        $kandidat->upisniRok = $request->UpisniRok;
        $kandidat->godinaStudija_id = 1;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $saved = $kandidat->save();

        if ($request->hasFile('imageUpload')) {
            $this->handleNewImageUpload($kandidat, $request->file('imageUpload'));
        }

        $insertedId = $kandidat->id;

        if ($saved) {
            $this->upisService->registrujKandidata($insertedId);

            KandidatPrilozenaDokumenta::where('kandidat_id', $insertedId)->delete();

            if ($request->has('dokumentaMaster')) {
                foreach ($request->dokumentaMaster as $dokument) {
                    $prilozenDokument = new KandidatPrilozenaDokumenta;
                    $prilozenDokument->prilozenaDokumenta_id = $dokument;
                    $prilozenDokument->kandidat_id = $insertedId;
                    $prilozenDokument->indikatorAktivan = 1;
                    $prilozenDokument->save();
                }
            }
        }

        return $kandidat;
    }

    /**
     * Update master kandidat.
     */
    public function updateMasterKandidat(int $id, Request $request): Kandidat
    {
        $kandidat = Kandidat::find($id);

        $kandidat->imeKandidata = $request->ImeKandidata;
        $kandidat->prezimeKandidata = $request->PrezimeKandidata;
        $kandidat->jmbg = $request->JMBG;

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        }

        $kandidat->statusUpisa_id = $request->statusUpisa_id;
        $kandidat->datumStatusa = empty($request->datumStatusa) ?
            Carbon::now() :
            date_create_from_format('d.m.Y.', $request->datumStatusa);

        if ($request->hasFile('imageUpload')) {
            $this->handleImageUpload($kandidat, $request->file('imageUpload'));
        }

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;

        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = $request->TipStudija;
        $kandidat->studijskiProgram_id = $request->StudijskiProgram;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;

        $kandidat->prosecnaOcena = str_replace(',', '.', $request->ProsecnaOcena);
        $kandidat->upisniRok = $request->UpisniRok;

        $kandidat->brojIndeksa = $request->brojIndeksa;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $saved = $kandidat->save();

        KandidatPrilozenaDokumenta::where('kandidat_id', $id)->delete();

        if ($request->has('dokumentaMaster')) {
            foreach ($request->dokumentaMaster as $dokument) {
                $prilozenDokument = new KandidatPrilozenaDokumenta;
                $prilozenDokument->prilozenaDokumenta_id = $dokument;
                $prilozenDokument->kandidat_id = $id;
                $prilozenDokument->indikatorAktivan = 1;
                $prilozenDokument->save();
            }
        }

        return $kandidat;
    }

    /**
     * Delete kandidat with all related records (osnovne studije).
     */
    public function deleteKandidat(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $kandidat = Kandidat::find($id);
            KandidatPrilozenaDokumenta::where(['kandidat_id' => $id])->delete();
            UpisGodine::where(['kandidat_id' => $id])->delete();
            SportskoAngazovanje::where(['kandidat_id' => $id])->delete();
            PrijavaIspita::where(['kandidat_id' => $id])->delete();

            $this->deleteKandidatImage($kandidat);

            return (bool) Kandidat::destroy($id);
        });
    }

    /**
     * Delete master kandidat (simple delete).
     */
    public function deleteMasterKandidat(int $id): bool
    {
        return (bool) Kandidat::destroy($id);
    }

    /**
     * Store sport for kandidat.
     */
    public function storeSport(int $kandidatId, array $data): SportskoAngazovanje
    {
        $sport = new SportskoAngazovanje;
        $sport->sport_id = $data['sport'];
        $sport->kandidat_id = $kandidatId;
        $sport->nazivKluba = $data['klub'];
        $sport->odDoGodina = $data['uzrast'];
        $sport->ukupnoGodina = $data['godine'];
        $sport->save();

        return $sport;
    }

    /**
     * Masovna uplata za osnovne studije.
     */
    public function masovnaUplata(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->uplata = 1;
            $kandidat->save();

            UpisGodine::uplatiGodinu($kandidatId, 1);
        }
    }

    /**
     * Masovni upis za osnovne studije.
     */
    public function masovniUpis(array $kandidatIds): bool
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $this->upisService->registrujKandidata($kandidatId);

            $returnValue = $this->upisService->upisiGodinu($kandidatId, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);

            if ($returnValue) {
                $kandidat->statusUpisa_id = 1;
                $kandidat->datumStatusa = Carbon::now();
                $kandidat->save();
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Masovna uplata za master studije.
     */
    public function masovnaUplataMaster(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->uplata = 1;
            $kandidat->save();
        }
    }

    /**
     * Masovni upis za master studije.
     */
    public function masovniUpisMaster(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->statusUpisa_id = 1;
            $kandidat->datumStatusa = Carbon::now();
            $kandidat->save();

            $this->upisService->generisiBrojIndeksa($kandidatId);
        }
    }

    public function masovniUpisAsync(array $kandidatIds): array
    {
        MassEnrollmentJob::dispatch($kandidatIds);

        return ['status' => 'queued', 'count' => count($kandidatIds)];
    }

    /**
     * Upis kandidata (enrollment logic).
     */
    public function upisKandidata(int $id): array
    {
        $kandidat = Kandidat::find($id);
        $this->upisService->registrujKandidata($id);

        if ($kandidat->tipStudija_id == 1) {
            $check = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $check) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
        } elseif ($kandidat->tipStudija_id == 2) {
            $checkTwo = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $checkTwo) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
            $this->upisService->generisiBrojIndeksa($kandidat->id);
        } elseif ($kandidat->tipStudija_id == 3) {
            $checkTwo = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $checkTwo) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
            $this->upisService->generisiBrojIndeksa($kandidat->id);
        }

        $kandidat->statusUpisa_id = 1;
        $kandidat->datumStatusa = Carbon::now();
        $saved = $kandidat->save();

        return [
            'success' => $saved,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ];
    }

    /**
     * Registracija kandidata.
     */
    public function registracijaKandidata(int $id): void
    {
        $this->upisService->registrujKandidata($id);
    }

    /**
     * Get dropdown data for edit view (osnovne).
     */
    public function getEditDropdownData(int $id): array
    {
        $sport = Sport::all();
        $dokumentiPrvaGodina = PrilozenaDokumenta::where('skolskaGodina_id', '1')->get();
        $dokumentiOstaleGodine = PrilozenaDokumenta::where('skolskaGodina_id', '2')->get();
        $statusKandidata = StatusGodine::whereNotIn('id', [4, 5])->get();
        $studijskiProgram = StudijskiProgram::where(['tipStudija_id' => 1, 'indikatorAktivan' => 1])->get();

        $prilozenaDokumenta = KandidatPrilozenaDokumenta::where('kandidat_id', $id)->pluck('prilozenaDokumenta_id')->toArray();

        try {
            $prviRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 1])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $prviRazred = new UspehSrednjaSkola;
            $prviRazred->kandidat_id = 0;
            $prviRazred->opstiUspeh_id = 1;
            $prviRazred->srednja_ocena = 0;
            $prviRazred->RedniBrojRazreda = 1;
        }

        try {
            $drugiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 2])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $drugiRazred = new UspehSrednjaSkola;
            $drugiRazred->kandidat_id = 0;
            $drugiRazred->opstiUspeh_id = 1;
            $drugiRazred->srednja_ocena = 0;
            $drugiRazred->RedniBrojRazreda = 1;
        }

        try {
            $treciRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 3])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $treciRazred = new UspehSrednjaSkola;
            $treciRazred->kandidat_id = 0;
            $treciRazred->opstiUspeh_id = 1;
            $treciRazred->srednja_ocena = 0;
            $treciRazred->RedniBrojRazreda = 1;
        }

        try {
            $cetvrtiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 4])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $cetvrtiRazred = new UspehSrednjaSkola;
            $cetvrtiRazred->kandidat_id = 0;
            $cetvrtiRazred->opstiUspeh_id = 1;
            $cetvrtiRazred->srednja_ocena = 0;
            $cetvrtiRazred->RedniBrojRazreda = 1;
        }

        $sportskoAngazovanjeKandidata = SportskoAngazovanje::where('kandidat_id', $id)->get();

        return array_merge($this->getDropdownData(), [
            'sport' => $sport,
            'dokumentiPrvaGodina' => $dokumentiPrvaGodina,
            'dokumentiOstaleGodine' => $dokumentiOstaleGodine,
            'statusKandidata' => $statusKandidata,
            'studijskiProgram' => $studijskiProgram,
            'prilozenaDokumenta' => $prilozenaDokumenta,
            'prviRazred' => $prviRazred,
            'drugiRazred' => $drugiRazred,
            'treciRazred' => $treciRazred,
            'cetvrtiRazred' => $cetvrtiRazred,
            'sportskoAngazovanjeKandidata' => $sportskoAngazovanjeKandidata,
        ]);
    }

    /**
     * Get dropdown data for edit master view.
     */
    public function getEditDropdownDataMaster(int $id): array
    {
        $statusKandidata = StatusGodine::whereNotIn('id', [4, 5])->get();
        $prilozenaDokumenta = KandidatPrilozenaDokumenta::where('kandidat_id', $id)->pluck('prilozenaDokumenta_id')->toArray();

        return array_merge($this->getDropdownDataMaster(), [
            'statusKandidata' => $statusKandidata,
            'prilozenaDokumenta' => $prilozenaDokumenta,
        ]);
    }

    public function storeKandidat(KandidatData $data): Kandidat
    {
        return DB::transaction(function () use ($data) {
            return Kandidat::create($data->toArray());
        });
    }

    /**
     * Get kandidat by ID (alias)
     */
    public function create(array $data): Kandidat
    {
        return DB::transaction(function () use ($data) {
            $kandidat = Kandidat::create($data);

            return $kandidat;
        });
    }

    /**
     * Update kandidat by array data (basic, legacy method)
     */
    public function update(int $id, array $data): ?Kandidat
    {
        $kandidat = $this->findById($id);

        if (! $kandidat) {
            return null;
        }

        $kandidat->update($data);

        return $kandidat;
    }

    /**
     * Delete kandidat by ID (basic, legacy method)
     */
    public function delete(int $id): bool
    {
        $kandidat = $this->findById($id);

        if (! $kandidat) {
            return false;
        }

        return $kandidat->delete();
    }

    /**
     * Get kandidati by status
     */
    public function getByStatus(int $statusId): mixed
    {
        return Kandidat::where('statusUpisa_id', $statusId)->get();
    }

    /**
     * Get kandidati by studijski program
     */
    public function getByStudijskiProgram(int $programId): mixed
    {
        return Kandidat::where('studijskiProgram_id', $programId)->get();
    }

    /**
     * Search kandidati
     */
    public function search(string $query): mixed
    {
        return Kandidat::where('imeKandidata', 'like', "%{$query}%")
            ->orWhere('prezimeKandidata', 'like', "%{$query}%")
            ->orWhere('brojIndeksa', 'like', "%{$query}%")
            ->get();
    }
}
