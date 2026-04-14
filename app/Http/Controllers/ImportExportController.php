<?php

namespace App\Http\Controllers;

use App\Exports\KandidatiExport;
use App\Exports\PolozeniIspitiExport;
use App\Exports\StudentiExport;
use App\Http\Requests\ImportFileRequest;
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

    public function import(ImportFileRequest $request)
    {
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

        return Excel::download(new StudentiExport($studenti), $filename.'.xlsx');
    }

    public function exportPolozeniIspiti(Request $request)
    {
        $filename = 'polozeni_ispiti_'.date('Y-m-d_His');

        return Excel::download(new PolozeniIspitiExport, $filename.'.xlsx');
    }
}
