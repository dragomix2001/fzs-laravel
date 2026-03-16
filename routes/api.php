<?php

use App\Http\Controllers\Api\AktivnostController;
use App\Http\Controllers\Api\ApiIspitController;
use App\Http\Controllers\Api\ApiKandidatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ObavestenjeController;
use App\Http\Controllers\Api\RasporedController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::prefix('v1')->group(function () {
    Route::apiResources([
        'kandidati' => ApiKandidatController::class,
        'ispiti' => ApiIspitController::class,
    ]);

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
    });

    Route::get('/obavestenja/javna', [ObavestenjeController::class, 'javna']);
    Route::get('/obavestenja', [ObavestenjeController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/obavestenja/{obavestenje}', [ObavestenjeController::class, 'show'])->middleware('auth:sanctum');

    Route::prefix('student')->middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [StudentController::class, 'profile']);
        Route::get('/ispiti', [StudentController::class, 'polozeniIspiti']);
        Route::get('/prijave', [StudentController::class, 'prijave']);
        Route::get('/upis', [StudentController::class, 'upis']);
        Route::get('/stats', [StudentController::class, 'stats']);
    });

    Route::prefix('raspored')->group(function () {
        Route::get('/', [RasporedController::class, 'index']);
        Route::get('/today', [RasporedController::class, 'today']);
        Route::get('/{raspored}', [RasporedController::class, 'show']);
    });

    Route::prefix('aktivnost')->group(function () {
        Route::get('/', [AktivnostController::class, 'index']);
        Route::get('/today', [AktivnostController::class, 'today']);
        Route::get('/{aktivnost}', [AktivnostController::class, 'show']);
        Route::get('/moje', [AktivnostController::class, 'myActivities'])->middleware('auth:sanctum');
    });
});
