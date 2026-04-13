<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StatusProfesora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class StatusProfesoraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $status = StatusProfesora::all();

        return view('sifarnici.statusProfesora', compact('status'));
    }

    public function unos(Request $request)
    {
        $status = new StatusProfesora;

        $status->naziv = $request->naziv;
        $status->indikatorAktivan = 1;

        $status->save();

        return Redirect::to('/statusProfesora');
    }

    public function edit(StatusProfesora $status)
    {
        return view('sifarnici.editStatusProfesora', compact('status'));
    }

    public function add()
    {
        return view('sifarnici.addStatusProfesora');
    }

    public function update(Request $request, StatusProfesora $status)
    {
        $status->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $status->indikatorAktivan = 1;
        } else {
            $status->indikatorAktivan = 0;
        }

        $status->update();

        return Redirect::to('/statusProfesora');
    }

    public function delete(StatusProfesora $status)
    {
        $status->delete();

        return back();
    }
}
