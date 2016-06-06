<?php

namespace App\Http\Controllers;

use App\GodinaStudija;
use App\Kandidat;
use App\KandidatPrilozenaDokumenta;
use App\KrsnaSlava;
use App\Mesto;
use App\OpstiUspeh;
use App\PrilozenaDokumenta;
use App\SkolskaGodUpisa;
use App\Sport;
use App\SportskoAngazovanje;
use App\SrednjeSkoleFakulteti;
use App\StatusStudiranja;
use App\StudijskiProgram;
use App\TipStudija;
use App\UspehSrednjaSkola;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\SportskoAngazovanjeContoller;

use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use Mockery\CountValidator\Exception;

class KandidatController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Session::put('kandidat', 'proba');

        $kandidati = Kandidat::all();

        return view("kandidat.indeks")->with('kandidati', $kandidati);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $mestoRodjenja = Mesto::all();
        $krsnaSlava = KrsnaSlava::all();
        $nazivSkoleFakulteta = SrednjeSkoleFakulteti::all();
        $mestoZavrseneSkoleFakulteta = Mesto::all();
        $opstiUspehSrednjaSkola = OpstiUspeh::all();
        $uspehSrednjaSkola = UspehSrednjaSkola::all();
        $sportskoAngazovanje = SportskoAngazovanje::all();
        $prilozeniDokumentPrvaGodina = PrilozenaDokumenta::all();
        $statusaUpisaKandidata = StatusStudiranja::all();
        $studijskiProgram = StudijskiProgram::all();
        $tipStudija = TipStudija::all();
        $godinaStudija = GodinaStudija::all();
        $skolskeGodineUpisa = SkolskaGodUpisa::all();

        return view("kandidat.create_part_1")
            ->with('mestoRodjenja', $mestoRodjenja)
            ->with('krsnaSlava', $krsnaSlava)
            ->with('nazivSkoleFakulteta', $nazivSkoleFakulteta)
            ->with('mestoZavrseneSkoleFakulteta', $mestoZavrseneSkoleFakulteta)
            ->with('opstiUspehSrednjaSkola', $opstiUspehSrednjaSkola)
            ->with('uspehSrednjaSkola', $uspehSrednjaSkola)
            ->with('sportskoAngazovanje', $sportskoAngazovanje)
            ->with('prilozeniDokumentPrvaGodina', $prilozeniDokumentPrvaGodina)
            ->with('statusaUpisaKandidata', $statusaUpisaKandidata)
            ->with('studijskiProgram', $studijskiProgram)
            ->with('tipStudija', $tipStudija)
            ->with('godinaStudija', $godinaStudija)
            ->with('skolskeGodineUpisa', $skolskeGodineUpisa);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->page == 1) {

//            $messages = [
//                'required' => 'Niste uneli polje -- :attribute.',
//            ];
//
//            $rules = [
//                'ImeKandidata' => 'required',
//            ];
//
//            $this->validate($request,$rules,$messages);

            $kandidat = new Kandidat();
            $kandidat->imeKandidata = $request->ImeKandidata;
            $kandidat->prezimeKandidata = $request->PrezimeKandidata;
            $kandidat->jmbg = $request->JMBG;

            //$dateArray = explode('.', ); date()
            if (date_create_from_format('d.m.Y.', $request->DatumRodjenja)) {
                $kandidat->datumRodjenja = date_create_from_format('d.m.Y.', $request->DatumRodjenja);
            } else {

            }


            $kandidat->mestoRodjenja_id = $request->MestoRodjenja;
            $kandidat->krsnaSlava_id = $request->KrsnaSlava;
            $kandidat->kontaktTelefon = $request->KontaktTelefon;
            $kandidat->adresaStanovanja = $request->AdresaStanovanja;
            $kandidat->email = $request->Email;
            $kandidat->imePrezimeJednogRoditelja = $request->ImePrezimeJednogRoditelja;
            $kandidat->kontaktTelefonRoditelja = $request->KontaktTelefonRoditelja;
            $kandidat->srednjeSkoleFakulteti_id = $request->NazivSkoleFakulteta;
            $kandidat->mestoZavrseneSkoleFakulteta_id = $request->MestoZavrseneSkoleFakulteta;
            $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

            $kandidat->tipStudija_id = $request->TipStudija;
            $kandidat->studijskiProgram_id = $request->StudijskiProgram;
            $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;
            $kandidat->godinaStudija_id = $request->GodinaStudija;

            try {
                $kandidat->save();
            } catch (\Illuminate\Database\QueryException $e) {


                if (strpos($e->getMessage(), 'jmbg_UNIQUE') !== false) {
                    return back()->with('jmbgError', '1')->withInput();
                } else {
                    dd("nesto je poslo po zlu");

                    if (strpos($e->getMessage(), 'jmbg_unique') !== false) {
                        return back()->with('jmbgError', '1')->withInput();
                    } else {
                        dd("nesto je poslo po zlu" . $e->getMessage());

                    }
                }
            }

            //$kandidat->save();

            $insertedId = $kandidat->id;

            $nazivSkoleFakulteta = SrednjeSkoleFakulteti::all();
            $mestoZavrseneSkoleFakulteta = Mesto::all();
            $opstiUspehSrednjaSkola = OpstiUspeh::all();
            $uspehSrednjaSkola = UspehSrednjaSkola::all();
            $prilozeniDokumentPrvaGodina = PrilozenaDokumenta::all();
            $statusaUpisaKandidata = StatusStudiranja::all();
            $studijskiProgram = StudijskiProgram::all();
            $tipStudija = TipStudija::all();
            $godinaStudija = GodinaStudija::all();
            $skolskeGodineUpisa = SkolskaGodUpisa::all();
            $sport = Sport::all();
            $dokumentiPrvaGodina = PrilozenaDokumenta::where('indGodina', '1')->get();
            $dokumentiOstaleGodine = PrilozenaDokumenta::where('indGodina', '2')->get();

            return view("kandidat.create_part_2")
                //->with('mestoRodjenja', $mestoRodjenja)
                //->with('krsnaSlava', $krsnaSlava)
                ->with('nazivSkoleFakulteta', $nazivSkoleFakulteta)
                ->with('mestoZavrseneSkoleFakulteta', $mestoZavrseneSkoleFakulteta)
                ->with('opstiUspehSrednjaSkola', $opstiUspehSrednjaSkola)
                ->with('uspehSrednjaSkola', $uspehSrednjaSkola)
                ->with('prilozeniDokumentPrvaGodina', $prilozeniDokumentPrvaGodina)
                ->with('statusaUpisaKandidata', $statusaUpisaKandidata)
                ->with('studijskiProgram', $studijskiProgram)
                ->with('tipStudija', $tipStudija)
                ->with('godinaStudija', $godinaStudija)
                ->with('skolskeGodineUpisa', $skolskeGodineUpisa)
                ->with('insertedId', $insertedId)
                ->with('sport', $sport)
                ->with('dokumentiPrvaGodina', $dokumentiPrvaGodina)
                ->with('dokumentiOstaleGodine', $dokumentiOstaleGodine);

        } else if ($request->page == 2) {

            $kandidat = Kandidat::find($request->insertedId);

            $skola_id = $kandidat->srednjeSkoleFakulteti_id;

            try {
                $prviRazred = new UspehSrednjaSkola();
                $prviRazred->kandidat_id = $request->insertedId;
                $prviRazred->SrednjeSkoleFakulteti_id = $skola_id;
                $prviRazred->opstiUspeh_id = $request->prviRazred;
                $prviRazred->srednja_ocena = $request->SrednjaOcena1;
                $prviRazred->RedniBrojRazreda = 1;
                $prviRazred->save();

            } catch (\Exception $e) {

            }catch (\Exception $e){

                return $e;
            }

            $drugiRazred = new UspehSrednjaSkola();
            $drugiRazred->kandidat_id = $request->insertedId;
            $drugiRazred->SrednjeSkoleFakulteti_id = $skola_id;
            $drugiRazred->opstiUspeh_id = $request->drugiRazred;
            $drugiRazred->srednja_ocena = $request->SrednjaOcena2;
            $drugiRazred->RedniBrojRazreda = 2;
            $drugiRazred->save();

            $treciRazred = new UspehSrednjaSkola();
            $treciRazred->kandidat_id = $request->insertedId;
            $treciRazred->SrednjeSkoleFakulteti_id = $skola_id;
            $treciRazred->opstiUspeh_id = $request->treciRazred;
            $treciRazred->srednja_ocena = $request->SrednjaOcena3;
            $treciRazred->RedniBrojRazreda = 3;
            $treciRazred->save();

            $cetvrtiRazred = new UspehSrednjaSkola();
            $cetvrtiRazred->kandidat_id = $request->insertedId;
            $cetvrtiRazred->SrednjeSkoleFakulteti_id = $skola_id;
            $cetvrtiRazred->opstiUspeh_id = $request->cetvrtiRazred;
            $cetvrtiRazred->srednja_ocena = $request->SrednjaOcena4;


            $cetvrtiRazred->RedniBrojRazreda = 4;
            $cetvrtiRazred->save();

            $kandidat->opstiUspehSrednjaSkola_id = $request->OpstiUspehSrednjaSkola;
            $kandidat->srednjaOcenaSrednjaSkola = $request->SrednjaOcenaSrednjaSkola;

            if ($request->sport1 != 0) {
                $sport1 = new SportskoAngazovanje();
                $sport1->sport_id = $request->sport1;
                $sport1->kandidat_id = $request->insertedId;
                $sport1->nazivKluba = $request->klub1;
                $sport1->odDoGodina = $request->uzrast1;
                $sport1->ukupnoGodina = $request->godine1;
                $sport1->save();
            }

            if ($request->sport2 != 0) {
                $sport2 = new SportskoAngazovanje();
                $sport2->sport_id = $request->sport2;
                $sport2->kandidat_id = $request->insertedId;
                $sport2->nazivKluba = $request->klub2;
                $sport2->odDoGodina = $request->uzrast2;
                $sport2->ukupnoGodina = $request->godine2;
                $sport2->save();
            }

            if ($request->sport3 != 0) {
                $sport3 = new SportskoAngazovanje();
                $sport3->sport_id = $request->sport3;
                $sport3->kandidat_id = $request->insertedId;
                $sport3->nazivKluba = $request->klub3;
                $sport3->odDoGodina = $request->uzrast3;
                $sport3->ukupnoGodina = $request->godine3;
                $sport3->save();
            }

            $kandidat->visina = $request->VisinaKandidata;
            $kandidat->telesnaTezina = $request->TelesnaTezinaKandidata;


            $dokumenta = PrilozenaDokumenta::all();

            foreach ($dokumenta as $dokument) {
                if ($request->has(str_replace(' ', '_', $dokument->naziv))) {
                    $prilozenDokument = new KandidatPrilozenaDokumenta();
                    $prilozenDokument->prilozenaDokumenta_id = $dokument->id;
                    $prilozenDokument->kandidat_id = $request->insertedId;
                    $prilozenDokument->indikatorAktivan = 1;
                    $prilozenDokument->save();
                }
            }

            //$kandidat->statusUpisa_id = $request->StatusaUpisaKandidata;
            $kandidat->brojBodovaTest = $request->BrojBodovaTest;
            $kandidat->brojBodovaSkola = $request->BrojBodovaSkola;
            $kandidat->upisniRok = $request->UpisniRok;
            //$kandidat->indikatorAktivan = $request->IndikatorAktivan;

            $kandidat->save();

            return redirect('/kandidat/');

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $kandidat = Kandidat::find($id)->toArray();

        return view('kandidat.details')->with('kandidat', $kandidat);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Session::get('kandidat') == 'proba'){ //testiranje sesija - neuspelo
            $kandidat = Kandidat::find($id);
        }
        else
        {
            //$kandidat = Kandidat::find(2);
            //$kandidat = Session::get('kandidat2');
        }


        $mestoRodjenja = Mesto::all();
        $krsnaSlava = KrsnaSlava::all();
        $nazivSkoleFakulteta = SrednjeSkoleFakulteti::all();
        $mestoZavrseneSkoleFakulteta = Mesto::all();
        $opstiUspehSrednjaSkola = OpstiUspeh::all();
        $uspehSrednjaSkola = UspehSrednjaSkola::all();
        $prilozeniDokumentPrvaGodina = PrilozenaDokumenta::all();
        $statusaUpisaKandidata = StatusStudiranja::all();
        $studijskiProgram = StudijskiProgram::all();
        $tipStudija = TipStudija::all();
        $godinaStudija = GodinaStudija::all();
        $skolskeGodineUpisa = SkolskaGodUpisa::all();
        $sport = Sport::all();
        $dokumentiPrvaGodina = PrilozenaDokumenta::where('indGodina', '1')->get();
        $dokumentiOstaleGodine = PrilozenaDokumenta::where('indGodina', '2')->get();

        $prilozenaDokumenta = KandidatPrilozenaDokumenta::where('kandidat_id', $id)->lists('prilozenaDokumenta_id')->toArray();


        try {
            $prviRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 1])->firstOrFail();
        }catch (ModelNotFoundException $e){
            $prviRazred = new UspehSrednjaSkola();
            $prviRazred->kandidat_id = 0;
            $prviRazred->SrednjeSkoleFakulteti_id = 1;
            $prviRazred->opstiUspeh_id = 1;
            $prviRazred->srednja_ocena = 0;
            $prviRazred->RedniBrojRazreda = 1;
        }

        try {
            $drugiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 2])->firstOrFail();
        }catch (ModelNotFoundException $e){
            $drugiRazred = new UspehSrednjaSkola();
            $drugiRazred->kandidat_id = 0;
            $drugiRazred->SrednjeSkoleFakulteti_id = 1;
            $drugiRazred->opstiUspeh_id = 1;
            $drugiRazred->srednja_ocena = 0;
            $drugiRazred->RedniBrojRazreda = 1;
        }

        try {
            $treciRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 3])->firstOrFail();
        }catch (ModelNotFoundException $e){
            $treciRazred = new UspehSrednjaSkola();
            $treciRazred->kandidat_id = 0;
            $treciRazred->SrednjeSkoleFakulteti_id = 1;
            $treciRazred->opstiUspeh_id = 1;
            $treciRazred->srednja_ocena = 0;
            $treciRazred->RedniBrojRazreda = 1;
        }

        try {
            $cetvrtiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 4])->firstOrFail();
        }catch (ModelNotFoundException $e){
            $cetvrtiRazred = new UspehSrednjaSkola();
            $cetvrtiRazred->kandidat_id = 0;
            $cetvrtiRazred->SrednjeSkoleFakulteti_id = 1;
            $cetvrtiRazred->opstiUspeh_id = 1;
            $cetvrtiRazred->srednja_ocena = 0;
            $cetvrtiRazred->RedniBrojRazreda = 1;
        }




        $sportskoAngazovanjeKandidata = SportskoAngazovanje::where('kandidat_id', $id)->get();


        return view('kandidat.update')->with('kandidat', $kandidat)
            ->with('mestoRodjenja', $mestoRodjenja)
            ->with('krsnaSlava', $krsnaSlava)
            ->with('nazivSkoleFakulteta', $nazivSkoleFakulteta)
            ->with('mestoZavrseneSkoleFakulteta', $mestoZavrseneSkoleFakulteta)
            ->with('opstiUspehSrednjaSkola', $opstiUspehSrednjaSkola)
            ->with('uspehSrednjaSkola', $uspehSrednjaSkola)
            ->with('prilozeniDokumentPrvaGodina', $prilozeniDokumentPrvaGodina)
            ->with('statusaUpisaKandidata', $statusaUpisaKandidata)
            ->with('studijskiProgram', $studijskiProgram)
            ->with('tipStudija', $tipStudija)
            ->with('godinaStudija', $godinaStudija)
            ->with('skolskeGodineUpisa', $skolskeGodineUpisa)
            ->with('sport', $sport)
            ->with('dokumentiPrvaGodina', $dokumentiPrvaGodina)
            ->with('dokumentiOstaleGodine', $dokumentiOstaleGodine)
            ->with('prilozenaDokumenta', $prilozenaDokumenta)
            ->with('prviRazred', $prviRazred)
            ->with('drugiRazred', $drugiRazred)
            ->with('treciRazred', $treciRazred)
            ->with('cetvrtiRazred', $cetvrtiRazred)
            ->with('sportskoAngazovanjeKandidata', $sportskoAngazovanjeKandidata);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $kandidat = Kandidat::find($id);

        Session::put('id', $kandidat->id);

        //$kandidat2 = $request;

        if ( $request->dalje == 1) {


            $kandidat->imeKandidata = $request->ImeKandidata;
            $kandidat->prezimeKandidata = $request->PrezimeKandidata;
            $kandidat->jmbg = $request->JMBG;

            $kandidat->datumRodjenja = date_create_from_format('d.m.Y.', $request->DatumRodjenja);

            $kandidat->mestoRodjenja_id = $request->MestoRodjenja;
            $kandidat->krsnaSlava_id = $request->KrsnaSlava;
            $kandidat->kontaktTelefon = $request->KontaktTelefon;
            $kandidat->adresaStanovanja = $request->AdresaStanovanja;
            $kandidat->email = $request->Email;
            $kandidat->imePrezimeJednogRoditelja = $request->ImePrezimeJednogRoditelja;
            $kandidat->kontaktTelefonRoditelja = $request->KontaktTelefonRoditelja;
            $kandidat->srednjeSkoleFakulteti_id = $request->NazivSkoleFakulteta;
            $kandidat->mestoZavrseneSkoleFakulteta_id = $request->MestoZavrseneSkoleFakulteta;
            $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

            $kandidat->tipStudija_id = $request->TipStudija;
            $kandidat->studijskiProgram_id = $request->StudijskiProgram;
            $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;
            $kandidat->godinaStudija_id = $request->GodinaStudija;

            $skola_id = $kandidat->srednjeSkoleFakulteti_id;

            $prviRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 1])->first();
            $prviRazred->opstiUspeh_id = $request->prviRazred;
            $prviRazred->srednja_ocena = $request->SrednjaOcena1;
            $prviRazred->save();

            $drugiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 2])->first();
            $drugiRazred->opstiUspeh_id = $request->drugiRazred;
            $drugiRazred->srednja_ocena = $request->SrednjaOcena2;
            $drugiRazred->save();

            $treciRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 3])->first();
            $treciRazred->opstiUspeh_id = $request->treciRazred;
            $treciRazred->srednja_ocena = $request->SrednjaOcena3;
            $treciRazred->save();

            $cetvrtiRazred = UspehSrednjaSkola::where(['kandidat_id' => $id, 'RedniBrojRazreda' => 4])->first();
            $cetvrtiRazred->opstiUspeh_id = $request->cetvrtiRazred;
            $cetvrtiRazred->srednja_ocena = $request->SrednjaOcena4;
            $cetvrtiRazred->save();

            $kandidat->opstiUspehSrednjaSkola_id = $request->OpstiUspehSrednjaSkola;
            $kandidat->srednjaOcenaSrednjaSkola = $request->SrednjaOcenaSrednjaSkola;

//        $sport1 = new SportskoAngazovanje();
//        $sport1->sport_id = $request->sport1;
//        $sport1->kandidat_id = $request->insertedId;
//        $sport1->nazivKluba = $request->klub1;
//        $sport1->odDoGodina = $request->uzrast1;
//        $sport1->ukupnoGodina = $request->godine1;
//        $sport1->save();
//
//        $sport2 = new SportskoAngazovanje();
//        $sport2->sport_id = $request->sport2;
//        $sport2->kandidat_id = $request->insertedId;
//        $sport2->nazivKluba = $request->klub2;
//        $sport2->odDoGodina = $request->uzrast2;
//        $sport2->ukupnoGodina = $request->godine2;
//        $sport2->save();
//
//        $sport3 = new SportskoAngazovanje();
//        $sport3->sport_id = $request->sport3;
//        $sport3->kandidat_id = $request->insertedId;
//        $sport3->nazivKluba = $request->klub3;
//        $sport3->odDoGodina = $request->uzrast3;
//        $sport3->ukupnoGodina = $request->godine3;
//        $sport3->save();

            $kandidat->visina = $request->VisinaKandidata;
            $kandidat->telesnaTezina = $request->TelesnaTezinaKandidata;


            $dokumenta = PrilozenaDokumenta::all();

            foreach ($dokumenta as $dokument) {
                if ($request->has(str_replace(' ', '_', $dokument->naziv))) {
                    $prilozenDokument = new KandidatPrilozenaDokumenta();
                    $prilozenDokument->prilozenaDokumenta_id = $dokument->id;
                    $prilozenDokument->kandidat_id = $id;
                    $prilozenDokument->indikatorAktivan = 1;
                    $prilozenDokument->save();
                } else {
                    $delete = KandidatPrilozenaDokumenta::where(['prilozenaDokumenta_id' => $dokument->id, 'kandidat_id' => $id])
                        ->first();
                    if ($delete != null) {
                        $delete->delete();
                    }
                }
            }

            $kandidat->statusUpisa_id = $request->StatusaUpisaKandidata;
            $kandidat->brojBodovaTest = $request->BrojBodovaTest;
            $kandidat->brojBodovaSkola = $request->BrojBodovaSkola;
            $kandidat->upisniRok = $request->UpisniRok;
            $kandidat->indikatorAktivan = $request->IndikatorAktivan;
            $kandidat->save();

            return redirect('/kandidat/');
        }

        else //grana za pozivanje view-a iz iste forme ------ ne koristi se
        {
            $sport = Sport::all();

            Session::put('id', $kandidat->id);

            app('App\Http\Controllers\SportskoAngazovanjeContoller')->index($kandidat);



            //Session::flush('kandidat');

            //Session::put('kandidat', 'nije');

            //Session::put('kandidat2', array($request));

            //$request->session()->flash('kandidat', $request);

            //return view('sifarnici.sportskoAngazovanje ', compact('kandidat', 'sport'));

            //return redirect()->back()->withInput();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Kandidat::destroy($id);

        return redirect('/kandidat/');
    }


    public function testPost(Request $request)
    {
        return $request->all();
    }
}
