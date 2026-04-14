<?php

namespace App\Services;

use App\DTOs\DiplomskiAddData;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\DiplomskiRad;
use App\Models\Kandidat;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use Elibyy\TCPDF\TCPDF;
use Illuminate\Database\QueryException;
use View;

class DiplomskiRadService extends BasePdfService
{
    public function diplomskiUnos(Kandidat $student)
    {
        $profesor = Profesor::all();

        return view('izvestaji.diplomskiUnos')
            ->with('student', $student)
            ->with('profesor', $profesor);
    }

    public function diplomskiAdd(DiplomskiAddData $data)
    {
        $kandidat = Kandidat::findOrFail($data->kandidatId);

        $diplomski = new DiplomskiRad;
        $diplomski->kandidat_id = $data->kandidatId;
        $diplomski->predmet_id = $data->predmetId;
        $diplomski->naziv = $data->naziv;
        $diplomski->mentor_id = $data->mentorId;
        $diplomski->predsednik_id = $data->predsednikId;
        $diplomski->clan_id = $data->clanId;
        $diplomski->ocenaOpis = $data->ocenaOpis;
        $diplomski->ocenaBroj = $data->ocenaBroj;
        $diplomski->datumPrijave = $data->datumPrijave;
        $diplomski->datumOdbrane = $data->datumOdbrane;
        $diplomski->save();

        $prijava = new DiplomskiPrijavaTeme;
        $prijava->tipStudija_id = $kandidat->tipStudija_id;
        $prijava->studijskiProgram_id = $kandidat->studijskiProgram_id;
        $prijava->kandidat_id = $data->kandidatId;
        $prijava->predmet_id = $data->predmetId;
        $prijava->nazivTeme = $data->naziv;
        $prijava->profesor_id = $data->mentorId;
        $prijava->datum = $data->datumPrijave;
        $prijava->indikatorOdobreno = false;
        $prijava->save();

        return redirect('/student');
    }

    public function komisijaStampa(Kandidat $student)
    {
        $diplomski = DiplomskiRad::where('kandidat_id', $student->id)->first();

        if (! $diplomski) {
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

            if (! $diplomskiPolaganje) {
                return redirect()->back()->with('error', 'Дипломско полагање није пронађено');
            }

            $diplomski = DiplomskiRad::where('kandidat_id', $student->id)->first();

            $pdf_settings = \Config::get('tcpdf');
            $pdf = new TCPDF([
                $pdf_settings['page_orientation'],
                $pdf_settings['page_units'],
                $pdf_settings['page_format'],
                true,
                'UTF-8',
                false,
            ]);

            $view = View::make('izvestaji.zapisnikDiplomski')
                ->with('student', $student)
                ->with('diplomski', $diplomski)
                ->with('diplomskiPolaganje', $diplomskiPolaganje);

            $contents = $view->render();
            $pdf->SetAutoPageBreak(true, 5);
            $pdf->SetTitle('Записник са одбране дипломског');
            $pdf->AddPage();
            $pdf->SetFont('freeserif', '', 10);
            $pdf->WriteHtml($contents);
            $pdf->Output('ZapisnikDiplomski.pdf');
        } catch (QueryException $e) {
            report($e);
            throw new \RuntimeException('Грешка при генерисању записника дипломског: '.$e->getMessage(), 0, $e);
        }
    }
}
