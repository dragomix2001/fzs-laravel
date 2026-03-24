<?php

use App\Jobs\TestFailingJob;
use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return view('home');
})->middleware('auth');

Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'App\Http\Controllers\Auth\LoginController@login')->middleware('throttle:5,1');
Route::post('logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => 'FZS Laravel',
        'version' => '1.0.0',
    ]);
})->middleware('cache.headers:public;max_age=60');

Route::get('/test-failed-job', function () {
    dispatch(new TestFailingJob);

    return response()->json(['message' => 'Test failing job dispatched']);
});

require __DIR__.'/fzs-routes.php';
require __DIR__.'/ai-routes.php';
