<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kandidat;
use App\Models\Sport;
use App\Models\SportskoAngazovanje;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class SportskoAngazovanjeController extends Controller
{
    public function index(Kandidat $kandidat)
    {
        // PrilozenaDokumenta::where('indGodina','2')->get()
        // $sportskoAngazovanje = SportskoAngazovanje::where(['kandidat_id', 1]);
        // $kandidat = Kandidat::all();

        $sport = Sport::all();

        return view('sifarnici.sportskoAngazovanje ', compact('kandidat', 'sport'));
    }

    public function unos(Request $request)
    {
        try {
            $angazovanje = new SportskoAngazovanje;

            $angazovanje->nazivKluba = $request->nazivKluba;
            $angazovanje->odDoGodina = $request->odDoGodina;
            $angazovanje->ukupnoGodina = $request->ukupnoGodina;
            $angazovanje->sport_id = $request->sport_id;
            $angazovanje->kandidat_id = Session::get('id');

            $angazovanje->save();

            return back();
        } catch (QueryException $e) {
            Log::error('Database error in SportskoAngazovanjeController unos: '.$e->getMessage(), [
                'method' => 'unos',
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке приликом рада са базом.');
        }
    }

    public function edit(SportskoAngazovanje $angazovanje)
    {
        $sport = Sport::all();

        return view('sifarnici.editSportskoAngazovanje', compact('angazovanje', 'sport'));
    }

    public function update(Request $request, SportskoAngazovanje $angazovanje)
    {
        try {
            $angazovanje->nazivKluba = $request->nazivKluba;
            $angazovanje->odDoGodina = $request->odDoGodina;
            $angazovanje->ukupnoGodina = $request->ukupnoGodina;
            $angazovanje->sport_id = $request->sport_id;
            $angazovanje->kandidat_id = Session::get('id');
            $id = Session::get('id');

            $angazovanje->update();

            return Redirect::to('/sportskoAngazovanje/'.$id);
        } catch (QueryException $e) {
            Log::error('Database error in SportskoAngazovanjeController update: '.$e->getMessage(), [
                'method' => 'update',
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке приликом рада са базом.');
        }
    }

    public function delete(SportskoAngazovanje $angazovanje)
    {
        try {
            $id = $angazovanje->kandidat_id;
            $kandidat = Kandidat::find($id);
            $sportskoAngazovanje = SportskoAngazovanje::where('kandidat_id', $id)->get();
            $sportovi = Sport::all();

            $angazovanje->delete();

            return redirect("/kandidat/{$id}/sportskoangazovanje")
                ->with('sport', $sportovi)
                ->with('kandidat', $kandidat)
                ->with('sportskoAngazovanje', $sportskoAngazovanje);
        } catch (QueryException $e) {
            Log::error('Database error in SportskoAngazovanjeController delete: '.$e->getMessage(), [
                'method' => 'delete',
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке приликом рада са базом.');
        }
    }

    public function vrati()
    {
        return redirect()->back()->withInput();
    }
}
