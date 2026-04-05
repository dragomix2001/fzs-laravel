<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SrednjeSkoleFakulteti;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class SrednjeSkoleFakultetiController extends Controller
{
    public function index()
    {
        try {
            $srednjeSkoleFakulteti = SrednjeSkoleFakulteti::all();

            return view('sifarnici.srednjeSkoleFakulteti', compact('srednjeSkoleFakulteti'));
        } catch (QueryException $e) {
            Log::error('Database error in SrednjeSkoleFakultetiController index: '.$e->getMessage(), [
                'method' => 'index',
                'exception' => $e,
            ]);

            return back()->with('error', 'Дошло је до непредвиђене грешке приликом учитавања податка.');
        }
    }

    public function unos(Request $request)
    {
        try {
            $srednjeSkoleFakulteti = new SrednjeSkoleFakulteti;

            $srednjeSkoleFakulteti->naziv = $request->naziv;
            $srednjeSkoleFakulteti->indSkoleFakulteta = $request->indSkoleFakulteta;

            $srednjeSkoleFakulteti->save();

            return back();
        } catch (QueryException $e) {
            Log::error('Database error in SrednjeSkoleFakultetiController unos: '.$e->getMessage(), [
                'method' => 'unos',
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке приликом рада са базом.');
        }
    }

    public function edit(SrednjeSkoleFakulteti $srednjeSkoleFakulteti)
    {
        return view('sifarnici.editSrednjeSkoleFakulteti', compact('srednjeSkoleFakulteti'));
    }

    public function update(Request $request, SrednjeSkoleFakulteti $srednjeSkoleFakulteti)
    {
        try {
            $srednjeSkoleFakulteti->naziv = $request->naziv;
            $srednjeSkoleFakulteti->indSkoleFakulteta = $request->indSkoleFakulteta;

            $srednjeSkoleFakulteti->update();

            return Redirect::to('/srednjeSkoleFakulteti');
        } catch (QueryException $e) {
            Log::error('Database error in SrednjeSkoleFakultetiController update: '.$e->getMessage(), [
                'method' => 'update',
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке приликом рада са базом.');
        }
    }

    public function delete(SrednjeSkoleFakulteti $srednjeSkoleFakulteti)
    {
        try {
            $srednjeSkoleFakulteti->delete();

            return back();
        } catch (QueryException $e) {
            Log::error('Database error in SrednjeSkoleFakultetiController delete: '.$e->getMessage(), [
                'method' => 'delete',
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке приликом рада са базом.');
        }
    }
}
