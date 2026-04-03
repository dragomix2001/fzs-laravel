<?php

namespace App\Http\Controllers;

use App\StatusProfesora;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class StatusProfesoraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $status = StatusProfesora::all();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.statusProfesora', compact('status'));
    }

    public function unos(Request $request)
    {
        $status = new StatusProfesora;

        $status->naziv = $request->naziv;
        $status->indikatorAktivan = 1;

        try {
            $status->save();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/statusProfesora');
    }

    public function edit(StatusProfesora $status)
    {
        return view('sifarnici.editstatusProfesora', compact('status'));
    }

    public function add()
    {
        return view('sifarnici.addstatusProfesora');
    }

    public function update(Request $request, StatusProfesora $status)
    {
        $status->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $status->indikatorAktivan = 1;
        } else {
            $status->indikatorAktivan = 0;
        }

        try {
            $status->update();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/statusProfesora');
    }

    public function delete(StatusProfesora $status)
    {
        try {
            $status->delete();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
