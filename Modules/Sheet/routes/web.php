<?php

use Illuminate\Support\Facades\Route;
use Modules\Sheet\Http\Controllers\SheetController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sheets', SheetController::class)->names('sheet');
});
