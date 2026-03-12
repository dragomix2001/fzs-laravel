<?php

namespace App\Services;

use App\Diploma;
use App\Kandidat;
use App\StudijskiProgram;
use App\Profesor;
use App\GodinaStudija;
use App\SkolskaGodUpisa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use View;
use PDF;

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

    public function diplomaAdd(Request $request)
    {
        $diploma = new Diploma();
        $diploma->kandidat_id = $request->kandidat_id;
        $diploma->brojDipломе = $request->brojDiplome;
        $diploma->datumOdbrane = $request->datumOdbrane;
        $diploma->nazivStudijskogPrograma = $request->nazivStudijskogPrograma;
        $diploma->brojPočetnogLista = $request->brojPocetnogLista;
        $diploma->brojЗаписника = $request->brojZapisnika;
        $diploma-> datum = $request->datum;
        $diploma->pristupniRad = $request->pristupniRad;
        $diploma->tema = $request->tema;
        $diploma->mentor = $request->mentor;
        $diploma->ocena = $request->ocena;
        $diploma->save();

        return redirect()->route('student.index');
    }

    public function diplomaStampa(Kandidat $student)
    {
        $diploma = Diploma::where('kandidat_id', $student->id)->first();
        
        if (!$diploma) {
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
