<?php

namespace App\Services;

use App\DTOs\NastavniPlanData;
use App\DTOs\ZapisnikStampaData;
use App\Jobs\GenerateZapisnikPdfJob;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\StudijskiProgram;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDF;
use View;

/**
 * PDF generation service for exam-related reports.
 *
 * Extracted from IspitService to separate PDF rendering from exam CRUD.
 * See docs/ADR/001-god-services.md
 *
 * @see BasePdfService
 */
class IspitPdfService extends BasePdfService
{
    public function generatePdfAsync(int $zapisnikId): string
    {
        $storagePath = 'pdfs/zapisnik_'.$zapisnikId.'_'.time().'.pdf';
        GenerateZapisnikPdfJob::dispatch($zapisnikId, $storagePath);

        return $storagePath;
    }

    public function zapisnikStampa(ZapisnikStampaData $data)
    {
        try {
            $zapisnik = ZapisnikOPolaganjuIspita::find($data->zapisnikId);
            $zapisnikStudent = ZapisnikOPolaganju_Student::where(['zapisnik_id' => $data->zapisnikId])->get();

            $ids = array_map(function (ZapisnikOPolaganju_Student $o) {
                return $o->kandidat_id;
            }, $zapisnikStudent->all());

            $studenti = Kandidat::whereIn('id', $ids)->orderByRaw('SUBSTR(brojIndeksa, 5)')->orderBy('brojIndeksa')->get();

            $prijavaIds = [];
            $studentiMap = $studenti->keyBy('id');
            $tipStudijaIds = $studentiMap->pluck('tipStudija_id')->unique()->all();
            $studijskiProgramIds = $studentiMap->pluck('studijskiProgram_id')->unique()->all();
            $predmetProgramLookup = PredmetProgram::where('predmet_id', $zapisnik->predmet_id)
                ->whereIn('tipStudija_id', $tipStudijaIds)
                ->whereIn('studijskiProgram_id', $studijskiProgramIds)
                ->get()
                ->keyBy(function ($item) {
                    return $item->tipStudija_id.'_'.$item->studijskiProgram_id;
                });

            foreach ($ids as $id) {
                $kandidat = $studentiMap->get($id);
                $predmetProgram = $kandidat === null
                    ? null
                    : $predmetProgramLookup->get($kandidat->tipStudija_id.'_'.$kandidat->studijskiProgram_id);

                if ($predmetProgram === null) {
                    continue;
                }

                $pom = PrijavaIspita::where([
                    'predmet_id' => $predmetProgram->id,
                    'rok_id' => $zapisnik->rok_id,
                    'kandidat_id' => $id,
                ])->first();
                if ($pom != null) {
                    $prijavaIds[$id] = $pom->id;
                }
            }

            $polozeniIspitIds = [];
            foreach ($ids as $id) {
                $kandidat = $studentiMap->get($id);
                $predmetProgram = $kandidat === null
                    ? null
                    : $predmetProgramLookup->get($kandidat->tipStudija_id.'_'.$kandidat->studijskiProgram_id);

                if ($predmetProgram === null) {
                    continue;
                }

                $pom = PolozeniIspiti::where([
                    'zapisnik_id' => $zapisnik->id,
                    'predmet_id' => $predmetProgram->id,
                    'kandidat_id' => $id,
                ])->first();
                if ($pom != null) {
                    $polozeniIspitIds[$id] = $pom->id;
                }
            }

            $polozeniIspiti = DB::table('polozeni_ispiti')
                ->where(['polozeni_ispiti.zapisnik_id' => $data->zapisnikId])
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
            report($e);
            throw new \RuntimeException('Грешка при учитавању података за записник: '.$e->getMessage(), 0, $e);
        }

        $view = View::make('izvestaji.zapisnik')
            ->with('zapisnik', $zapisnik)
            ->with('studenti', $studenti)
            ->with('ispit', $ispit->naziv)
            ->with('polozeniIspiti', $polozeniIspiti)
            ->with('predmet', $data->predmet)
            ->with('rok', $data->rok)
            ->with('profesor', $data->profesor)
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
                ->join('predmet_program', 'prijava_ispita.predmet_id', '=', 'predmet_program.id')
                ->join('predmet', 'predmet_program.predmet_id', '=', 'predmet.id')
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
            report($e);
            throw new \RuntimeException('Грешка при учитавању положених испита: '.$e->getMessage(), 0, $e);
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

    public function nastavniPlan(NastavniPlanData $data)
    {
        try {
            $predmet = Predmet::where('id', $data->predmetId)->first();
            $program = StudijskiProgram::where('id', $data->programId)->first();
            $godina = GodinaStudija::where('id', $data->godinaId)->first();
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Грешка при учитавању наставног плана: '.$e->getMessage(), 0, $e);
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
