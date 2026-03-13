<?php

namespace App\Http\Controllers;

use App\Models\Obavestenje;
use App\Models\Profesor;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ObavestenjeController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request)
    {
        $query = Obavestenje::with('profesor');

        if ($request->tip) {
            $query->where('tip', $request->tip);
        }

        if ($request->samo_aktivna) {
            $query->aktivna();
        }

        $obavestenja = $query->orderBy('datum_objave', 'desc')->get();

        return view('obavestenja.index', compact('obavestenja'));
    }

    public function create()
    {
        $profesori = Profesor::all();
        $tipovi = [
            'opste' => 'Опште',
            'ispit' => 'Испит',
            'raspored' => 'Распоред',
            'upis' => 'Упис',
            'Ocena' => 'Оцена',
            'stipendija' => 'Стипендија',
        ];

        return view('obavestenja.create', compact('profesori', 'tipovi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'naslov' => 'required|string|max:255',
            'sadrzaj' => 'required',
            'tip' => 'required|string|max:50',
            'aktivan' => 'boolean',
            'datum_objave' => 'required',
            'datum_isteka' => 'nullable|after:datum_objave',
            'posalji_email' => 'boolean',
        ]);

        $data = $request->all();
        $data['profesor_id'] = auth()->user()->profesor_id ?? $request->profesor_id;

        $obavestenje = Obavestenje::create($data);

        // Send email notification if requested
        if ($request->boolean('posalji_email')) {
            $this->notificationService->sendObavestenjeToAllStudents(
                $obavestenje->naslov,
                $obavestenje->sadrzaj,
                $obavestenje->tip
            );
        }

        return redirect()->route('obavestenja.index')->with('success', 'Обавештење креирано');
    }

    public function show(Obavestenje $obavestenje)
    {
        return view('obavestenja.show', compact('obavestenje'));
    }

    public function edit(Obavestenje $obavestenje)
    {
        $profesori = Profesor::all();
        $tipovi = [
            'opste' => 'Опште',
            'ispit' => 'Испит',
            'raspored' => 'Распоред',
            'upis' => 'Упис',
            'Ocena' => 'Оцена',
            'stipendija' => 'Стипендија',
        ];

        return view('obavestenja.edit', compact('obavestenje', 'profesori', 'tipovi'));
    }

    public function update(Request $request, Obavestenje $obavestenje)
    {
        $request->validate([
            'naslov' => 'required|string|max:255',
            'sadrzaj' => 'required',
            'tip' => 'required|string|max:50',
            'aktivan' => 'boolean',
            'datum_objave' => 'required',
            'datum_isteka' => 'nullable|after:datum_objave',
        ]);

        $obavestenje->update($request->all());

        return redirect()->route('obavestenja.index')->with('success', 'Обавештење ажурирано');
    }

    public function destroy(Obavestenje $obavestenje)
    {
        $obavestenje->delete();
        return redirect()->route('obavestenja.index')->with('success', 'Обавештење обрисано');
    }

    public function toggleStatus(Obavestenje $obavestenje)
    {
        $obavestenje->update(['aktivan' => !$obavestenje->aktivan]);
        return redirect()->route('obavestenja.index')->with('success', 'Статус промењен');
    }

    public function javna(Request $request)
    {
        $obavestenja = Obavestenje::aktivna()
            ->orderBy('datum_objave', 'desc')
            ->get();

        return view('obavestenja.javna', compact('obavestenja'));
    }

    public function moja(Request $request)
    {
        $user = auth()->user();
        
        $obavestenja = Obavestenje::whereHas('korisnici', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orWhere(function ($query) {
            $query->where('tip', 'opste')->aktivna();
        })->orderBy('datum_objave', 'desc')->get();

        return view('obavestenja.moja', compact('obavestenja'));
    }
}
