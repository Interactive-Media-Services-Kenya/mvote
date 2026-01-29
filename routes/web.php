<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [LoginController::class, 'index']);

Route::get('/lineup', function () {
    return Inertia::render('Lineup');
});

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

