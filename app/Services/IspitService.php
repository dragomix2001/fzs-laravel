<?php

namespace App\Services;

use App\AktivniIspitniRokovi;
use App\GodinaStudija;
use App\Kandidat;
use App\PolozeniIspiti;
use App\Predmet;
use App\PredmetProgram;
use App\PrijavaIspita;
use App\Profesor;
use App\StatusIspita;
use App\ZapisnikOPolaganju_Student;
use App\ZapisnikOPolaganju_StudijskiProgram;
use App\ZapisnikOPolaganjuIspita;
use App\Models\StudijskiProgram;
use App\DTOs\ZapisnikData;
use App\Jobs\GenerateZapisnikPdfJob;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use View;

class IspitService extends BasePdfService
{
    // -------------------------------------------------------------------------
    // Index / listing
    // -------------------------------------------------------------------------

    public function getZapisniciForIndex(array $filters): array
    {
        $query = ZapisnikOPolaganjuIspita::where(['arhiviran' => false]);

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

    // -------------------------------------------------------------------------
    // Create zapisnik — reference data
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // AJAX helpers
    // -------------------------------------------------------------------------

    public function getZapisnikPredmetData(int $rokId): array
    {
        $prijava = PrijavaIspita::where([
            'rok_id' => $rokId,
        ])->select('predmet_id', 'profesor_id')->get();

        $predmetId = array_unique($prijava->pluck('predmet_id')->all());
        $profesorId = array_unique($prijava->pluck('profesor_id')->all());

        $profesori = Profesor::whereIn('id', $profesorId)->get()->isEmpty()
            ? Profesor::all()
            : Profesor::whereIn('id', $profesorId)->get();

        $predmeti = Predmet::whereIn('id', $predmetId)->get();

        return ['predmeti' => $predmeti, 'profesori' => $profesori];
    }

    public function getZapisnikStudenti(int $predmetId, int $rokId, int $profesorId): array
    {
        $program = PredmetProgram::where([
            'predmet_id' => $predmetId,
        ])->pluck('id');

        $prijava = PrijavaIspita::where([
            'rok_id' => $rokId,
            'profesor_id' => $profesorId,
        ])->whereIn('predmet_id', $program)->get();

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

    // -------------------------------------------------------------------------
    // Store zapisnik
    // -------------------------------------------------------------------------

    public function createZapisnik(array $data, array $odabir): ZapisnikOPolaganjuIspita
    {
        $zapisnik = new ZapisnikOPolaganjuIspita($data);
        $zapisnik->save();

        $smerovi = [];

        $kandidatiMap = Kandidat::whereIn('id', $odabir)->get()->keyBy('id');

        $studijskiProgramIds = $kandidatiMap->pluck('studijskiProgram_id')->unique()->all();
        $predmetProgramMap = PredmetProgram::where('predmet_id', $data['predmet_id'])
            ->whereIn('studijskiProgram_id', $studijskiProgramIds)
            ->get()
            ->keyBy('studijskiProgram_id');

        foreach ($odabir as $id) {
            $zapisStudent = new ZapisnikOPolaganju_Student;
            $zapisStudent->zapisnik_id = $zapisnik->id;
            $zapisStudent->prijavaIspita_id = $zapisnik->prijavaIspita_id;
            $zapisStudent->kandidat_id = $id;
            $zapisStudent->save();

            $kandidat = $kandidatiMap->get($id);
            $smerovi[] = $kandidat->studijskiProgram_id;

            $programId = $predmetProgramMap->get($kandidat->studijskiProgram_id)->id;

            $polozenIspit = new PolozeniIspiti;
            $polozenIspit->indikatorAktivan = 0;
            $polozenIspit->kandidat_id = $id;
            $polozenIspit->predmet_id = $programId;
            $polozenIspit->zapisnik_id = $zapisnik->id;
            $polozenIspit->prijava_id = $zapisnik->prijavaIspita_id;
            $polozenIspit->save();
        }

        $smerovi = array_unique($smerovi);
        foreach ($smerovi as $id) {
            $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
            $zapisSmer->zapisnik_id = $zapisnik->id;
            $zapisSmer->StudijskiProgram_id = $id;
            $zapisSmer->save();
        }

        return $zapisnik;
    }

    public function storeZapisnik(ZapisnikData $data): ZapisnikOPolaganjuIspita
    {
        return $this->createZapisnik($data->toArray(), $data->studentiIds);
    }

    // -------------------------------------------------------------------------
    // Pregled zapisnika
    // -------------------------------------------------------------------------

    public function getZapisnikPregled(int $zapisnikId): array
    {
        $zapisnik = ZapisnikOPolaganjuIspita::find($zapisnikId);
        $zapisnikStudent = ZapisnikOPolaganju_Student::where(['zapisnik_id' => $zapisnikId])->pluck('kandidat_id')->all();
        $studenti = Kandidat::whereIn('id', $zapisnikStudent)->get();
        $studentiMap = $studenti->keyBy('id');

        // Pre-fetch all relevant PredmetProgram records for these kandidati in one query
        $tipStudijaIds = $studentiMap->pluck('tipStudija_id')->unique()->all();
        $studijskiProgramIdsForMap = $studentiMap->pluck('studijskiProgram_id')->unique()->all();
        $predmetProgramLookup = PredmetProgram::where('predmet_id', $zapisnik->predmet_id)
            ->whereIn('tipStudija_id', $tipStudijaIds)
            ->whereIn('studijskiProgram_id', $studijskiProgramIdsForMap)
            ->get()
            ->keyBy(function ($item) {
                return $item->tipStudija_id . '_' . $item->studijskiProgram_id;
            });

        $prijavaIds = [];
        foreach ($zapisnikStudent as $id) {
            $kandidat = $studentiMap->get($id);

            $predmetProgram = $predmetProgramLookup->get($kandidat->tipStudija_id . '_' . $kandidat->studijskiProgram_id);

            $pom = PrijavaIspita::where(['predmet_id' => $predmetProgram->id, 'rok_id' => $zapisnik->rok_id, 'kandidat_id' => $id])->first();
            if ($pom != null) {
                $prijavaIds[$id] = $pom->id;
            }
        }

        $polozeniIspitIds = [];
        foreach ($zapisnikStudent as $id) {
            $kandidat = $studentiMap->get($id);
            $predmetProgram = $predmetProgramLookup->get($kandidat->tipStudija_id . '_' . $kandidat->studijskiProgram_id);
            $pom = PolozeniIspiti::where(['zapisnik_id' => $zapisnik->id, 'predmet_id' => $predmetProgram->id, 'kandidat_id' => $id])->first();
            if ($pom != null) {
                $polozeniIspitIds[$id] = $pom->id;
            }
        }

        // Use last kandidat from the loop to determine predmetProgram for the final query below
        $lastKandidat = $studentiMap->last();
        $predmetProgram = $predmetProgramLookup->get($lastKandidat->tipStudija_id . '_' . $lastKandidat->studijskiProgram_id);
        $studijskiProgrami = ZapisnikOPolaganju_StudijskiProgram::where(['zapisnik_id' => $zapisnikId])->get();
        $statusIspita = StatusIspita::all();
        $polozeniIspiti = PolozeniIspiti::where(['zapisnik_id' => $zapisnikId])->get();

        $polozeniIspiti = $polozeniIspiti->sortBy(function ($name, $key) use ($studentiMap) {
            return $studentiMap->get($name['kandidat_id'])->brojIndeksa;
        });

        $kandidati = Kandidat::where([
            'tipStudija_id' => $predmetProgram->tipStudija_id,
            'studijskiProgram_id' => $predmetProgram->studijskiProgram_id,
        ])->get();

        return compact('zapisnik', 'studenti', 'studijskiProgrami', 'statusIspita', 'polozeniIspiti', 'polozeniIspitIds', 'prijavaIds', 'kandidati');
    }

    // -------------------------------------------------------------------------
    // Save exam results
    // -------------------------------------------------------------------------

    public function savePolozeniIspiti(array $ispitIds, array $ocenePismeni, array $oceneUsmeni, array $konacneOcene, array $brojBodova, array $statusIspita): int
    {
        $zapisnikId = 0;
        foreach ($ispitIds as $index => $ispit) {
            $polozeniIspit = PolozeniIspiti::find($ispit);
            $polozeniIspit->ocenaPismeni = $ocenePismeni[$index] ?? null;
            $polozeniIspit->ocenaUsmeni = $oceneUsmeni[$index] ?? null;
            $polozeniIspit->konacnaOcena = $konacneOcene[$index] ?? null;
            $polozeniIspit->brojBodova = $brojBodova[$index] ?? null;
            $polozeniIspit->statusIspita = $statusIspita[$index] ?? null;
            $polozeniIspit->indikatorAktivan = 1;
            $polozeniIspit->save();

            $zapisnikId = $polozeniIspit->zapisnik_id;
        }

        return $zapisnikId;
    }

    // -------------------------------------------------------------------------
    // Add student to zapisnik
    // -------------------------------------------------------------------------

    public function addStudentToZapisnik(int $zapisnikId, array $odabir): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::find($zapisnikId);

        $prijavljeniStudenti = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
        ])->pluck('kandidat_id')->all();

        $prijavljeniSmerovi = ZapisnikOPolaganju_StudijskiProgram::where([
            'zapisnik_id' => $zapisnikId,
        ])->pluck('studijskiProgram_id')->all();

        $smerovi = [];

        $kandidatiMap = Kandidat::whereIn('id', $odabir)->get()->keyBy('id');

        $studijskiProgramIdsForDodaj = $kandidatiMap->pluck('studijskiProgram_id')->unique()->all();
        $predmetProgramMapDodaj = PredmetProgram::where('predmet_id', $zapisnik->predmet_id)
            ->whereIn('studijskiProgram_id', $studijskiProgramIdsForDodaj)
            ->get()
            ->groupBy('studijskiProgram_id');

        foreach ($odabir as $id) {
            if (in_array($id, $prijavljeniStudenti)) {
                // ako student vec postoji u zapisniku, preskacemo ga
                continue;
            }
            $kandidat = $kandidatiMap->get($id);
            $predmetProgram = $predmetProgramMapDodaj->get($kandidat->studijskiProgram_id);

            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem = new PrijavaIspita;
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->kandidat_id = $id;
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->predmet_id = $predmetProgram->first()->id;
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->profesor_id = $zapisnik->profesor_id;
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->rok_id = $zapisnik->rok_id;
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->brojPolaganja = 1;
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->datum = Carbon::now();
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->tipPrijave_id = 0;
            $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->save();

            $zapisStudent = new ZapisnikOPolaganju_Student;
            $zapisStudent->zapisnik_id = $zapisnik->id;
            $zapisStudent->prijavaIspita_id = $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->id;
            $zapisStudent->kandidat_id = $id;
            $zapisStudent->save();

            if (! in_array($kandidat->studijskiProgram_id, $prijavljeniSmerovi)) {
                $smerovi[] = $kandidat->studijskiProgram_id;
            }

            $polozenIspit = new PolozeniIspiti;
            $polozenIspit->indikatorAktivan = 0;
            $polozenIspit->kandidat_id = $id;
            $polozenIspit->predmet_id = $zapisnik->predmet_id;
            $polozenIspit->zapisnik_id = $zapisnik->id;
            $polozenIspit->prijava_id = $novaPrijavaZaDodatogStudentaNaZapisnikPrekoRedaMamuVamJebem->id;
            $polozenIspit->save();
        }

        $smerovi = array_unique($smerovi);
        foreach ($smerovi as $id) {
            $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
            $zapisSmer->zapisnik_id = $zapisnik->id;
            $zapisSmer->StudijskiProgram_id = $id;
            $zapisSmer->save();
        }
    }

    // -------------------------------------------------------------------------
    // Delete student from zapisnik
    // -------------------------------------------------------------------------

    /**
     * Returns true if the zapisnik was also deleted (no students left).
     */
    public function removeStudentFromZapisnik(int $zapisnikId, int $kandidatId): bool
    {
        ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
            'kandidat_id' => $kandidatId,
        ])->delete();

        PolozeniIspiti::where([
            'zapisnik_id' => $zapisnikId,
            'kandidat_id' => $kandidatId,
        ])->delete();

        $zapisnikProvera = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
        ])->get();

        if ($zapisnikProvera->count() == 0) {
            ZapisnikOPolaganjuIspita::destroy($zapisnikId);

            return true;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Delete zapisnik
    // -------------------------------------------------------------------------

    public function deleteZapisnikWithChildren(int $id): void
    {
        ZapisnikOPolaganju_Student::where(['zapisnik_id' => $id])->delete();
        ZapisnikOPolaganju_StudijskiProgram::where(['zapisnik_id' => $id])->delete();
        ZapisnikOPolaganjuIspita::destroy($id);
    }

    public function deleteZapisnik(int $id): void
    {
        $this->deleteZapisnikWithChildren($id);
    }

    // -------------------------------------------------------------------------
    // Delete polozeni ispit
    // -------------------------------------------------------------------------

    public function deletePolozeniIspit(int $id, int $brisiZapisnik): void
    {
        $ispit = PolozeniIspiti::find($id);

        if ($brisiZapisnik == 1) {
            ZapisnikOPolaganju_Student::where([
                'zapisnik_id' => $ispit->zapisnik_id,
                'kandidat_id' => $ispit->kandidat_id,
            ])->delete();

            PolozeniIspiti::destroy($id);

            $zapisnikProvera = ZapisnikOPolaganju_Student::where([
                'zapisnik_id' => $ispit->zapisnik_id,
            ])->get();

            if ($zapisnikProvera->count() == 0) {
                ZapisnikOPolaganjuIspita::destroy($ispit->zapisnik_id);
            }
        } else {
            $ispit->indikatorAktivan = 0;
            $ispit->ocenaPismeni = 0;
            $ispit->ocenaUsmeni = 0;
            $ispit->konacnaOcena = 0;
            $ispit->brojBodova = 0;
            $ispit->statusIspita = 0;
            $ispit->save();
        }
    }

    public function deletePrivremeniIspit(int $id): void
    {
        $polozenIspit = PolozeniIspiti::find($id);
        $polozenIspit->delete();
    }

    // -------------------------------------------------------------------------
    // Priznavanje ispita
    // -------------------------------------------------------------------------

    public function getPriznavanjeData(int $id): array
    {
        $kandidat = Kandidat::find($id);

        $predmetProgram = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar')->get();

        return compact('kandidat', 'predmetProgram');
    }

    public function storePriznatiIspiti(int $kandidatId, ?array $predmetIds, array $konacneOcene): void
    {
        if ($predmetIds != null) {
            foreach ($predmetIds as $index => $ispit) {
                $polozenIspit = new PolozeniIspiti;
                $polozenIspit->kandidat_id = $kandidatId;
                $polozenIspit->predmet_id = $ispit;
                $polozenIspit->zapisnik_id = 0;
                $polozenIspit->prijava_id = 0;
                $polozenIspit->konacnaOcena = $konacneOcene[$index];
                $polozenIspit->statusIspita = 5;
                $polozenIspit->indikatorAktivan = 1;
                $polozenIspit->save();
            }
        }
    }

    public function deletePriznatIspit(int $id): int
    {
        $polozenIspit = PolozeniIspiti::find($id);
        $kandidatId = $polozenIspit->kandidat_id;
        $polozenIspit->delete();

        return $kandidatId;
    }

    // -------------------------------------------------------------------------
    // Update zapisnik details
    // -------------------------------------------------------------------------

    public function updateZapisnikDetails(int $zapisnikId, array $data): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::find($zapisnikId);
        $zapisnik->vreme = $data['vreme'];
        $zapisnik->ucionica = $data['ucionica'];
        $zapisnik->datum = $data['datum'];
        $zapisnik->datum2 = $data['datum2'];
        $zapisnik->save();
    }

    // -------------------------------------------------------------------------
    // Arhiva
    // -------------------------------------------------------------------------

    public function getArhiviraniZapisnici(): array
    {
        $arhiviraniZapisnici = ZapisnikOPolaganjuIspita::where(['arhiviran' => true])->get();
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

    // =========================================================================
    // PDF methods (kept from original)
    // =========================================================================

    public function generatePdfAsync(int $zapisnikId): string
    {
        $storagePath = 'pdfs/zapisnik_' . $zapisnikId . '_' . time() . '.pdf';
        GenerateZapisnikPdfJob::dispatch($zapisnikId, $storagePath);

        return $storagePath;
    }

    public function zapisnikStampa(Request $request)
    {
        try {
            $zapisnik = ZapisnikOPolaganjuIspita::find($request->id);
            $zapisnikStudent = ZapisnikOPolaganju_Student::where(['zapisnik_id' => $request->id])->get();

            $ids = array_map(function (ZapisnikOPolaganju_Student $o) {
                return $o->kandidat_id;
            }, $zapisnikStudent->all());

            $studenti = Kandidat::whereIn('id', $ids)->orderByRaw('SUBSTR(brojIndeksa, 5)')->orderBy('brojIndeksa')->get();

            $prijavaIds = [];
            foreach ($ids as $id) {
                $pom = PrijavaIspita::where([
                    'predmet_id' => $zapisnik->predmet_id,
                    'rok_id' => $zapisnik->rok_id,
                    'kandidat_id' => $id,
                ])->first();
                if ($pom != null) {
                    $prijavaIds[$id] = $pom->id;
                }
            }

            $polozeniIspitIds = [];
            foreach ($ids as $id) {
                $pom = PolozeniIspiti::where([
                    'zapisnik_id' => $zapisnik->id,
                    'predmet_id' => $zapisnik->predmet_id,
                    'kandidat_id' => $id,
                ])->first();
                if ($pom != null) {
                    $polozeniIspitIds[$id] = $pom->id;
                }
            }

            $polozeniIspiti = DB::table('polozeni_ispiti')
                ->where(['polozeni_ispiti.zapisnik_id' => $request->id])
                ->join('kandidat', 'polozeni_ispiti.kandidat_id', '=', 'kandidat.id')
                ->join('prijava_ispita', 'polozeni_ispiti.prijava_id', '=', 'prijava_ispita.id')
                ->select(
                    'kandidat.*',
                    'kandidat.brojIndeksa as indeks',
                    'prijava_ispita.brojPolaganja as polaganja',
                    'polozeni_ispiti.brojBodova as brojBodova',
                    'polozeni_ispiti.konacnaOcena as konacnaOcena',
                    'polozeni_ispiti.statusIspita as statusIspita'
                )
                ->orderByRaw('SUBSTR(indeks, 5)')
                ->orderBy('indeks')
                ->get();

            $ispit = Predmet::where(['id' => $zapisnik->predmet_id])->first();

            $predmetiProgramiSpisak = PredmetProgram::where(['predmet_id' => $zapisnik->predmet_id])->get();

            $ids = [];
            foreach ($predmetiProgramiSpisak as $item) {
                $ids[] = $item->studijskiProgram_id;
            }

            $programi = StudijskiProgram::whereIn('id', $ids)->get();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        $view = View::make('izvestaji.zapisnik')
            ->with('zapisnik', $zapisnik)
            ->with('studenti', $studenti)
            ->with('ispit', $ispit->naziv)
            ->with('polozeniIspiti', $polozeniIspiti)
            ->with('predmet', $request->predmet)
            ->with('rok', $request->rok)
            ->with('profesor', $request->profesor)
            ->with('programi', $programi)
            ->with('datum', $zapisnik->datum)
            ->with('vreme', $zapisnik->vreme)
            ->with('ucionica', $zapisnik->ucionica)
            ->with('datum2', $zapisnik->datum2);

        $contents = $view->render();
        PDF::SetAutoPageBreak(true, 5);
        PDF::SetTitle('Записник о полагању испита');
        PDF::AddPage();
        PDF::SetFont('dejavusans', '', 10);
        PDF::WriteHtml($contents, true);
        PDF::Output('Zapisnik.pdf');
    }

    public function polozeniStampa($id)
    {
        try {
            $student = Kandidat::find($id);

            $ispiti = DB::table('polozeni_ispiti')
                ->where(['polozeni_ispiti.kandidat_id' => $id])
                ->join('prijava_ispita', 'polozeni_ispiti.prijava_id', '=', 'prijava_ispita.id')
                ->join('predmet', 'prijava_ispita.predmet_id', '=', 'predmet.id')
                ->join('profesor', 'prijava_ispita.profesor_id', '=', 'profesor.id')
                ->select(
                    'predmet.naziv as predmet',
                    'profesor.ime as ime',
                    'profesor.prezime as prezime',
                    'polozeni_ispiti.brojBodova as poeni',
                    'polozeni_ispiti.konacnaOcena as ocena'
                )
                ->get();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.polozeniStampa')
            ->with('student', $student)
            ->with('ispiti', $ispiti);

        $contents = $view->render();
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->SetTitle('Уверење о положеним испитима');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents, true);
        $pdf->Output('Ispiti.pdf');
    }

    public function nastavniPlan(Request $request)
    {
        try {
            $predmet = Predmet::where('id', $request->predmet)->first();
            $program = StudijskiProgram::where('id', $request->program)->first();
            $godina = GodinaStudija::where('id', $request->godina)->first();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.nastavniPlan')
            ->with('predmet', $predmet)
            ->with('program', $program)
            ->with('godina', $godina);

        $contents = $view->render();
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->SetTitle('Наставни план');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('NastavniPlan.pdf');
    }
}
