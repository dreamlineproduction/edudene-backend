<?php

use App\Http\Controllers\AccountActivationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user/activate-account', [AccountActivationController::class, 'activateUserAccount']);
