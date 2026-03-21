<?php

namespace App\Http\Controllers;

use App\Services\PredictionService;
use App\Models\Kandidat;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    protected $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->middleware('auth');
        $this->predictionService = $predictionService;
    }

    public function index()
    {
        $students = Kandidat::orderBy('prezimeKandidata')->orderBy('imeKandidata')->get();
        
        return view('prediction.index', compact('students'));
    }

    public function studentPrediction($id)
    {
        $prediction = $this->predictionService->predictStudentSuccess($id);
        
        if (isset($prediction['error'])) {
            return back()->with('error', $prediction['error']);
        }
        
        return view('prediction.student', compact('prediction'));
    }

    public function classStatistics()
    {
        $statistics = $this->predictionService->getClassStatistics();
        
        if (isset($statistics['error'])) {
            return back()->with('error', $statistics['error']);
        }
        
        return view('prediction.statistics', compact('statistics'));
    }

    public function apiStudentPrediction($id)
    {
        return response()->json($this->predictionService->predictStudentSuccess($id));
    }

    public function apiClassStatistics()
    {
        return response()->json($this->predictionService->getClassStatistics());
    }
}
