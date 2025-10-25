<?php

use App\Http\Controllers\Web\AccountActivationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user/verify-account', [AccountActivationController::class, 'verifyUserAccount']);
