<?php

namespace App\Services;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use Illuminate\Support\Facades\DB;
use View;

class StudentListService extends BasePdfService
{
    public function spisakPoSmerovima()
    {
        $kandidat = Kandidat::where(['statusUpisa_id' => 3])
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $picks = Kandidat::where(['statusUpisa_id' => 3])
            ->distinct('studijskiProgram_id', 'godinaStudija_id')
            ->select('studijskiProgram_id')
            ->groupBy('studijskiProgram_id', 'godinaStudija_id')
            ->get();

        $picks2 = Kandidat::where(['statusUpisa_id' => 3])
            ->distinct('godinaStudija_id')
            ->select('godinaStudija_id')
            ->groupBy('godinaStudija_id')
            ->get();

        $picks3 = Kandidat::where(['statusUpisa_id' => 3])
            ->distinct('studijskiProgram_id', 'godinaStudija_id')
            ->select('studijskiProgram_id', 'godinaStudija_id')
            ->groupBy('studijskiProgram_id', 'godinaStudija_id')
            ->get();

        $uslov = [];
        $uslov2 = [];
        foreach ($picks as $item) {
            $uslov[] = $item->studijskiProgram_id;
        }
        foreach ($picks2 as $item) {
            $uslov2[] = $item->godinaStudija_id;
        }

        $program = StudijskiProgram::whereIn('id', $uslov)->get();
        $godina = GodinaStudija::whereIn('id', $uslov2)->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.test')
            ->with('studijskiProgram', $program)
            ->with('kandidat', $kandidat)
            ->with('godina', $godina)
            ->with('uslov', $picks3);

        $contents = $view->render();
        $pdf->SetTitle('Списак кандидата по модулима');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('Spisak.pdf');
    }

    public function integralno($godina)
    {
        $statusi = ['1', '2', '4', '5', '7'];

        $kandidat = DB::table('kandidat')
            ->join('studijski_program', 'kandidat.studijskiProgram_id', '=', 'studijski_program.id')
            ->whereIn('kandidat.statusUpisa_id', $statusi)
            ->where(['kandidat.skolskaGodinaUpisa_id' => $godina])
            ->select('kandidat.*', 'kandidat.godinaStudija_id as godina', 'studijski_program.skrNazivStudijskogPrograma as program')
            ->orderByRaw('SUBSTR(kandidat.brojIndeksa, 5)')
            ->orderBy('kandidat.brojIndeksa')
            ->get();

        $picks2 = Kandidat::where(['statusUpisa_id' => 1])
            ->distinct('tipStudija_id')
            ->select('tipStudija_id')
            ->groupBy('tipStudija_id')
            ->get();

        $uslov2 = [];
        foreach ($picks2 as $item) {
            $uslov2[] = $item->tipStudija_id;
        }

        $tipStudija = TipStudija::whereIn('id', $uslov2)->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.integralno')
            ->with('kandidat', $kandidat)
            ->with('godina', $godina)
            ->with('tip', $tipStudija)
            ->with('tipSvi', $tipStudija);

        $contents = $view->render();
        $pdf->SetTitle('Интегрални списак');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('Integralni.pdf');
    }

    public function spisakPoSmerovimaOstali($godina)
    {
        $statusi = ['1', '2', '4', '5', '7'];

        $kandidat = DB::table('kandidat')
            ->join('studijski_program', 'kandidat.studijskiProgram_id', '=', 'studijski_program.id')
            ->whereIn('kandidat.statusUpisa_id', $statusi)
            ->where(['kandidat.skolskaGodinaUpisa_id' => $godina])
            ->select('kandidat.*', 'kandidat.godinaStudija_id as godina', 'studijski_program.skrNazivStudijskogPrograma as program')
            ->orderByRaw('SUBSTR(kandidat.brojIndeksa, 5)')
            ->orderBy('kandidat.brojIndeksa')
            ->get();

        $picks = Kandidat::whereIn('statusUpisa_id', $statusi)
            ->where(['skolskaGodinaUpisa_id' => $godina])
            ->distinct('studijskiProgram_id')
            ->select('studijskiProgram_id')
            ->groupBy('studijskiProgram_id')
            ->get();

        $uslov = [];
        foreach ($picks as $item) {
            $uslov[] = $item->studijskiProgram_id;
        }

        $program = StudijskiProgram::whereIn('id', $uslov)->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spisakSvihStudenataOstalo')
            ->with('kandidat', $kandidat)
            ->with('program', $program)
            ->with('godina', $godina);

        $contents = $view->render();
        $pdf->SetTitle('Списак свих студената - остали');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakOstali.pdf');
    }

    public function spisakPoSmerovimaAktivni($godina)
    {
        $kandidat = Kandidat::where(['statusUpisa_id' => 3, 'skolskaGodinaUpisa_id' => $godina])
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $picks = Kandidat::where(['statusUpisa_id' => 3, 'skolskaGodinaUpisa_id' => $godina])
            ->distinct('studijskiProgram_id')
            ->select('studijskiProgram_id')
            ->groupBy('studijskiProgram_id')
            ->get();

        $uslov = [];
        foreach ($picks as $item) {
            $uslov[] = $item->studijskiProgram_id;
        }

        $program = StudijskiProgram::whereIn('id', $uslov)->get();

        $tipStudija = TipStudija::all();
        $godinaStudija = GodinaStudija::all();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spisakSvihStudenata')
            ->with('kandidat', $kandidat)
            ->with('program', $program)
            ->with('tip', $tipStudija)
            ->with('tipSvi', $tipStudija)
            ->with('godina', $godinaStudija);

        $contents = $view->render();
        $pdf->SetTitle('Списак свих студената');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakAktivni.pdf');
    }

    public function spisakZaSmer($programId, $godinaId)
    {
        $kandidat = Kandidat::where([
            'statusUpisa_id' => 3,
            'studijskiProgram_id' => $programId,
            'godinaStudija_id' => $godinaId,
        ])
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $program = StudijskiProgram::find($programId);
        $godina = GodinaStudija::find($godinaId);

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spisakSmer')
            ->with('studenti', $kandidat)
            ->with('program', $program ? $program->naziv : '')
            ->with('godina', $godina);

        $contents = $view->render();
        $pdf->SetTitle('Списак за смер');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakSmer.pdf');
    }

    public function spisakPoProgramu($programId)
    {
        $kandidat = Kandidat::where([
            'statusUpisa_id' => 3,
            'studijskiProgram_id' => $programId,
        ])
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $program = StudijskiProgram::find($programId);

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spisakPoProgramu')
            ->with('kandidat', $kandidat)
            ->with('program', $program);

        $contents = $view->render();
        $pdf->SetTitle('Списак по програму');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakPoProgramu.pdf');
    }

    public function spisakPoGodini($godinaId)
    {
        $kandidat = Kandidat::where([
            'statusUpisa_id' => 3,
            'godinaStudija_id' => $godinaId,
        ])
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $godina = GodinaStudija::find($godinaId);

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spisakPoGodini')
            ->with('kandidat', $kandidat)
            ->with('godinaNaziv', $godina);

        $contents = $view->render();
        $pdf->SetTitle('Списак по години');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakPoGodini.pdf');
    }

    public function spisakPoSlavama()
    {
        $kandidat = Kandidat::where('statusUpisa_id', 3)
            ->whereNotNull('krsnaSlava_id')
            ->orderBy('krsnaSlava_id')
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $uslov = Kandidat::where('statusUpisa_id', 3)
            ->whereNotNull('krsnaSlava_id')
            ->distinct('krsnaSlava_id')
            ->select('krsnaSlava_id')
            ->groupBy('krsnaSlava_id')
            ->get();

        $slave = DB::table('krsna_slava')->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spisakPoSlavama')
            ->with('kandidat', $kandidat)
            ->with('uslov', $uslov)
            ->with('slave', $slave);

        $contents = $view->render();
        $pdf->SetTitle('Списак по крсним славама');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakPoSlavama.pdf');
    }

    public function spisakPoProfesorima()
    {
        $profesori = Profesor::all();
        $veza = ProfesorPredmet::with('predmet.predmet')->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.predmetiPoProfesorima')
            ->with('profesori', $profesori)
            ->with('veza', $veza);

        $contents = $view->render();
        $pdf->SetTitle('Списак предмета по професорима');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakPoProfesorima.pdf');
    }

    public function spiskoviStudenti()
    {
        $kandidat = Kandidat::where(['statusUpisa_id' => 3])
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $program = StudijskiProgram::all();
        $skolskaGodina6 = SkolskaGodUpisa::all();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spiskoviStudenti')
            ->with('kandidat', $kandidat)
            ->with('program', $program)
            ->with('programS', $program)
            ->with('programPlan', $program)
            ->with('programE', $program)
            ->with('skolskaGodina6', $skolskaGodina6)
            ->with('skolskaGodina3', $skolskaGodina6)
            ->with('skolskaGodina', $skolskaGodina6)
            ->with('skolskaGodina4', $skolskaGodina6)
            ->with('tipStudija', TipStudija::all())
            ->with('predmeti', Predmet::all())
            ->with('skolskaGodinaE', $skolskaGodina6)
            ->with('skolskaGodina7', $skolskaGodina6)
            ->with('skolskaGodina8', $skolskaGodina6)
            ->with('skolskaGodina9', $skolskaGodina6);

        $contents = $view->render();
        $pdf->SetTitle('Списци студената');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpiskoviStudenti.pdf');
    }

    public function spisakPoPredmetima($predmetId)
    {
        $predmetProgramIds = PredmetProgram::where('predmet_id', $predmetId)->pluck('id');
        $programi = PredmetProgram::where('predmet_id', $predmetId)->with('program')->get();
        $prijave = PrijavaIspita::whereIn('predmet_id', $predmetProgramIds)->get();
        $predmet = Predmet::find($predmetId);

        $kandidatIds = $prijave->pluck('kandidat_id')->unique();
        $studenti = Kandidat::whereIn('id', $kandidatIds)
            ->select('kandidat.*', 'kandidat.godinaStudija_id as godina', 'kandidat.studijskiProgram_id as program_id')
            ->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.spisakPoPredmetima')
            ->with('studenti', $studenti)
            ->with('programi', $programi)
            ->with('predmet', $predmet ? $predmet->naziv : '');

        $contents = $view->render();
        $pdf->SetTitle('Списак студената по предметима');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('SpisakPoPredmetima.pdf');
    }

    public function spisakDiplomiranih($godina)
    {
        $statusi = [6];

        $kandidat = DB::table('kandidat')
            ->join('studijski_program', 'kandidat.studijskiProgram_id', '=', 'studijski_program.id')
            ->whereIn('kandidat.statusUpisa_id', $statusi)
            ->where(['kandidat.skolskaGodinaUpisa_id' => $godina])
            ->select('kandidat.*', 'studijski_program.naziv as program')
            ->orderByRaw('SUBSTR(kandidat.brojIndeksa, 5)')
            ->orderBy('kandidat.brojIndeksa')
            ->get();

        $pdf = $this->createPdf();
        $view = View::make('izvestaji.diplomirani')
            ->with('diplomirani', $kandidat)
            ->with('godina', $godina);

        $contents = $view->render();
        $pdf->SetTitle('Дипломирани студенти');
        $pdf->AddPage();
        $pdf->SetFont('freeserif', '', 10);
        $pdf->WriteHtml($contents);
        $pdf->Output('Diplomirani.pdf');
    }
}
