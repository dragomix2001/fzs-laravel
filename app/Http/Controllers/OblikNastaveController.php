<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OblikNastave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class OblikNastaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $oblikNastave = OblikNastave::all();

        return view('sifarnici.oblikNastave', compact('oblikNastave'));
    }

    public function unos(Request $request)
    {
        $oblikNastave = new OblikNastave;

        $oblikNastave->naziv = $request->naziv;
        $oblikNastave->skrNaziv = $request->skrNaziv;
        $oblikNastave->indikatorAktivan = 1;

        $oblikNastave->save();

        return Redirect::to('/oblikNastave');
    }

    public function edit(OblikNastave $oblikNastave)
    {
        return view('sifarnici.editOblikNastave', compact('oblikNastave'));
    }

    public function add()
    {
        return view('sifarnici.addOblikNastave');
    }

    public function update(Request $request, OblikNastave $oblikNastave)
    {
        $oblikNastave->naziv = $request->naziv;
        $oblikNastave->skrNaziv = $request->skrNaziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $oblikNastave->indikatorAktivan = 1;
        } else {
            $oblikNastave->indikatorAktivan = 0;
        }

        $oblikNastave->update();

        return Redirect::to('/oblikNastave');
    }

    public function delete(OblikNastave $oblikNastave)
    {
        $oblikNastave->delete();

        return back();
    }
}
