<?php

namespace App\Http\Controllers;

use App\Models\StatusStudiranja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class StatusStudiranjaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $statusStudiranja = StatusStudiranja::all();

        return view('sifarnici.statusStudiranja', compact('statusStudiranja'));
    }

    public function unos(Request $request)
    {
        $statusStudiranja = new StatusStudiranja;

        $statusStudiranja->naziv = $request->naziv;
        $statusStudiranja->indikatorAktivan = 1;

        $statusStudiranja->save();

        return Redirect::to('/statusStudiranja');
    }

    public function edit(StatusStudiranja $statusStudiranja)
    {
        return view('sifarnici.editStatusStudiranja', compact('statusStudiranja'));
    }

    public function add()
    {
        return view('sifarnici.addStatusStudiranja');
    }

    public function update(Request $request, StatusStudiranja $statusStudiranja)
    {
        $statusStudiranja->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $statusStudiranja->indikatorAktivan = 1;
        } else {
            $statusStudiranja->indikatorAktivan = 0;
        }

        $statusStudiranja->update();

        return Redirect::to('/statusStudiranja');
    }

    public function delete(StatusStudiranja $statusStudiranja)
    {
        $statusStudiranja->delete();

        return back();
    }
}
