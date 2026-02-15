<?php

use Illuminate\Support\Facades\Route;
use Modules\Sheet\Http\Controllers\SheetController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('sheets', SheetController::class)->names('sheet');
});
