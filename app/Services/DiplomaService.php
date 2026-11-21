<?php

namespace App\Services;

use App\DTOs\DiplomaAddData;
use App\Models\Diploma;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class DiplomaService extends BasePdfService
{
    public function potvrdeStudent(Kandidat $student)
    {
        return View::make('izvestaji.potvrdeStudent')
            ->with('student', $student);
    }

    public function diplomaUnos(Kandidat $student)
    {
        $program = StudijskiProgram::all();
        $godina = GodinaStudija::all();
        $skolskaGodina = SkolskaGodUpisa::all();
        $profesor = Profesor::all();

        return view('izvestaji.diplomaUnos')
            ->with('student', $student)
            ->with('program', $program)
            ->with('godina', $godina)
            ->with('skolskaGodina', $skolskaGodina)
            ->with('profesor', $profesor);
    }

    public function diplomaAdd(DiplomaAddData $data)
    {
        $diploma = new Diploma;
        $diploma->kandidat_id = $data->kandidatId;
        $diploma->brojDiplome = $data->brojDiplome;
        $diploma->datumOdbrane = $data->datumOdbrane;
        $diploma->nazivStudijskogPrograma = $data->nazivStudijskogPrograma;
        $diploma->brojPocetnogLista = $data->brojPocetnogLista;
        $diploma->brojZapisnika = $data->brojZapisnika;
        $diploma->datum = $data->datum;
        $diploma->pristupniRad = $data->pristupniRad;
        $diploma->tema = $data->tema;
        $diploma->mentor = $data->mentor;
        $diploma->ocena = $data->ocena;
        $diploma->save();

        return redirect()->route('student.index');
    }

    public function diplomaStampa(Kandidat $student): Response
    {
        $student->loadMissing(['program.tipStudija', 'godinaUpisa', 'godinaStudija']);
        $diploma = Diploma::where('kandidat_id', $student->id)->first();
        $diplomski = DiplomskiPrijavaOdbrane::where('kandidat_id', $student->id)->first();
        $prosek = (float) (PolozeniIspiti::where('kandidat_id', $student->id)->avg('konacnaOcena') ?? 0);

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.diplomaStampa')
            ->with('student', $student)
            ->with('diploma', $diploma)
            ->with('diplomski', $diplomski)
            ->with('prosek', $prosek);

        $contents = $view->render();
        $pdf->SetTitle('Уверење');
        $pdf->SetMargins(12, 2, 12, true);
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 12);
        $pdf->WriteHtml($contents, true);

        return $this->pdfResponse($pdf->Output('Uverenje.pdf', 'S'), 'Uverenje.pdf');
    }
}
