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

        $this->generatePdf('izvestaji.test', [
            'studijskiProgram' => $program,
            'kandidat' => $kandidat,
            'godina' => $godina,
            'uslov' => $picks3,
        ], 'Seznam кандидата по модулима', 'Spisak.pdf');
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

        $this->generatePdf('izvestaji.integralno', [
            'kandidat' => $kandidat,
            'godina' => $godina,
            'tip' => $tipStudija,
            'tipSvi' => $tipStudija,
        ], 'Интегрални списак', 'Integralni.pdf');
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

        $this->generatePdf('izvestaji.spisakSvihStudenataOstalo', [
            'kandidat' => $kandidat,
            'program' => $program,
            'godina' => $godina,
        ], 'Seznam свих студената - остали', 'SpisakOstali.pdf');
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

        $this->generatePdf('izvestaji.spisakSvihStudenata', [
            'kandidat' => $kandidat,
            'program' => $program,
            'tip' => $tipStudija,
            'tipSvi' => $tipStudija,
            'godina' => $godinaStudija,
        ], 'Seznam свих студената', 'SpisakAktivni.pdf');
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

        $this->generatePdf('izvestaji.spisakSmer', [
            'studenti' => $kandidat,
            'program' => $program ? $program->naziv : '',
            'godina' => $godina,
        ], 'Seznam за смер', 'SpisakSmer.pdf');
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

        $this->generatePdf('izvestaji.spisakPoProgramu', [
            'kandidat' => $kandidat,
            'program' => $program,
        ], 'Seznam по програму', 'SpisakPoProgramu.pdf');
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

        $this->generatePdf('izvestaji.spisakPoGodini', [
            'kandidat' => $kandidat,
            'godinaNaziv' => $godina,
        ], 'Seznam по години', 'SpisakPoGodini.pdf');
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

        $this->generatePdf('izvestaji.spisakPoSlavama', [
            'kandidat' => $kandidat,
            'uslov' => $uslov,
            'slave' => $slave,
        ], 'Seznam по крсним славама', 'SpisakPoSlavama.pdf');
    }

    public function spisakPoProfesorima()
    {
        $profesori = Profesor::all();
        $veza = ProfesorPredmet::with('predmet.predmet')->get();

        $this->generatePdf('izvestaji.predmetiPoProfesorima', [
            'profesori' => $profesori,
            'veza' => $veza,
        ], 'Seznam предмета по професорима', 'SpisakPoProfesorima.pdf');
    }

    public function spiskoviStudenti()
    {
        $kandidat = Kandidat::where(['statusUpisa_id' => 3])
            ->orderByRaw('SUBSTR(brojIndeksa, 5)')
            ->orderBy('brojIndeksa')
            ->get();

        $program = StudijskiProgram::all();
        $skolskaGodina6 = SkolskaGodUpisa::all();

        $this->generatePdf('izvestaji.spiskoviStudenti', [
            'kandidat' => $kandidat,
            'program' => $program,
            'programS' => $program,
            'programPlan' => $program,
            'programE' => $program,
            'skolskaGodina6' => $skolskaGodina6,
            'skolskaGodina3' => $skolskaGodina6,
            'skolskaGodina' => $skolskaGodina6,
            'skolskaGodina4' => $skolskaGodina6,
            'tipStudija' => TipStudija::all(),
            'predmeti' => Predmet::all(),
            'skolskaGodinaE' => $skolskaGodina6,
            'skolskaGodina7' => $skolskaGodina6,
            'skolskaGodina8' => $skolskaGodina6,
            'skolskaGodina9' => $skolskaGodina6,
        ], 'Списци студената', 'SpiskoviStudenti.pdf');
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

        $this->generatePdf('izvestaji.spisakPoPredmetima', [
            'studenti' => $studenti,
            'programi' => $programi,
            'predmet' => $predmet ? $predmet->naziv : '',
        ], 'Seznam студената по предметима', 'SpisakPoPredmetima.pdf');
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

        $this->generatePdf('izvestaji.diplomirani', [
            'diplomirani' => $kandidat,
            'godina' => $godina,
        ], 'Дипломирани студенти', 'Diplomirani.pdf');
    }
}
