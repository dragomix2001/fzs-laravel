<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StatusGodine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class StatusKandidataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $status = StatusGodine::all();

        return view('sifarnici.statusKandidata', compact('status'));
    }

    public function unos(Request $request)
    {
        $status = new StatusGodine;

        $status->naziv = $request->naziv;
        $status->indikatorAktivan = 1;

        $status->save();

        return Redirect::to('/statusKandidata');
    }

    public function edit(StatusGodine $status)
    {
        return view('sifarnici.editStatusKandidata', compact('status'));
    }

    public function add()
    {
        return view('sifarnici.addStatusKandidata');
    }

    public function update(Request $request, StatusGodine $status)
    {
        $status->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $status->indikatorAktivan = 1;
        } else {
            $status->indikatorAktivan = 0;
        }

        $status->update();

        return Redirect::to('/statusKandidata');
    }

    public function delete(StatusGodine $status)
    {
        $status->delete();

        return back();
    }
}
