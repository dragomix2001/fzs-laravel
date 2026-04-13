<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bodovanje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class BodovanjeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $bodovanje = Bodovanje::all();

        return view('sifarnici.bodovanje', compact('bodovanje'));
    }

    public function unos(Request $request)
    {
        $bodovanje = new Bodovanje;

        $bodovanje->opisnaOcena = $request->opisnaOcena;
        $bodovanje->poeniMin = $request->poeniMin;
        $bodovanje->poeniMax = $request->poeniMax;
        $bodovanje->ocena = $request->ocena;
        $bodovanje->indikatorAktivan = 1;

        $bodovanje->save();

        return Redirect::to('/bodovanje');
    }

    public function edit(Bodovanje $bodovanje)
    {
        return view('sifarnici.editBodovanje', compact('bodovanje'));
    }

    public function add()
    {
        return view('sifarnici.addBodovanje');
    }

    public function update(Request $request, Bodovanje $bodovanje)
    {
        $bodovanje->opisnaOcena = $request->opisnaOcena;
        $bodovanje->poeniMin = $request->poeniMin;
        $bodovanje->poeniMax = $request->poeniMax;
        $bodovanje->ocena = $request->ocena;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $bodovanje->indikatorAktivan = 1;
        } else {
            $bodovanje->indikatorAktivan = 0;
        }

        $bodovanje->update();

        return Redirect::to('/bodovanje');
    }

    public function delete(Bodovanje $bodovanje)
    {
        $bodovanje->delete();

        return back();
    }
}
