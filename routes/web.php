<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\JudgeController;
use App\Http\Controllers\LineupController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::post('/login/identify', [LoginController::class, 'identify']);
    Route::post('/login/verify', [LoginController::class, 'verify']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/lineup', [LineupController::class, 'index'])->name('lineup');

    Route::get('/artist/{id}', [LineupController::class, 'artist']);

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        });
        Route::get('/artists', [ArtistController::class, 'index']);
        Route::get('/event', [EventController::class, 'index']);
        Route::post('/event', [EventController::class, 'store']);
        Route::get('/judges', [JudgeController::class, 'index']);
        Route::post('/judges', [JudgeController::class, 'store']);

        Route::post('/event/questions', [QuestionController::class, 'store'])->name('admin.questions.sync');
        Route::delete('/event/questions/{question}', [QuestionController::class, 'destroy'])->name('admin.questions.delete');
    });
});

