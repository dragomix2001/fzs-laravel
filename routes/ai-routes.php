<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\PredictionController;

Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
Route::post('/chatbot/chat', [ChatbotController::class, 'chat'])->name('chatbot.chat');
Route::post('/chatbot/clear', [ChatbotController::class, 'clearHistory'])->name('chatbot.clear');
Route::post('/chatbot/quick', [ChatbotController::class, 'quickQuestion'])->name('chatbot.quick');

Route::get('/prediction', [PredictionController::class, 'index'])->name('prediction.index');
Route::get('/prediction/student/{id}', [PredictionController::class, 'studentPrediction'])->name('prediction.student');
Route::get('/prediction/statistics', [PredictionController::class, 'classStatistics'])->name('prediction.statistics');
Route::get('/api/prediction/student/{id}', [PredictionController::class, 'apiStudentPrediction'])->name('api.prediction.student');
Route::get('/api/prediction/statistics', [PredictionController::class, 'apiClassStatistics'])->name('api.prediction.statistics');
