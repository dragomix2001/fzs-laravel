<?php

namespace App\Services;

use App\DiplomskiPolaganje;
use App\DiplomskiPrijavaOdbrane;
use App\DiplomskiPrijavaTeme;
use App\DiplomskiRad;
use App\Kandidat;
use App\Profesor;
use App\ProfesorPredmet;
use Illuminate\Http\Request;
use View;
use PDF;

class DiplomskiRadService extends BasePdfService
{
    public function diplomskiUnos(Kandidat $student)
    {
        $profesor = Profesor::all();

        return view('izvestaji.diplomskiUnos')
            ->with('student', $student)
            ->with('profesor', $profesor);
    }

    public function diplomskiAdd(Request $request)
    {
        $diplomski = new DiplomskiRad();
        $diplomski->kandidat_id = $request->kandidat_id;
        $diplomski->tema = $request->tema;
        $diplomski->mentor = $request->mentor;
        $diplomski->datumPrijave = $request->datumPrijave;
        $diplomski->save();

        $prijava = new DiplomskiPrijavaTeme();
        $prijava->kandidat_id = $request->kandidat_id;
        $prijava->tema = $request->tema;
        $prijava->mentor = $request->mentor;
        $prijava->datum = $request->datumPrijave;
        $prijava->save();

        return redirect()->route('student.index');
    }

    public function komisijaStampa(Kandidat $student)
    {
        $diplomski = DiplomskiRad::where('kandidat_id', $student->id)->first();
        
        if (!$diplomski) {
            return redirect()->back()->with('error', 'Дипломски рад није пронађен');
        }

        $profPredmet = ProfesorPredmet::where('predmet_id', $diplomski->predmet_id)->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.komisijaStampa')
            ->with('student', $student)
            ->with('diplomski', $diplomski)
            ->with('profPredmet', $profPredmet);

        $contents = $view->render();
        $pdf->SetTitle('Одлука о формирању комисије');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 12);
        $pdf->WriteHtml($contents, true);
        $pdf->Output('Komisija.pdf');
    }

    public function zapisnikDiplomski(Kandidat $student)
    {
        try {
            $diplomskiPolaganje = DiplomskiPolaganje::where('kandidat_id', $student->id)->first();
            
            if (!$diplomskiPolaganje) {
                return redirect()->back()->with('error', 'Дипломско полагање није пронађено');
            }

            $diplomski = DiplomskiRad::where('kandidat_id', $student->id)->first();
            
            $pdf_settings = \Config::get('tcpdf');
            $pdf = new \Elibyy\TCPDF\TCPDF([
                $pdf_settings['page_orientation'],
                $pdf_settings['page_units'],
                $pdf_settings['page_format'],
                true,
                'UTF-8',
                false
            ], 'tcpdf');

            $view = View::make('izvestaji.zapisnikDiplomski')
                ->with('student', $student)
                ->with('diplomski', $diplomski)
                ->with('diplomskiPolaganje', $diplomskiPolaganje);

            $contents = $view->render();
            $pdf->SetAutoPageBreak(TRUE, 5);
            $pdf->SetTitle('Записник са одбране дипломског');
            $pdf->AddPage();
            $pdf->SetFont('freeserif', '', 10);
            $pdf->WriteHtml($contents);
            $pdf->Output('ZapisnikDiplomski.pdf');
        } catch (\Illuminate\Database\QueryException $e) {
            dd('Дошло је до непредвиђене грешке.' . $e->getMessage());
        }
    }
}
