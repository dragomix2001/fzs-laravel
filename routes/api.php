<?php

use App\Http\Controllers\Api\AktivnostController;
use App\Http\Controllers\Api\ApiIspitController;
use App\Http\Controllers\Api\ApiKandidatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ObavestenjeController;
use App\Http\Controllers\Api\PredictionController;
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
    // Public endpoints with rate limiting
    Route::middleware('throttle:60,1')->group(function () {
        Route::apiResources([
            'kandidati' => ApiKandidatController::class,
            'ispiti' => ApiIspitController::class,
        ]);

        Route::prefix('auth')->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
        });

        Route::get('/obavestenja/javna', [ObavestenjeController::class, 'javna']);

        Route::prefix('raspored')->group(function () {
            Route::get('/', [RasporedController::class, 'index']);
            Route::get('/today', [RasporedController::class, 'today']);
            Route::get('/{raspored}', [RasporedController::class, 'show']);
        });

        Route::prefix('aktivnost')->group(function () {
            Route::get('/', [AktivnostController::class, 'index']);
            Route::get('/today', [AktivnostController::class, 'today']);
            Route::get('/{aktivnost}', [AktivnostController::class, 'show']);
        });
    });

    // Protected endpoints
    Route::middleware('auth:sanctum', 'throttle:120,1')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        Route::get('/obavestenja', [ObavestenjeController::class, 'index']);
        Route::get('/obavestenja/{obavestenje}', [ObavestenjeController::class, 'show']);

        Route::prefix('student')->group(function () {
            Route::get('/profile', [StudentController::class, 'profile']);
            Route::get('/ispiti', [StudentController::class, 'polozeniIspiti']);
            Route::get('/prijave', [StudentController::class, 'prijave']);
            Route::get('/upis', [StudentController::class, 'upis']);
            Route::get('/stats', [StudentController::class, 'stats']);
        });

        Route::prefix('aktivnost')->group(function () {
            Route::get('/moje', [AktivnostController::class, 'myActivities']);
        });

        Route::prefix('prediction')->group(function () {
            Route::get('/student/{id}', [PredictionController::class, 'studentPrediction']);
            Route::get('/statistics', [PredictionController::class, 'statistics']);
        });
    });
});
