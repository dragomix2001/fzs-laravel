<?php

namespace App\Services;

use App\Kandidat;
use App\PolozeniIspiti;
use App\Predmet;
use App\PredmetProgram;
use App\PrijavaIspita;
use App\StudijskiProgram;
use App\ZapisnikOPolaganju_Student;
use App\ZapisnikOPolaganjuIspita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use View;

class IspitService extends BasePdfService
{
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
        } catch (\Illuminate\Database\QueryException $e) {
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
        } catch (\Illuminate\Database\QueryException $e) {
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
            $godina = \App\GodinaStudija::where('id', $request->godina)->first();
        } catch (\Illuminate\Database\QueryException $e) {
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
