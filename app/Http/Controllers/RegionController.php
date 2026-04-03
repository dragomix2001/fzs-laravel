<?php

namespace App\Http\Controllers;

use App\Region;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $region = Region::all();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.region', compact('region'));
    }

    public function unos(Request $request)
    {
        $region = new Region;
        $region->naziv = $request->naziv;

        try {
            $region->save();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/region');
    }

    public function edit(Region $region)
    {
        return view('sifarnici.editRegion', compact('region'));
    }

    public function add()
    {
        return view('sifarnici.addRegion');
    }

    public function update(Request $request, Region $region)
    {
        $region->naziv = $request->naziv;

        try {
            $region->update();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/region');
    }

    public function delete(Region $region)
    {
        try {
            $region->delete();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
