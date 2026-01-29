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
