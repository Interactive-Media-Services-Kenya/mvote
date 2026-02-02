<?php

use App\Http\Controllers\STKPushController;
use Illuminate\Support\Facades\Route;

Route::post('/initiate-stk-push', [STKPushController::class, 'initiateSTK']);
Route::get('testing', function () {
    return 'Yay!';
});
