<?php

namespace App\Http\Controllers;

use App\Models\KrsnaSlava;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class KrsnaSlavaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $krsnaSlava = KrsnaSlava::all();

        return view('sifarnici.krsnaSlava', compact('krsnaSlava'));
    }

    public function unos(Request $request)
    {
        $krsnaSlava = new KrsnaSlava;
        $krsnaSlava->naziv = $request->naziv;
        $krsnaSlava->datumSlave = $request->datumSlave;
        $krsnaSlava->indikatorAktivan = 1;

        $krsnaSlava->save();

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

        $krsnaSlava->update();

        return Redirect::to('/krsnaSlava');
    }

    public function delete(KrsnaSlava $krsnaSlava)
    {
        $krsnaSlava->delete();

        return back();
    }
}
