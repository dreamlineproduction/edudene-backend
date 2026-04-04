<?php

use App\Http\Controllers\Api\Admin\PartnerController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'role:5'])->group(function () {
    // Partners CRUD Routes
    Route::apiResource('admin/partners', PartnerController::class);
    
    // Bulk delete partners
    Route::post('admin/partners/bulk-delete', [PartnerController::class, 'bulkDelete']);
});
