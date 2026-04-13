<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GodinaStudija;
use App\Models\PrilozenaDokumenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PrilozenaDokumentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $dokument = PrilozenaDokumenta::with('godinaStudija')->get();
        $godinaStudija = GodinaStudija::all();

        return view('sifarnici.prilozenaDokumenta', compact('dokument', 'godinaStudija'));
    }

    public function unos(Request $request)
    {
        $dokument = new PrilozenaDokumenta;

        $dokument->redniBrojDokumenta = $request->redniBrojDokumenta;
        $dokument->naziv = $request->naziv;
        $dokument->skolskaGodina_id = $request->skolskaGodina_id;

        $dokument->save();

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

        $dokument->update();

        return Redirect::to('/prilozenaDokumenta');
    }

    public function delete(PrilozenaDokumenta $dokument)
    {
        $dokument->delete();

        return back();
    }
}
