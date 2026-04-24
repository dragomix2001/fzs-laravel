<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Rute koje se aktivno koriste u projektu
// Home route
Route::get('/', 'HomeController@index');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => 'FZS Laravel',
        'version' => '1.0.0',
    ]);
});

// Added by Andrija
// Routes in the web middleware group

Route::group(['middleware' => ['web']], function () {

    // Auth routes - manual definitions
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login')->middleware('throttle:5,1');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    Route::resource('kandidat', 'KandidatController');
    Route::get('/kandidat/{id}/sportskoangazovanje', 'KandidatController@sport')->name('kandidat.sport');
    Route::post('/kandidat/{id}/sportskoangazovanje', 'KandidatController@sportStore')->name('kandidat.sportStore');

    Route::get('/kandidat/{id}/delete', 'KandidatController@destroy')->name('kandidat.delete');

    Route::post('/kandidat/masovnaUplata', 'KandidatController@masovnaUplata')->name('kandidat.masovnaUplata');
    Route::post('/kandidat/masovniUpis', 'KandidatController@masovniUpis')->name('kandidat.masovniUpis');

    Route::get('/master/create', 'KandidatController@createMaster')->name('master.create');
    Route::get('/master/', 'KandidatController@indexMaster')->name('master.index');
    Route::get('/master/{id}/edit', 'KandidatController@editMaster')->name('master.edit');
    Route::post('/master/{id}/edit', 'KandidatController@updateMaster')->name('master.update');
    Route::post('/storeMaster/', 'KandidatController@storeMaster')->name('master.store');
    Route::get('/master/{id}/delete', 'KandidatController@destroyMaster')->name('master.destroy');

    Route::post('/master/masovnaUplata', 'KandidatController@masovnaUplataMaster')->name('master.masovnaUplata');
    Route::post('/master/masovniUpis', 'KandidatController@masovniUpisMaster')->name('master.masovniUpis');

    Route::get('/kandidat/{id}/upis', 'KandidatController@upisKandidata')->name('kandidat.upis');

    Route::get('/student/{id}/upis', 'StudentController@upisStudenta');

    Route::get('/student/{id}/uplataSkolarine', 'StudentController@uplataSkolarine');
    Route::get('/student/{id}/upisiStudenta', 'StudentController@upisiStudenta');

    Route::post('/student/masovnaUplata', 'StudentController@masovnaUplata');
    Route::post('/student/masovniUpis', 'StudentController@masovniUpis');

    Route::get('/student/index/{tipStudijaId}/', 'StudentController@index')->name('student.index');
    // zamrznuti studenti
    Route::get('/student/zamrznuti', 'StudentController@zamrznutiStudenti');
    // diplomirani studenti
    Route::get('/student/diplomirani', 'StudentController@diplomiraniStudenti');
    // ispisani studenti
    Route::get('/student/ispisani', 'StudentController@ispisaniStudenti');

    Route::get('/kalendar/', 'KalendarController@index');
    Route::get('/kalendar/indexRok/', 'KalendarController@indexRok');
    Route::get('/kalendar/createRok/', 'KalendarController@createRok');
    Route::get('/kalendar/editRok/{id}', 'KalendarController@editRok');
    Route::get('/kalendar/deleteRok/{id}', 'KalendarController@deleteRok');
    Route::post('/kalendar/updateRok', 'KalendarController@updateRok');
    Route::post('/kalendar/storeRok/', 'KalendarController@storeRok');
    Route::get('/kalendar/eventSource/', 'KalendarController@eventSource');

    // Prijava za ispit preko studenta (INDEX i CREATE Student-Prijava)
    Route::get('/prijava/zaStudenta/{kandidatId}', 'PrijavaController@svePrijaveIspitaZaStudenta');
    Route::get('/prijava/student/{kandidatId}', 'PrijavaController@createPrijavaIspitaStudent');

    // Spisak predmeta za prijavu ispita
    Route::get('/predmeti/', 'PrijavaController@spisakPredmeta');

    // (INDEX i CREATE Predmet-Prijava)
    Route::get('/prijava/zaPredmet/{predmetId}', 'PrijavaController@indexPrijavaIspitaPredmet');
    Route::get('/prijava/predmet/{predmetId}', 'PrijavaController@createPrijavaIspitaPredmet');
    Route::get('/prijava/predmetVise/{predmetId}', 'PrijavaController@createPrijavaIspitaPredmetMany');
    Route::post('/prijava/predmetVise/', 'PrijavaController@storePrijavaIspitaPredmetMany');

    Route::post('/prijava/', 'PrijavaController@storePrijavaIspita');
    Route::get('/prijava/delete/{id}', 'PrijavaController@deletePrijavaIspita');
    Route::get('/prijava/unosPrivremeni/{kandidat}', 'PrijavaController@unosPrivremeni');

    Route::post('/prijava/dodajPolozeneIspite', 'PrijavaController@dodajPolozeneIspite');

    // AJAX pozivi sa prijave
    Route::post('/prijava/vratiKandidataPrijava', 'PrijavaController@vratiKandidataPrijava');
    Route::post('/prijava/vratiPredmetPrijava', 'PrijavaController@vratiPredmetPrijava');
    Route::post('/prijava/vratiKandidataPoBroju', 'PrijavaController@vratiKandidataPoBroju');
    Route::post('/prijava/vratiIspitPoId', 'PrijavaController@vratiIspitPoId');

    // Diplomski rad
    // TEMA
    Route::get('/prijava/diplomskiTema/{kandidat}', 'PrijavaController@diplomskiTema');
    Route::get('/prijava/vratiIspitPoId', 'PrijavaController@vratiIspitPoId');
    Route::get('/prijava/vratiIspitPoId', 'PrijavaController@vratiIspitPoId');
    Route::post('/prijava/storeDiplomskiTema', 'PrijavaController@storeDiplomskiTema');
    Route::get('/prijava/diplomskiTema/{kandidat}/edit', 'PrijavaController@editDiplomskiTema');
    Route::post('/prijava/updateDiplomskiTema', 'PrijavaController@updateDiplomskiTema');
    Route::get('/deleteDiplomskiTema/{kandidat}/delete', 'PrijavaController@deleteDiplomskiTema');

    // ODBRANA
    Route::get('/prijava/diplomskiOdbrana/{kandidat}', 'PrijavaController@diplomskiOdbrana');
    Route::post('/prijava/storeDiplomskiOdbrana', 'PrijavaController@storeDiplomskiOdbrana');
    Route::get('/prijava/diplomskiOdbrana/{kandidat}/edit', 'PrijavaController@editDiplomskiOdbrana');
    Route::post('/prijava/updateDiplomskiOdbrana', 'PrijavaController@updateDiplomskiOdbrana');
    Route::get('/deleteDiplomskiOdbrana/{kandidat}/delete', 'PrijavaController@deleteDiplomskiOdbrana');

    // POLAGANJE
    Route::get('/prijava/diplomskiPolaganje/{kandidat}', 'PrijavaController@diplomskiPolaganje');
    Route::post('/prijava/storeDiplomskiPolaganje', 'PrijavaController@storeDiplomskipolaganje');
    Route::get('/prijava/diplomskiPolaganje/{kandidat}/edit', 'PrijavaController@editDiplomskiPolaganje');
    Route::post('/prijava/updateDiplomskiPolaganje', 'PrijavaController@updateDiplomskiPolaganje');
    Route::get('/deleteDiplomskiPolaganje/{kandidat}/delete', 'PrijavaController@deleteDiplomskiPolaganje');

    // Zapisnik o polaganju ispita
    Route::get('/zapisnik', 'IspitController@indexZapisnik');
    Route::get('/zapisnik/create', 'IspitController@createZapisnik');
    Route::get('/zapisnik/vratiZapisnikPredmet', 'IspitController@vratiZapisnikPredmet');
    Route::get('/zapisnik/vratiZapisnikStudenti', 'IspitController@vratiZapisnikStudenti');
    Route::post('/zapisnik/podaci', 'IspitController@podaci');
    Route::post('/zapisnik/storeZapisnik', 'IspitController@storeZapisnik');
    Route::get('/zapisnik/delete/{id}', 'IspitController@deleteZapisnik');
    Route::get('/zapisnik/pregled/{id}', 'IspitController@pregledZapisnik');
    Route::get('/zapisnik/pregled/{zapisnikId}/{kandidatId}/delete', 'IspitController@pregledZapisnikDelete');
    Route::post('/zapisnik/pregled/dodajStudenta', 'IspitController@dodajStudenta');
    Route::post('/zapisnik/pregled/izmeniPodatke', 'IspitController@izmeniPodatke');
    Route::get('/zapisnik/arhiva/', 'IspitController@arhivaZapisnik');
    Route::get('/zapisnik/pretraga/', 'IspitController@pretragaZapisnik');
    Route::get('/zapisnik/arhiviraj/{id}', 'IspitController@arhivirajZapisnik');
    Route::post('/zapisnik/arhivirajRok', 'IspitController@arhivirajZapisnikeZaIspitniRok');

    Route::get('/ispit/delete/{id}', 'IspitController@deletePolozeniIspit');

    Route::post('/zapisnik/polozeniIspit', 'IspitController@polozeniIspit');

    // Priznavanje ispita za kandidate koji se upisusju na II, III, i IV godinu studija
    Route::get('/priznavanjeIspita/{kandidatId}', 'IspitController@priznavanjeIspita');
    Route::post('/storePriznatiIspiti/', 'IspitController@storePriznatiIspiti');
    Route::get('/deletePriznatIspit/{id}', 'IspitController@deletePriznatIspit');
    Route::get('/deletePrivremeniIspit/{id}', 'IspitController@deletePrivremeniIspit');

    Route::get('/student/{id}/obnova', 'StudentController@obnoviGodinu');
    Route::get('/student/{id}/obrisiObnovu', 'StudentController@obrisiObnovuGodine');
    Route::get('/student/{id}/ponistiUplatu', 'StudentController@ponistiUplatu');
    Route::get('/student/{id}/ponistiUpis', 'StudentController@ponistiUpis');
    Route::get('/student/{id}/izmenaGodine', 'StudentController@izmenaGodine');
    Route::post('/student/{id}/izmenaGodine', 'StudentController@storeIzmenaGodine');
    Route::get('/student/{id}/status/{statusId}/{godinaId}', 'StudentController@promeniStatus');
    Route::get('/student/{id}/upisMasterStudija', 'StudentController@upisMasterStudija');

    // PRETRAGA
    Route::get('/pretraga', 'SearchController@search');
    Route::post('/pretraga', 'SearchController@searchResult');

    // SKOLARINA
    Route::get('/skolarina/{id}', 'SkolarinaController@index');
    Route::get('/skolarina/dodavanje/{id}', 'SkolarinaController@create');
    Route::get('/skolarina/izmena/{id}', 'SkolarinaController@edit');
    Route::get('/skolarina/view/{id}', 'SkolarinaController@view');
    Route::get('/skolarina/delete/{id}', 'SkolarinaController@delete');
    Route::get('/skolarina/uplata/{id}', 'SkolarinaController@createUplata');
    Route::get('/skolarina/uplata/edit/{id}', 'SkolarinaController@editUplata');
    Route::post('/skolarina/store', 'SkolarinaController@store');
    Route::post('/uplata/store', 'SkolarinaController@storeUplata');
    Route::get('/skolarina/uplata/delete/{id}', 'SkolarinaController@deleteUplata');
    Route::get('/skolarina/arhiva/{id}', 'SkolarinaController@arhiva');

});

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::get('/admintest', 'HomeController@adminTest');
});

// rute za sifarnike
Route::get('/tipStudija', 'TipStudijaController@index');
Route::get('/studijskiProgram', 'StudijskiProgramController@index');
Route::get('/godinaStudija', 'GodinaStudijaController@index');
Route::get('/sport', 'SportController@index');
// Route::get('/sportskoAngazovanje/{kandidat}', 'SportskoAngazovanjeController@index');
Route::get('/statusStudiranja', 'StatusStudiranjaController@index');
Route::get('/predmet', 'PredmetController@index');
Route::get('/srednjeSkoleFakulteti', 'SrednjeSkoleFakultetiController@index');
Route::get('/krsnaSlava', 'KrsnaSlavaController@index');
Route::get('/region', 'RegionController@index');
Route::get('/opstina', 'OpstinaController@index');
Route::get('/mesto', 'MestoController@index');
Route::get('/prilozenaDokumenta', 'PrilozenaDokumentaController@index');
Route::get('/semestar', 'SemestarController@index');
Route::get('/ispitniRok', 'IspitniRokController@index');
Route::get('/oblikNastave', 'OblikNastaveController@index');
Route::get('/tipPredmeta', 'TipPredmetaController@index');
Route::get('/bodovanje', 'BodovanjeController@index');
Route::get('/statusKandidata', 'StatusKandidataController@index');
Route::get('/statusIspita', 'StatusIspitaController@index');
Route::get('/statusProfesora', 'StatusProfesoraController@index');
Route::get('/tipPrijave', 'TipPrijaveController@index');
Route::get('/profesor', 'ProfesorController@index');

Route::post('/tipStudija/unos', 'TipStudijaController@unos');
Route::post('/studijskiProgram/unos', 'StudijskiProgramController@unos');
Route::post('/godinaStudija/unos', 'GodinaStudijaController@unos');
Route::post('/sport/unos', 'SportController@unos');
Route::post('/srednjeSkoleFakulteti/unos', 'SrednjeSkoleFakultetiController@unos');
Route::post('/krsnaSlava/unos', 'KrsnaSlavaController@unos');
Route::post('/region/unos', 'RegionController@unos');
Route::post('/opstina/unos', 'OpstinaController@unos');
Route::post('/mesto/unos', 'MestoController@unos');
Route::post('/statusStudiranja/unos', 'StatusStudiranjaController@unos');
Route::post('/predmet/unos', 'PredmetController@unos');
Route::post('/prilozenaDokumenta/unos', 'PrilozenaDokumentaController@unos');
Route::post('/sportskoAngazovanje/unos', 'SportskoAngazovanjeController@unos');
Route::post('/semestar/unos', 'SemestarController@unos');
Route::post('/ispitniRok/unos', 'IspitniRokController@unos');
Route::post('/oblikNastave/unos', 'OblikNastaveController@unos');
Route::post('/tipPredmeta/unos', 'TipPredmetaController@unos');
Route::post('/bodovanje/unos', 'BodovanjeController@unos');
Route::post('/statusKandidata/unos', 'StatusKandidataController@unos');
Route::post('/statusIspita/unos', 'StatusIspitaController@unos');
Route::post('/statusProfesora/unos', 'StatusProfesoraController@unos');
Route::post('/tipPrijave/unos', 'TipPrijaveController@unos');
Route::post('/profesor/unos', 'ProfesorController@unos');

Route::get('/tipStudija/add', 'TipStudijaController@add');
Route::get('/studijskiProgram/add', 'StudijskiProgramController@add');
Route::get('/godinaStudija/add', 'GodinaStudijaController@add');
Route::get('/sport/add', 'SportController@add');
Route::get('/srednjeSkoleFakulteti/add', 'SrednjeSkoleFakultetiController@add');
Route::get('/krsnaSlava/add', 'KrsnaSlavaController@add');
Route::get('/region/add', 'RegionController@add');
Route::get('/opstina/add', 'OpstinaController@add');
Route::get('/mesto/add', 'MestoController@add');
Route::get('/statusStudiranja/add', 'StatusStudiranjaController@add');
Route::get('/predmet/add', 'PredmetController@add');
Route::get('/prilozenaDokumenta/add', 'PrilozenaDokumentaController@add');
Route::get('/sportskoAngazovanje/add', 'SportskoAngazovanjeController@add');
Route::get('/semestar/add', 'SemestarController@add');
Route::get('/ispitniRok/add', 'IspitniRokController@add');
Route::get('/oblikNastave/add', 'OblikNastaveController@add');
Route::get('/tipPredmeta/add', 'TipPredmetaController@add');
Route::get('/bodovanje/add', 'BodovanjeController@add');
Route::get('/statusKandidata/add', 'StatusKandidataController@add');
Route::get('/statusIspita/add', 'StatusIspitaController@add');
Route::get('/statusProfesora/add', 'StatusProfesoraController@add');
Route::get('/tipPrijave/add', 'TipPrijaveController@add');
Route::get('/profesor/add', 'ProfesorController@add');

Route::get('/sport/{sport}/edit', 'SportController@edit');
Route::patch('sport/{sport}', 'SportController@update');
Route::get('sport/{sport}/delete', 'SportController@delete');

Route::get('/tipStudija/{tipStudija}/edit', 'TipStudijaController@edit');
Route::patch('tipStudija/{tipStudija}', 'TipStudijaController@update');
Route::get('tipStudija/{tipStudija}/delete', 'TipStudijaController@delete');

Route::get('/studijskiProgram/{studijskiProgram}/edit', 'StudijskiProgramController@edit');
Route::patch('studijskiProgram/{studijskiProgram}', 'StudijskiProgramController@update');
Route::get('studijskiProgram/{studijskiProgram}/delete', 'StudijskiProgramController@delete');

Route::get('/godinaStudija/{godinaStudija}/edit', 'GodinaStudijaController@edit');
Route::patch('godinaStudija/{godinaStudija}', 'GodinaStudijaController@update');
Route::get('godinaStudija/{godinaStudija}/delete', 'GodinaStudijaController@delete');

Route::get('/statusStudiranja/{statusStudiranja}/edit', 'StatusStudiranjaController@edit');
Route::patch('statusStudiranja/{statusStudiranja}', 'StatusStudiranjaController@update');
Route::get('statusStudiranja/{statusStudiranja}/delete', 'StatusStudiranjaController@delete');

Route::get('/predmet/{predmet}/edit', 'PredmetController@edit');
Route::patch('predmet/{predmet}', 'PredmetController@update');
Route::get('predmet/{predmet}/delete', 'PredmetController@delete');
Route::get('predmet/{program}/deleteProgram', 'PredmetController@deleteProgram');
Route::get('/predmet/{predmet}/addProgram', 'PredmetController@addProgram');
Route::post('predmet/addProgramUnos', 'PredmetController@addProgramUnos');
Route::get('/predmet/{predmet}/editProgram', 'PredmetController@editProgram');

Route::get('/krsnaSlava/{krsnaSlava}/edit', 'KrsnaSlavaController@edit');
Route::patch('krsnaSlava/{krsnaSlava}', 'KrsnaSlavaController@update');
Route::get('krsnaSlava/{krsnaSlava}/delete', 'KrsnaSlavaController@delete');

Route::get('/region/{region}/edit', 'RegionController@edit');
Route::patch('region/{region}', 'RegionController@update');
Route::get('region/{region}/delete', 'RegionController@delete');

Route::get('/opstina/{opstina}/edit', 'OpstinaController@edit');
Route::patch('opstina/{opstina}', 'OpstinaController@update');
Route::get('opstina/{opstina}/delete', 'OpstinaController@delete');

Route::get('/mesto/{mesto}/edit', 'MestoController@edit');
Route::patch('mesto/{mesto}', 'MestoController@update');
Route::get('mesto/{mesto}/delete', 'MestoController@delete');

Route::get('/srednjeSkoleFakulteti/{srednjeSkoleFakulteti}/edit', 'SrednjeSkoleFakultetiController@edit');
Route::patch('srednjeSkoleFakulteti/{srednjeSkoleFakulteti}', 'SrednjeSkoleFakultetiController@update');
Route::get('srednjeSkoleFakulteti/{srednjeSkoleFakulteti}/delete', 'SrednjeSkoleFakultetiController@delete');

Route::get('/prilozenaDokumenta/{dokument}/edit', 'PrilozenaDokumentaController@edit');
Route::patch('prilozenaDokumenta/{dokument}', 'PrilozenaDokumentaController@update');
Route::get('prilozenaDokumenta/{dokument}/delete', 'PrilozenaDokumentaController@delete');

Route::get('/sportskoAngazovanje/vrati', 'SportskoAngazovanjeController@vrati');
Route::get('/sportskoAngazovanje/{angazovanje}/edit', 'SportskoAngazovanjeController@edit');
Route::patch('sportskoAngazovanje/{angazovanje}', 'SportskoAngazovanjeController@update');
Route::get('sportskoAngazovanje/{angazovanje}/delete', 'SportskoAngazovanjeController@delete');

Route::get('/semestar/{semestar}/edit', 'SemestarController@edit');
Route::patch('semestar/{semestar}', 'SemestarController@update');
Route::get('semestar/{semestar}/delete', 'SemestarController@delete');

Route::get('/ispitniRok/{ispitniRok}/edit', 'IspitniRokController@edit');
Route::patch('ispitniRok/{ispitniRok}', 'IspitniRokController@update');
Route::get('ispitniRok/{ispitniRok}/delete', 'IspitniRokController@delete');

Route::get('/oblikNastave/{oblikNastave}/edit', 'OblikNastaveController@edit');
Route::patch('oblikNastave/{oblikNastave}', 'OblikNastaveController@update');
Route::get('oblikNastave/{oblikNastave}/delete', 'OblikNastaveController@delete');

Route::get('/tipPredmeta/{tipPredmeta}/edit', 'TipPredmetaController@edit');
Route::patch('tipPredmeta/{tipPredmeta}', 'TipPredmetaController@update');
Route::get('tipPredmeta/{tipPredmeta}/delete', 'TipPredmetaController@delete');

Route::get('/bodovanje/{bodovanje}/edit', 'BodovanjeController@edit');
Route::patch('bodovanje/{bodovanje}', 'BodovanjeController@update');
Route::get('bodovanje/{bodovanje}/delete', 'BodovanjeController@delete');

Route::get('/statusKandidata/{status}/edit', 'StatusKandidataController@edit');
Route::patch('statusKandidata/{status}', 'StatusKandidataController@update');
Route::get('statusKandidata/{status}/delete', 'StatusKandidataController@delete');

Route::get('/statusIspita/{status}/edit', 'StatusIspitaController@edit');
Route::patch('statusIspita/{status}', 'StatusIspitaController@update');
Route::get('statusIspita/{status}/delete', 'StatusIspitaController@delete');

Route::get('/statusProfesora/{status}/edit', 'StatusProfesoraController@edit');
Route::patch('statusProfesora/{status}', 'StatusProfesoraController@update');
Route::get('statusProfesora/{status}/delete', 'StatusProfesoraController@delete');

Route::get('/tipPrijave/{tip}/edit', 'TipPrijaveController@edit');
Route::patch('tipPrijave/{tip}', 'TipPrijaveController@update');
Route::get('tipPrijave/{tip}/delete', 'TipPrijaveController@delete');

Route::get('/profesor/{profesor}/edit', 'ProfesorController@edit');
Route::patch('profesor/{profesor}', 'ProfesorController@update');
Route::get('profesor/{profesor}/delete', 'ProfesorController@delete');
Route::get('profesor/{predmet}/deletePredmet', 'ProfesorController@deletePredmet');
Route::get('/profesor/{profesor}/addPredmet', 'ProfesorController@addPredmet');
Route::post('profesor/addPredmetUnos', 'ProfesorController@addPredmetUnos');
Route::get('/profesor/{profesor}/editPredmet', 'ProfesorController@editPredmet');

// /izvestaji

Route::get('izvestaji/spisakPoSmerovima', 'IzvestajiController@spisakPoSmerovima');
Route::get('/izvestaji/spiskoviStudenti', 'IzvestajiController@spiskoviStudenti');
Route::post('izvestaji/spisakZaSmer', 'IzvestajiController@spisakZaSmer');
Route::get('izvestaji/potvrdeStudent/{student}', 'IzvestajiController@potvrdeStudent');
Route::post('izvestaji/spisakPoSmerovimaAktivni', 'IzvestajiController@spisakPoSmerovimaAktivni');
Route::post('izvestaji/spisakPoSmerovimaOstali', 'IzvestajiController@spisakPoSmerovimaOstali');
Route::post('izvestaji/spisakPoPredmetima', 'IzvestajiController@spisakPoPredmetima');
Route::get('izvestaji/{student}/diplomaUnos', 'IzvestajiController@diplomaUnos');
Route::post('izvestaji/diplomaAdd', 'IzvestajiController@diplomaAdd');
Route::get('izvestaji/diplomaStampa/{student}', 'IzvestajiController@diplomaStampa');
Route::get('izvestaji/diplomskiUnos/{student}', 'IzvestajiController@diplomskiUnos');
Route::post('izvestaji/diplomskiAdd', 'IzvestajiController@diplomskiAdd');
Route::get('izvestaji/diplomaUnos/{student}', 'IzvestajiController@diplomaUnos');
Route::get('izvestaji/komisijaStampa/{student}', 'IzvestajiController@komisijaStampa');
Route::get('izvestaji/polozeniStampa/{student}', 'IzvestajiController@polozeniStampa');
Route::post('izvestaji/nastavniPlan', 'IzvestajiController@nastavniPlan');
Route::post('izvestaji/spisakDiplomiranih', 'IzvestajiController@spisakDiplomiranih');
Route::post('izvestaji/zapisnikStampa/{zapisnik}', 'IzvestajiController@zapisnikStampa');
Route::post('izvestaji/spisakPoGodini', 'IzvestajiController@spisakPoGodini');
Route::post('izvestaji/spisakPoProgramu', 'IzvestajiController@spisakPoProgramu');
Route::post('izvestaji/spisakPoSlavama', 'IzvestajiController@spisakPoSlavama');
Route::post('izvestaji/spisakPoProfesorima', 'IzvestajiController@spisakPoProfesorima');
Route::post('izvestaji/excelStampa', 'IzvestajiController@excelStampa');
Route::post('izvestaji/integralno', 'IzvestajiController@integralno');
Route::get('izvestaji/zapisnikDiplomski/{student}', 'IzvestajiController@zapisnikDiplomski');

// Route::any('/kandidat/{kandidat}/{indikator}', 'KandidatController@update');

// Dodao Andrija rute za testiranje
Route::get('/test1', 'KandidatController@test1');
Route::get('/test2', 'KandidatController@test2');
Route::get('/test3', 'KandidatController@test3');

Route::post('/testPost', 'KandidatController@testPost');

Route::get('/home', 'HomeController@index');

Route::get('/regk/{id}', 'KandidatController@registracijaKandidata')->name('kandidat.registracija');

// Nove rute za module
Route::get('/prisustvo', 'PrisustvoController@index')->name('prisustvo.index');
Route::get('/prisustvo/create', 'PrisustvoController@create')->name('prisustvo.create');
Route::post('/prisustvo', 'PrisustvoController@store')->name('prisustvo.store');
Route::get('/prisustvo/report', 'PrisustvoController@report')->name('prisustvo.report');

Route::get('/aktivnost', 'AktivnostController@index')->name('aktivnost.index');
Route::get('/aktivnost/create', 'AktivnostController@create')->name('aktivnost.create');
Route::post('/aktivnost', 'AktivnostController@store')->name('aktivnost.store');
Route::get('/aktivnost/{aktivnost}', 'AktivnostController@show')->name('aktivnost.show');
Route::get('/aktivnost/{aktivnost}/ocenjivanje', 'AktivnostController@ocenjivanje')->name('aktivnost.ocenjivanje');
Route::post('/aktivnost/{aktivnost}/ocenjivanje', 'AktivnostController@saveOcenjivanje')->name('aktivnost.saveOcenjivanje');
Route::get('/aktivnost/rezime', 'AktivnostController@rezime')->name('aktivnost.rezime');

Route::get('/raspored', 'RasporedController@index')->name('raspored.index');
Route::get('/raspored/create', 'RasporedController@create')->name('raspored.create');
Route::post('/raspored', 'RasporedController@store')->name('raspored.store');
Route::get('/raspored/{raspored}/edit', 'RasporedController@edit')->name('raspored.edit');
Route::put('/raspored/{raspored}', 'RasporedController@update')->name('raspored.update');
Route::delete('/raspored/{raspored}', 'RasporedController@destroy')->name('raspored.destroy');
Route::get('/raspored/pregled', 'RasporedController@pregled')->name('raspored.pregled');
Route::get('/raspored/kalendar', 'RasporedController@kalendar')->name('raspored.kalendar');
Route::get('/raspored/kalendar/events', 'RasporedController@kalendarEvents')->name('raspored.kalendar.events');

Route::get('/obavestenja', 'ObavestenjeController@index')->name('obavestenja.index');
Route::get('/obavestenja/create', 'ObavestenjeController@create')->name('obavestenja.create');
Route::post('/obavestenja', 'ObavestenjeController@store')->name('obavestenja.store');
Route::get('/obavestenja/javna', 'ObavestenjeController@javna')->name('obavestenja.javna');
Route::get('/language/switch', 'LanguageController@switch')->name('language.switch');
Route::get('/moja-obavestenja', 'ObavestenjeController@moja')->name('obavestenja.moja');
Route::get('/obavestenja/{obavestenje}', 'ObavestenjeController@show')->name('obavestenja.show');
Route::get('/obavestenja/{obavestenje}/edit', 'ObavestenjeController@edit')->name('obavestenja.edit');
Route::put('/obavestenja/{obavestenje}', 'ObavestenjeController@update')->name('obavestenja.update');
Route::delete('/obavestenja/{obavestenje}', 'ObavestenjeController@destroy')->name('obavestenja.destroy');
Route::get('/obavestenja/{obavestenje}/toggle', 'ObavestenjeController@toggleStatus')->name('obavestenja.toggle');

Route::get('/dashboard', 'DashboardController@index')->name('dashboard.index');
Route::post('/dashboard/widgets', 'DashboardController@saveWidgets')->name('dashboard.widgets');
Route::get('/dashboard/studenti', 'DashboardController@studenti')->name('dashboard.studenti');
Route::get('/dashboard/ispiti', 'DashboardController@ispiti')->name('dashboard.ispiti');

// AI rute
Route::get('/chatbot', 'ChatbotController@index')->name('chatbot.index');
Route::post('/chatbot/chat', 'ChatbotController@chat')->name('chatbot.chat');
Route::post('/chatbot/clear', 'ChatbotController@clearHistory')->name('chatbot.clear');
Route::post('/chatbot/quick', 'ChatbotController@quickQuestion')->name('chatbot.quick');

Route::get('/prediction', 'PredictionController@index')->name('prediction.index');
Route::get('/prediction/student/{id}', 'PredictionController@studentPrediction')->name('prediction.student');
Route::get('/prediction/statistics', 'PredictionController@classStatistics')->name('prediction.statistics');
Route::get('/api/prediction/student/{id}', 'PredictionController@apiStudentPrediction')->name('api.prediction.student');
Route::get('/api/prediction/statistics', 'PredictionController@apiClassStatistics')->name('api.prediction.statistics');

// User management routes (admin only)
Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/kandidat/documents/incomplete', 'DocumentReviewController@index')->name('kandidat.documents.incomplete');
    Route::get('/kandidat/{kandidat}/documents/review', 'DocumentReviewController@show')->name('kandidat.documents.review');
    Route::patch('/kandidat/{kandidat}/documents/{attachment}/approve', 'DocumentReviewController@approve')->name('kandidat.documents.approve');
    Route::patch('/kandidat/{kandidat}/documents/{attachment}/reject', 'DocumentReviewController@reject')->name('kandidat.documents.reject');
    Route::patch('/kandidat/{kandidat}/documents/{attachment}/needs-revision', 'DocumentReviewController@needsRevision')->name('kandidat.documents.needs-revision');

    Route::get('/users', 'UserController@index')->name('user.index');
    Route::get('/users/create', 'UserController@create')->name('user.create');
    Route::post('/users', 'UserController@store')->name('user.store');
    Route::get('/users/{user}', 'UserController@show')->name('user.show');
    Route::get('/users/{user}/edit', 'UserController@edit')->name('user.edit');
    Route::put('/users/{user}', 'UserController@update')->name('user.update');
    Route::delete('/users/{user}', 'UserController@destroy')->name('user.destroy');
    Route::get('/users/{user}/toggle', 'UserController@toggleStatus')->name('user.toggle');

    // Audit log routes
    Route::get('/audit-logs', 'AuditLogController@index')->name('audit.index');
});
