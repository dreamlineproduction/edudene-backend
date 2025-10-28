<?php

use App\Http\Controllers\Web\AccountActivationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // $mailData = [
    //     'fullName' => 'Gauyram',
    //     'mail' => 'demo@exampe.com',
    //     'resetLink' => 'sajksdkj.com',
    // ];

    // return view('emails.user.password-reset', ['mailData' => $mailData]);

    return view('welcome');
});

Route::get('/user/verify-account', [AccountActivationController::class, 'verifyUserAccount']);
