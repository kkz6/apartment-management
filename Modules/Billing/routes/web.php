<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\ChargesController;
use Modules\Billing\Http\Controllers\ExpensesController;
use Modules\Billing\Http\Controllers\GenerateChargesController;
use Modules\Billing\Http\Controllers\PaymentsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('charges', ChargesController::class)->except(['show']);
    Route::resource('payments', PaymentsController::class)->except(['show']);
    Route::resource('expenses', ExpensesController::class)->except(['show']);
    Route::get('generate-charges', [GenerateChargesController::class, 'create'])->name('charges.generate');
    Route::post('generate-charges', [GenerateChargesController::class, 'store'])->name('charges.generate.store');
});
