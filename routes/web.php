<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\JudgeController;
use App\Http\Controllers\LineupController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::post('/login/identify', [LoginController::class, 'identify']);
    Route::post('/login/verify', [LoginController::class, 'verify']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/vote', [VoteController::class, 'store'])->name('vote.submit');

    Route::get('/lineup', [LineupController::class, 'index'])->name('lineup');
    Route::get('/audience', [\App\Http\Controllers\AudienceController::class, 'index'])->name('audience.display');

    Route::get('/artist/{id}', [LineupController::class, 'artist']);

    Route::prefix('admin')->middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/artists', [ArtistController::class, 'index']);
        Route::post('/artists', [ArtistController::class, 'store']);
        Route::post('/artists/{artist}', [ArtistController::class, 'update']);
        Route::delete('/artists/{artist}', [ArtistController::class, 'destroy']);
        Route::get('/event', [EventController::class, 'index']);
        Route::post('/event', [EventController::class, 'store']);
        Route::get('/judges', [JudgeController::class, 'index']);
        Route::post('/judges', [JudgeController::class, 'store']);

        Route::post('/performance/start', [PerformanceController::class, 'start']);
        Route::post('/performance/{performance}/open-voting', [PerformanceController::class, 'openVoting']);
        Route::post('/performance/{performance}/toggle-pause', [PerformanceController::class, 'togglePause']);
        Route::post('/performance/{performance}/adjust-time', [PerformanceController::class, 'adjustTime']);
        Route::post('/performance/{performance}/end', [PerformanceController::class, 'end']);
        Route::post('/performance/{performance}/reset', [PerformanceController::class, 'reset']);

        Route::post('/event/questions', [QuestionController::class, 'store'])->name('admin.questions.sync');
        Route::delete('/event/questions/{question}', [QuestionController::class, 'destroy'])->name('admin.questions.delete');
    });
});
