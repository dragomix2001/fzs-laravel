<?php

namespace App\Http\Controllers;

use App\Models\Semestar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SemestarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $semestar = Semestar::all();

        return view('sifarnici.semestar', compact('semestar'));
    }

    public function unos(Request $request)
    {
        $semestar = new Semestar;

        $semestar->naziv = $request->naziv;
        $semestar->nazivRimski = $request->nazivRimski;
        $semestar->nazivBrojcano = $request->nazivBrojcano;
        $semestar->indikatorAktivan = 1;

        $semestar->save();

        return Redirect::to('/semestar');
    }

    public function edit(Semestar $semestar)
    {
        return view('sifarnici.editSemestar', compact('semestar'));
    }

    public function add()
    {
        return view('sifarnici.addSemestar');
    }

    public function update(Request $request, Semestar $semestar)
    {
        $semestar->naziv = $request->naziv;
        $semestar->nazivRimski = $request->nazivRimski;
        $semestar->nazivBrojcano = $request->nazivBrojcano;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $semestar->indikatorAktivan = 1;
        } else {
            $semestar->indikatorAktivan = 0;
        }

        $semestar->update();

        return Redirect::to('/semestar');
    }

    public function delete(Semestar $semestar)
    {
        $semestar->delete();

        return back();
    }
}
