<?php

namespace App\Http\Controllers;

use App\Models\StatusIspita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class StatusIspitaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $status = StatusIspita::all();

        return view('sifarnici.statusIspita', compact('status'));
    }

    public function unos(Request $request)
    {
        $status = new StatusIspita;

        $status->naziv = $request->naziv;
        $status->indikatorAktivan = 1;

        $status->save();

        return Redirect::to('/statusIspita');
    }

    public function edit(StatusIspita $status)
    {
        return view('sifarnici.editStatusIspita', compact('status'));
    }

    public function add()
    {
        return view('sifarnici.addStatusIspita');
    }

    public function update(Request $request, StatusIspita $status)
    {
        $status->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $status->indikatorAktivan = 1;
        } else {
            $status->indikatorAktivan = 0;
        }

        $status->update();

        return Redirect::to('/statusIspita');
    }

    public function delete(StatusIspita $status)
    {
        $status->delete();

        return back();
    }
}
