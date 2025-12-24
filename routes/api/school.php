<?php


use App\Http\Controllers\Api\School\SchoolAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('school', [SchoolAuthController::class, 'store']);
    Route::post('school/login', [SchoolAuthController::class, 'login']);
    Route::post('school/register', [SchoolAuthController::class, 'register']);


});

//->middleware(['auth:sanctum','role:3'])
Route::prefix('v1')->group(function () {   
    
});
?>