<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\KrsnaSlava;
use Illuminate\Support\Facades\Redirect;

use App\Http\Requests;

class KrsnaSlavaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $krsnaSlava = KrsnaSlava::all();
        } catch (\Illuminate\Database\QueryException $e) {
            dd('Дошло је до непредвиђене грешке.' . $e->getMessage());
        }

        return view('sifarnici.krsnaSlava', compact('krsnaSlava'));
    }

    public function unos(Request $request)
    {
        $krsnaSlava = new KrsnaSlava();
        $krsnaSlava->naziv = $request->naziv;
        $krsnaSlava->datumSlave = $request->datumSlave;
        $krsnaSlava->indikatorAktivan = 1;

        try {
            $krsnaSlava->save();
        } catch (\Illuminate\Database\QueryException $e) {
            dd('Дошло је до непредвиђене грешке.' . $e->getMessage());
        }

        return Redirect::to('/krsnaSlava');
    }

    public function edit(KrsnaSlava $krsnaSlava)
    {
        return view('sifarnici.editKrsnaSlava', compact('krsnaSlava'));
    }

    public function add()
    {
        return view('sifarnici.addKrsnaSlava');
    }

    public function update(Request $request, KrsnaSlava $krsnaSlava)
    {
        $krsnaSlava->naziv = $request->naziv;
        $krsnaSlava->datumSlave = $request->datumSlave;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $krsnaSlava->indikatorAktivan = 1;
        } else {
            $krsnaSlava->indikatorAktivan = 0;
        }

        try {
            $krsnaSlava->update();
        } catch (\Illuminate\Database\QueryException $e) {
            dd('Дошло је до непредвиђене грешке.' . $e->getMessage());
        }

        return Redirect::to('/krsnaSlava');
    }

    public function delete(KrsnaSlava $krsnaSlava)
    {
        try {
            $krsnaSlava->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            dd('Дошло је до непредвиђене грешке.' . $e->getMessage());
        }

        return back();
    }
}
