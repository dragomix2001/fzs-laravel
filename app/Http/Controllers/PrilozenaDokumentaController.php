<?php

namespace App\Http\Controllers;

use App\GodinaStudija;
use App\PrilozenaDokumenta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class PrilozenaDokumentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $dokument = PrilozenaDokumenta::all();
            $godinaStudija = GodinaStudija::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.prilozenaDokumenta', compact('dokument', 'godinaStudija'));
    }

    public function unos(Request $request)
    {
        $dokument = new PrilozenaDokumenta;

        $dokument->redniBrojDokumenta = $request->redniBrojDokumenta;
        $dokument->naziv = $request->naziv;
        $dokument->skolskaGodina_id = $request->skolskaGodina_id;

        try {
            $dokument->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/prilozenaDokumenta');
    }

    public function edit(PrilozenaDokumenta $dokument)
    {
        $godinaStudija = GodinaStudija::all();

        return view('sifarnici.editPrilozenaDokumenta', compact('dokument', 'godinaStudija'));
    }

    public function add()
    {
        $godinaStudija = GodinaStudija::all();

        return view('sifarnici.addPrilozenaDokumenta', compact('godinaStudija'));
    }

    public function update(Request $request, PrilozenaDokumenta $dokument)
    {
        $dokument->redniBrojDokumenta = $request->redniBrojDokumenta;
        $dokument->naziv = $request->naziv;
        $dokument->skolskaGodina_id = $request->skolskaGodina_id;

        try {
            $dokument->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/prilozenaDokumenta');
    }

    public function delete(PrilozenaDokumenta $dokument)
    {
        try {
            $dokument->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
