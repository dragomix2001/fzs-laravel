<?php

namespace App\Http\Controllers;

use App\Models\Raspored;
use App\Models\Predmet;
use App\Models\Profesor;
use App\Models\StudijskiProgram;
use App\Models\GodinaStudija;
use App\Models\Semestar;
use App\Models\SkolskaGodUpisa;
use App\Models\OblikNastave;
use Illuminate\Http\Request;

class RasporedController extends Controller
{
    public function index(Request $request)
    {
        $query = Raspored::with([
            'predmet',
            'profesor',
            'studijskiProgram',
            'godinaStudija',
            'semestar',
            'oblikNastave'
        ]);

        if ($request->skolska_godina_id) {
            $query->where('skolska_godina_id', $request->skolska_godina_id);
        } else {
            $query->aktivan();
        }

        if ($request->studijski_program_id) {
            $query->where('studijski_program_id', $request->studijski_program_id);
        }

        if ($request->semestar_id) {
            $query->where('semestar_id', $request->semestar_id);
        }

        $raspored = $query->orderBy('dan')->orderBy('vreme_od')->get();

        $studijskiProgrami = StudijskiProgram::all();
        $semestri = Semestar::all();
        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();

        return view('raspored.index', compact('raspored', 'studijskiProgrami', 'semestri', 'skolskeGodine'));
    }

    public function create()
    {
        $predmeti = Predmet::all();
        $profesori = Profesor::all();
        $studijskiProgrami = StudijskiProgram::all();
        $godineStudija = GodinaStudija::all();
        $semestri = Semestar::all();
        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();
        $obliciNastave = OblikNastave::all();

        return view('raspored.create', compact(
            'predmeti', 'profesori', 'studijskiProgrami', 
            'godineStudija', 'semestri', 'skolskeGodine', 'obliciNastave'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'predmet_id' => 'required|exists:predmet,id',
            'profesor_id' => 'required|exists:profesor,id',
            'studijski_program_id' => 'required|exists:studijski_program,id',
            'godina_studija_id' => 'required|exists:godina_studija,id',
            'semestar_id' => 'required|exists:semestar,id',
            'skolska_godina_id' => 'required|exists:skolska_god_upisa,id',
            'oblik_nastave_id' => 'required|exists:oblik_nastave,id',
            'dan' => 'required|integer|min:1|max:7',
            'vreme_od' => 'required',
            'vreme_do' => 'required|after:vreme_od',
            'prostorija' => 'nullable|string|max:50',
            'grupa' => 'nullable|string|max:50',
        ]);

        Raspored::create($request->all());

        return redirect()->route('raspored.index')->with('success', 'Распоред креиран');
    }

    public function edit(Raspored $raspored)
    {
        $predmeti = Predmet::all();
        $profesori = Profesor::all();
        $studijskiProgrami = StudijskiProgram::all();
        $godineStudija = GodinaStudija::all();
        $semestri = Semestar::all();
        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();
        $obliciNastave = OblikNastave::all();

        return view('raspored.edit', compact(
            'raspored', 'predmeti', 'profesori', 'studijskiProgrami', 
            'godineStudija', 'semestri', 'skolskeGodine', 'obliciNastave'
        ));
    }

    public function update(Request $request, Raspored $raspored)
    {
        $request->validate([
            'predmet_id' => 'required|exists:predmet,id',
            'profesor_id' => 'required|exists:profesor,id',
            'studijski_program_id' => 'required|exists:studijski_program,id',
            'godina_studija_id' => 'required|exists:godina_studija,id',
            'semestar_id' => 'required|exists:semestar,id',
            'skolska_godina_id' => 'required|exists:skolska_god_upisa,id',
            'oblik_nastave_id' => 'required|exists:oblik_nastave,id',
            'dan' => 'required|integer|min:1|max:7',
            'vreme_od' => 'required',
            'vreme_do' => 'required|after:vreme_od',
            'prostorija' => 'nullable|string|max:50',
            'grupa' => 'nullable|string|max:50',
        ]);

        $raspored->update($request->all());

        return redirect()->route('raspored.index')->with('success', 'Распоред ажуриран');
    }

    public function destroy(Raspored $raspored)
    {
        $raspored->delete();
        return redirect()->route('raspored.index')->with('success', 'Распоред обрисан');
    }

    public function pregled(Request $request)
    {
        $query = Raspored::with([
            'predmet',
            'profesor',
            'studijskiProgram',
            'godinaStudija',
            'semestar',
            'oblikNastave'
        ]);

        if ($request->skolska_godina_id) {
            $query->where('skolska_godina_id', $request->skolska_godina_id);
        } else {
            $query->aktivan();
        }

        $raspored = $query->orderBy('dan')->orderBy('vreme_od')->get();

        $dani = [
            1 => 'Понедељак',
            2 => 'Уторак',
            3 => 'Среда',
            4 => 'Четвртак',
            5 => 'Петак',
            6 => 'Субота',
            7 => 'Недеља',
        ];

        $rasporedPoDanima = [];
        foreach ($dani as $dan => $naziv) {
            $rasporedPoDanima[$dan] = [
                'naziv' => $naziv,
                'casovi' => $raspored->filter(function ($r) use ($dan) {
                    return $r->dan == $dan;
                })->values()
            ];
        }

        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();

        return view('raspored.pregled', compact('rasporedPoDanima', 'skolskeGodine'));
    }
}
