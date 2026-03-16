<?php

namespace App\Http\Controllers;

use App\Exports\KandidatiExport;
use App\Imports\KandidatiImport;
use App\Models\Kandidat;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportController extends Controller
{
    public function index()
    {
        return view('import-export.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new KandidatiImport, $request->file('file'));

            return back()->with('success', 'Успешно uvezено');
        } catch (\Exception $e) {
            return back()->with('error', 'Грешка при увозу: '.$e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');

        $filename = 'kandidati_'.date('Y-m-d_His');

        return Excel::download(new KandidatiExport, $filename.'.'.$format);
    }

    public function exportStudenti(Request $request)
    {
        $studenti = Kandidat::where('statusUpisa_id', 3)->get();

        $filename = 'studenti_'.date('Y-m-d_His');

        return Excel::download(new \App\Exports\StudentiExport($studenti), $filename.'.xlsx');
    }

    public function exportPolozeniIspiti(Request $request)
    {
        $filename = 'polozeni_ispiti_'.date('Y-m-d_His');

        return Excel::download(new \App\Exports\PolozeniIspitiExport, $filename.'.xlsx');
    }
}
