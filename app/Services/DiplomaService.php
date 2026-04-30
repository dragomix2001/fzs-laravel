<?php

namespace App\Services;

use App\DTOs\DiplomaAddData;
use App\Models\Diploma;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use View;

class DiplomaService extends BasePdfService
{
    public function potvrdeStudent(Kandidat $student)
    {
        $pdf = $this->createPdf();
        $view = View::make('izvestaji.potvrdeStudent')
            ->with('student', $student);

        $contents = $view->render();
        $pdf->SetTitle('Потврда о студирању');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 12);
        $pdf->WriteHtml($contents);
        $pdf->Output('Potvrda.pdf');
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

    public function diplomaStampa(Kandidat $student)
    {
        $diploma = Diploma::where('kandidat_id', $student->id)->first();

        if (! $diploma) {
            return redirect()->back()->with('error', 'Диплома није пронађена');
        }

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.diplomaStampa')
            ->with('student', $student)
            ->with('diploma', $diploma);

        $contents = $view->render();
        $pdf->SetTitle('Диплома');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 12);
        $pdf->WriteHtml($contents, true);
        $pdf->Output('Diploma.pdf');
    }
}
