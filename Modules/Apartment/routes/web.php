<?php

use Illuminate\Support\Facades\Route;
use Modules\Apartment\Http\Controllers\MaintenanceSlabsController;
use Modules\Apartment\Http\Controllers\ResidentsController;
use Modules\Apartment\Http\Controllers\UnitsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('units', UnitsController::class)->except(['show']);
    Route::resource('residents', ResidentsController::class)->except(['show']);
    Route::resource('maintenance-slabs', MaintenanceSlabsController::class)->except(['show']);
});
