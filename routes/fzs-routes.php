<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'App\Http\Controllers\HomeController@index');

Route::group(['middleware' => ['web']], function () {

    Route::resource('kandidat', 'App\Http\Controllers\KandidatController');
    Route::get('/kandidat/{id}/sportskoangazovanje', 'App\Http\Controllers\KandidatController@sport');
    Route::post('/kandidat/{id}/sportskoangazovanje', 'App\Http\Controllers\KandidatController@sportStore');
    Route::get('/kandidat/{id}/delete', 'App\Http\Controllers\KandidatController@destroy');
    Route::post('/kandidat/masovnaUplata', 'App\Http\Controllers\KandidatController@masovnaUplata');
    Route::post('/kandidat/masovniUpis', 'App\Http\Controllers\KandidatController@masovniUpis');

    Route::get('/master/create', 'App\Http\Controllers\KandidatController@createMaster');
    Route::get('/master/', 'App\Http\Controllers\KandidatController@indexMaster');
    Route::get('/master/{id}/edit', 'App\Http\Controllers\KandidatController@editMaster');
    Route::post('/master/{id}/edit', 'App\Http\Controllers\KandidatController@updateMaster');
    Route::post('/storeMaster/', 'App\Http\Controllers\KandidatController@storeMaster');
    Route::get('/master/{id}/delete', 'App\Http\Controllers\KandidatController@destroyMaster');
    Route::post('/master/masovnaUplata', 'App\Http\Controllers\KandidatController@masovnaUplataMaster');
    Route::post('/master/masovniUpis', 'App\Http\Controllers\KandidatController@masovniUpisMaster');

    Route::get('/kandidat/{id}/upis', 'App\Http\Controllers\KandidatController@upisKandidata');

    Route::get('/student/{id}/upis', 'App\Http\Controllers\StudentController@upisStudenta');
    Route::get('/student/{id}/uplataSkolarine', 'App\Http\Controllers\StudentController@uplataSkolarine');
    Route::get('/student/{id}/upisiStudenta', 'App\Http\Controllers\StudentController@upisiStudenta');
    Route::post('/student/masovnaUplata', 'App\Http\Controllers\StudentController@masovnaUplata');
    Route::post('/student/masovniUpis', 'App\Http\Controllers\StudentController@masovniUpis');
    Route::get('/student/index/{tipStudijaId}/', 'App\Http\Controllers\StudentController@index');
    Route::get('/student/zamrznuti', 'App\Http\Controllers\StudentController@zamrznutiStudenti');
    Route::get('/student/diplomirani', 'App\Http\Controllers\StudentController@diplomiraniStudenti');

    Route::get('/kalendar/', 'App\Http\Controllers\KalendarController@index');
    Route::get('/kalendar/indexRok/', 'App\Http\Controllers\KalendarController@indexRok');
    Route::get('/kalendar/createRok/', 'App\Http\Controllers\KalendarController@createRok');
    Route::get('/kalendar/editRok/{id}', 'App\Http\Controllers\KalendarController@editRok');
    Route::get('/kalendar/deleteRok/{id}', 'App\Http\Controllers\KalendarController@deleteRok');
    Route::post('/kalendar/updateRok', 'App\Http\Controllers\KalendarController@updateRok');
    Route::post('/kalendar/storeRok/', 'App\Http\Controllers\KalendarController@storeRok');
    Route::get('/kalendar/eventSource/', 'App\Http\Controllers\KalendarController@eventSource');

    Route::get('/prijava/zaStudenta/{kandidatId}', 'App\Http\Controllers\PrijavaController@svePrijaveIspitaZaStudenta');
    Route::get('/prijava/student/{kandidatId}', 'App\Http\Controllers\PrijavaController@createPrijavaIspitaStudent');
    Route::get('/predmeti/', 'App\Http\Controllers\PrijavaController@spisakPredmeta');
    Route::get('/prijava/zaPredmet/{predmetId}', 'App\Http\Controllers\PrijavaController@indexPrijavaIspitaPredmet');
    Route::get('/prijava/predmet/{predmetId}', 'App\Http\Controllers\PrijavaController@createPrijavaIspitaPredmet');
    Route::get('/prijava/predmetVise/{predmetId}', 'App\Http\Controllers\PrijavaController@createPrijavaIspitaPredmetMany');
    Route::post('/prijava/predmetVise/', 'App\Http\Controllers\PrijavaController@storePrijavaIspitaPredmetMany');
    Route::post('/prijava/', 'App\Http\Controllers\PrijavaController@storePrijavaIspita');
    Route::get('/prijava/delete/{id}', 'App\Http\Controllers\PrijavaController@deletePrijavaIspita');
    Route::post('/prijava/vratiKandidataPrijava', 'App\Http\Controllers\PrijavaController@vratiKandidataPrijava');
    Route::post('/prijava/vratiPredmetPrijava', 'App\Http\Controllers\PrijavaController@vratiPredmetPrijava');
    Route::post('/prijava/vratiKandidataPoBroju', 'App\Http\Controllers\PrijavaController@vratiKandidataPoBroju');

    Route::get('/zapisnik', 'App\Http\Controllers\IspitController@indexZapisnik');
    Route::get('/zapisnik/create', 'App\Http\Controllers\IspitController@createZapisnik');
    Route::get('/zapisnik/vratiZapisnikPredmet', 'App\Http\Controllers\IspitController@vratiZapisnikPredmet');
    Route::get('/zapisnik/vratiZapisnikStudenti', 'App\Http\Controllers\IspitController@vratiZapisnikStudenti');
    Route::post('/zapisnik/podaci', 'App\Http\Controllers\IspitController@podaci');
    Route::post('/zapisnik/storeZapisnik', 'App\Http\Controllers\IspitController@storeZapisnik');
    Route::get('/zapisnik/delete/{id}', 'App\Http\Controllers\IspitController@deleteZapisnik');
    Route::get('/zapisnik/pregled/{id}', 'App\Http\Controllers\IspitController@pregledZapisnik');
    Route::post('/zapisnik/polozeniIspit', 'App\Http\Controllers\IspitController@polozeniIspit');
    Route::get('/priznavanjeIspita/{kandidatId}', 'App\Http\Controllers\IspitController@priznavanjeIspita');
    Route::post('/storePriznatiIspiti/', 'App\Http\Controllers\IspitController@storePriznatiIspiti');
    Route::get('/deletePriznatIspit/{id}', 'App\Http\Controllers\IspitController@deletePriznatIspit');

    Route::get('/student/{id}/obnova', 'App\Http\Controllers\StudentController@obnoviGodinu');
    Route::get('/student/{id}/obrisiObnovu', 'App\Http\Controllers\StudentController@obrisiObnovuGodine');
    Route::get('/student/{id}/ponistiUplatu', 'App\Http\Controllers\StudentController@ponistiUplatu');
    Route::get('/student/{id}/ponistiUpis', 'App\Http\Controllers\StudentController@ponistiUpis');
    Route::get('/student/{id}/izmenaGodine', 'App\Http\Controllers\StudentController@izmenaGodine');
    Route::post('/student/{id}/izmenaGodine', 'App\Http\Controllers\StudentController@storeIzmenaGodine');
    Route::get('/student/{id}/status/{statusId}/{godinaId}', 'App\Http\Controllers\StudentController@promeniStatus');
    Route::get('/student/{id}/upisMasterStudija', 'App\Http\Controllers\StudentController@upisMasterStudija');

    Route::get('/pretraga', 'App\Http\Controllers\SearchController@search');
    Route::post('/pretraga', 'App\Http\Controllers\SearchController@searchResult');

    Route::get('/skolarina/{id}', 'App\Http\Controllers\SkolarinaController@index');
    Route::get('/skolarina/dodavanje/{id}', 'App\Http\Controllers\SkolarinaController@create');
    Route::get('/skolarina/izmena/{id}', 'App\Http\Controllers\SkolarinaController@edit');
    Route::get('/skolarina/view/{id}', 'App\Http\Controllers\SkolarinaController@view');
    Route::get('/skolarina/delete/{id}', 'App\Http\Controllers\SkolarinaController@delete');
    Route::get('/skolarina/uplata/{id}', 'App\Http\Controllers\SkolarinaController@createUplata');
    Route::get('/skolarina/uplata/edit/{id}', 'App\Http\Controllers\SkolarinaController@editUplata');
    Route::post('/skolarina/store', 'App\Http\Controllers\SkolarinaController@store');
    Route::post('/uplata/store', 'App\Http\Controllers\SkolarinaController@storeUplata');
    Route::get('/skolarina/uplata/delete/{id}', 'App\Http\Controllers\SkolarinaController@deleteUplata');
    Route::get('/skolarina/arhiva/{id}', 'App\Http\Controllers\SkolarinaController@arhiva');

});

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::get('/admintest','App\Http\Controllers\HomeController@adminTest');
});

Route::get('/tipStudija', 'App\Http\Controllers\TipStudijaController@index');
Route::post('/tipStudija/unos', 'App\Http\Controllers\TipStudijaController@unos');
Route::get('/tipStudija/add', 'App\Http\Controllers\TipStudijaController@add');
Route::get('/tipStudija/{tipStudija}/edit', 'App\Http\Controllers\TipStudijaController@edit');
Route::patch('tipStudija/{tipStudija}', 'App\Http\Controllers\TipStudijaController@update');
Route::get('tipStudija/{tipStudija}/delete', 'App\Http\Controllers\TipStudijaController@delete');

Route::get('/studijskiProgram', 'App\Http\Controllers\StudijskiProgramController@index');
Route::post('/studijskiProgram/unos', 'App\Http\Controllers\StudijskiProgramController@unos');
Route::get('/studijskiProgram/add', 'App\Http\Controllers\StudijskiProgramController@add');
Route::get('/studijskiProgram/{studijskiProgram}/edit', 'App\Http\Controllers\StudijskiProgramController@edit');
Route::patch('studijskiProgram/{studijskiProgram}', 'App\Http\Controllers\StudijskiProgramController@update');
Route::get('studijskiProgram/{studijskiProgram}/delete', 'App\Http\Controllers\StudijskiProgramController@delete');

Route::get('/godinaStudija', 'App\Http\Controllers\GodinaStudijaController@index');
Route::post('/godinaStudija/unos', 'App\Http\Controllers\GodinaStudijaController@unos');
Route::get('/godinaStudija/add', 'App\Http\Controllers\GodinaStudijaController@add');
Route::get('/godinaStudija/{godinaStudija}/edit', 'App\Http\Controllers\GodinaStudijaController@edit');
Route::patch('godinaStudija/{godinaStudija}', 'App\Http\Controllers\GodinaStudijaController@update');
Route::get('godinaStudija/{godinaStudija}/delete', 'App\Http\Controllers\GodinaStudijaController@delete');

Route::get('/sport', 'App\Http\Controllers\SportController@index');
Route::post('/sport/unos', 'App\Http\Controllers\SportController@unos');
Route::get('/sport/add', 'App\Http\Controllers\SportController@add');
Route::get('/sport/{sport}/edit', 'App\Http\Controllers\SportController@edit');
Route::patch('sport/{sport}', 'App\Http\Controllers\SportController@update');
Route::get('sport/{sport}/delete', 'App\Http\Controllers\SportController@delete');

Route::get('/statusStudiranja', 'App\Http\Controllers\StatusStudiranjaController@index');
Route::post('/statusStudiranja/unos', 'App\Http\Controllers\StatusStudiranjaController@unos');
Route::get('/statusStudiranja/add', 'App\Http\Controllers\StatusStudiranjaController@add');
Route::get('/statusStudiranja/{statusStudiranja}/edit', 'App\Http\Controllers\StatusStudiranjaController@edit');
Route::patch('statusStudiranja/{statusStudiranja}', 'App\Http\Controllers\StatusStudiranjaController@update');
Route::get('statusStudiranja/{statusStudiranja}/delete', 'App\Http\Controllers\StatusStudiranjaController@delete');

Route::get('/predmet', 'App\Http\Controllers\PredmetController@index');
Route::post('/predmet/unos', 'App\Http\Controllers\PredmetController@unos');
Route::get('/predmet/add', 'App\Http\Controllers\PredmetController@add');
Route::get('/predmet/{predmet}/edit', 'App\Http\Controllers\PredmetController@edit');
Route::patch('predmet/{predmet}', 'App\Http\Controllers\PredmetController@update');
Route::get('predmet/{predmet}/delete', 'App\Http\Controllers\PredmetController@delete');
Route::get('predmet/{program}/deleteProgram', 'App\Http\Controllers\PredmetController@deleteProgram');
Route::get('/predmet/{predmet}/addProgram', 'App\Http\Controllers\PredmetController@addProgram');
Route::post('predmet/addProgramUnos', 'App\Http\Controllers\PredmetController@addProgramUnos');
Route::get('/predmet/{predmet}/editProgram', 'App\Http\Controllers\PredmetController@editProgram');

Route::get('/profesor', 'App\Http\Controllers\ProfesorController@index');
Route::post('/profesor/unos', 'App\Http\Controllers\ProfesorController@unos');
Route::get('/profesor/add', 'App\Http\Controllers\ProfesorController@add');
Route::get('/profesor/{profesor}/edit', 'App\Http\Controllers\ProfesorController@edit');
Route::patch('profesor/{profesor}', 'App\Http\Controllers\ProfesorController@update');
Route::get('profesor/{profesor}/delete', 'App\Http\Controllers\ProfesorController@delete');
Route::get('profesor/{predmet}/deletePredmet', 'App\Http\Controllers\ProfesorController@deletePredmet');
Route::get('/profesor/{profesor}/addPredmet', 'App\Http\Controllers\ProfesorController@addPredmet');
Route::post('profesor/addPredmetUnos', 'App\Http\Controllers\ProfesorController@addPredmetUnos');
Route::get('/profesor/{profesor}/editPredmet', 'App\Http\Controllers\ProfesorController@editPredmet');

Route::get('izvestaji/spisakPoSmerovima', 'App\Http\Controllers\IzvestajiController@spisakPoSmerovima');
Route::get('/izvestaji/spiskoviStudenti', 'App\Http\Controllers\IzvestajiController@spiskoviStudenti');
Route::post('izvestaji/spisakZaSmer', 'App\Http\Controllers\IzvestajiController@spisakZaSmer');
Route::get('izvestaji/potvrdeStudent/{student}', 'App\Http\Controllers\IzvestajiController@potvrdeStudent');
Route::post('izvestaji/spisakPoSmerovimaAktivni', 'App\Http\Controllers\IzvestajiController@spisakPoSmerovimaAktivni');
Route::post('izvestaji/spisakPoSmerovimaOstali', 'App\Http\Controllers\IzvestajiController@spisakPoSmerovimaOstali');
Route::post('izvestaji/spisakPoPredmetima', 'App\Http\Controllers\IzvestajiController@spisakPoPredmetima');
Route::get('izvestaji/{student}/diplomaUnos', 'App\Http\Controllers\IzvestajiController@diplomaUnos');
Route::post('izvestaji/diplomaAdd', 'App\Http\Controllers\IzvestajiController@diplomaAdd');
Route::get('izvestaji/diplomaStampa/{student}', 'App\Http\Controllers\IzvestajiController@diplomaStampa');
Route::get('izvestaji/diplomskiUnos/{student}', 'App\Http\Controllers\IzvestajiController@diplomskiUnos');
Route::post('izvestaji/diplomskiAdd', 'App\Http\Controllers\IzvestajiController@diplomskiAdd');
Route::get('izvestaji/komisijaStampa/{student}', 'App\Http\Controllers\IzvestajiController@komisijaStampa');
Route::get('izvestaji/polozeniStampa/{student}', 'App\Http\Controllers\IzvestajiController@polozeniStampa');
Route::post('izvestaji/nastavniPlan', 'App\Http\Controllers\IzvestajiController@nastavniPlan');
Route::post('izvestaji/spisakDiplomiranih', 'App\Http\Controllers\IzvestajiController@spisakDiplomiranih');
Route::post('izvestaji/zapisnikStampa/{zapisnik}', 'App\Http\Controllers\IzvestajiController@zapisnikStampa');
Route::post('izvestaji/spisakPoGodini', 'App\Http\Controllers\IzvestajiController@spisakPoGodini');
Route::post('izvestaji/spisakPoProgramu', 'App\Http\Controllers\IzvestajiController@spisakPoProgramu');
Route::post('izvestaji/spisakPoSlavama', 'App\Http\Controllers\IzvestajiController@spisakPoSlavama');
Route::post('izvestaji/spisakPoProfesorima', 'App\Http\Controllers\IzvestajiController@spisakPoProfesorima');
Route::post('izvestaji/excelStampa', 'App\Http\Controllers\IzvestajiController@excelStampa');
Route::post('izvestaji/integralno', 'App\Http\Controllers\IzvestajiController@integralno');

Route::get('/test1', 'App\Http\Controllers\KandidatController@test1');
Route::get('/test2', 'App\Http\Controllers\KandidatController@test2');
Route::get('/test3', 'App\Http\Controllers\KandidatController@test3');
Route::post('/testPost', 'App\Http\Controllers\KandidatController@testPost');

Route::get('/regk/{id}', 'App\Http\Controllers\KandidatController@registracijaKandidata');

Route::get('/prisustvo', 'App\Http\Controllers\PrisustvoController@index')->name('prisustvo.index');
Route::get('/prisustvo/create', 'App\Http\Controllers\PrisustvoController@create')->name('prisustvo.create');
Route::post('/prisustvo', 'App\Http\Controllers\PrisustvoController@store')->name('prisustvo.store');
Route::get('/prisustvo/report', 'App\Http\Controllers\PrisustvoController@report')->name('prisustvo.report');

Route::get('/aktivnost', 'App\Http\Controllers\AktivnostController@index')->name('aktivnost.index');
Route::get('/aktivnost/create', 'App\Http\Controllers\AktivnostController@create')->name('aktivnost.create');
Route::post('/aktivnost', 'App\Http\Controllers\AktivnostController@store')->name('aktivnost.store');
Route::get('/aktivnost/{aktivnost}', 'App\Http\Controllers\AktivnostController@show')->name('aktivnost.show');
Route::get('/aktivnost/{aktivnost}/ocenjivanje', 'App\Http\Controllers\AktivnostController@ocenjivanje')->name('aktivnost.ocenjivanje');
Route::post('/aktivnost/{aktivnost}/ocenjivanje', 'App\Http\Controllers\AktivnostController@saveOcenjivanje')->name('aktivnost.saveOcenjivanje');
Route::get('/aktivnost/rezime', 'App\Http\Controllers\AktivnostController@rezime')->name('aktivnost.rezime');

Route::get('/raspored', 'App\Http\Controllers\RasporedController@index')->name('raspored.index');
Route::get('/raspored/create', 'App\Http\Controllers\RasporedController@create')->name('raspored.create');
Route::post('/raspored', 'App\Http\Controllers\RasporedController@store')->name('raspored.store');
Route::get('/raspored/{raspored}/edit', 'App\Http\Controllers\RasporedController@edit')->name('raspored.edit');
Route::put('/raspored/{raspored}', 'App\Http\Controllers\RasporedController@update')->name('raspored.update');
Route::delete('/raspored/{raspored}', 'App\Http\Controllers\RasporedController@destroy')->name('raspored.destroy');
Route::get('/raspored/pregled', 'App\Http\Controllers\RasporedController@pregled')->name('raspored.pregled');

Route::get('/obavestenja', 'App\Http\Controllers\ObavestenjeController@index')->name('obavestenja.index');
Route::get('/obavestenja/create', 'App\Http\Controllers\ObavestenjeController@create')->name('obavestenja.create');
Route::post('/obavestenja', 'App\Http\Controllers\ObavestenjeController@store')->name('obavestenja.store');
Route::get('/obavestenja/{obavestenje}', 'App\Http\Controllers\ObavestenjeController@show')->name('obavestenja.show');
Route::get('/obavestenja/{obavestenje}/edit', 'App\Http\Controllers\ObavestenjeController@edit')->name('obavestenja.edit');
Route::put('/obavestenja/{obavestenje}', 'App\Http\Controllers\ObavestenjeController@update')->name('obavestenja.update');
Route::delete('/obavestenja/{obavestenje}', 'App\Http\Controllers\ObavestenjeController@destroy')->name('obavestenja.destroy');
Route::get('/obavestenja/{obavestenje}/toggle', 'App\Http\Controllers\ObavestenjeController@toggleStatus')->name('obavestenja.toggle');
Route::get('/obavestenja/javna', 'App\Http\Controllers\ObavestenjeController@javna')->name('obavestenja.javna');
Route::get('/moja-obavestenja', 'App\Http\Controllers\ObavestenjeController@moja')->name('obavestenja.moja');

Route::get('/dashboard', 'App\Http\Controllers\DashboardController@index')->name('dashboard.index');
Route::get('/dashboard/studenti', 'App\Http\Controllers\DashboardController@studenti')->name('dashboard.studenti');
Route::get('/dashboard/ispiti', 'App\Http\Controllers\DashboardController@ispiti')->name('dashboard.ispiti');
