<?php

namespace App\Http\Controllers;

use App\Models\IspitniRok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class IspitniRokController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $ispitniRok = IspitniRok::all();

        return view('sifarnici.ispitniRok', compact('ispitniRok'));
    }

    public function unos(Request $request)
    {
        $ispitniRok = new IspitniRok;

        $ispitniRok->naziv = $request->naziv;
        $ispitniRok->indikatorAktivan = 1;

        $ispitniRok->save();

        return Redirect::to('/ispitniRok');
    }

    public function edit(IspitniRok $ispitniRok)
    {
        return view('sifarnici.editIspitniRok', compact('ispitniRok'));
    }

    public function add()
    {
        return view('sifarnici.addIspitniRok');
    }

    public function update(Request $request, IspitniRok $ispitniRok)
    {
        $ispitniRok->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on') {
            $ispitniRok->indikatorAktivan = 1;
        } else {
            $ispitniRok->indikatorAktivan = 0;
        }

        $ispitniRok->update();

        return Redirect::to('/ispitniRok');
    }

    public function delete(IspitniRok $ispitniRok)
    {
        $ispitniRok->delete();

        return back();
    }
}
