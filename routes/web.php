<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::post('/login/identify', [LoginController::class, 'identify']);
    Route::post('/login/verify', [LoginController::class, 'verify']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/lineup', function () {
        return Inertia::render('Lineup');
    })->name('lineup');

    Route::get('/artist/{id}', function ($id) {
        return Inertia::render('ArtistProfile', ['id' => $id]);
    });

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        });
        Route::get('/artists', function () {
            return Inertia::render('Admin/Artists');
        });
        Route::get('/event', function () {
            return Inertia::render('Admin/EventSetup');
        });
        Route::get('/judges', function () {
            return Inertia::render('Admin/Judges');
        });
    });
});

