<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ReviewQueueController;
use Modules\Import\Http\Controllers\UploadsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('uploads', UploadsController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::post('uploads/{upload}/retry', [UploadsController::class, 'retry'])->name('uploads.retry');

    Route::get('review-queue', [ReviewQueueController::class, 'index'])->name('review-queue.index');
    Route::post('review-queue/{parsedTransaction}/assign-payment', [ReviewQueueController::class, 'assignPayment'])->name('review-queue.assign-payment');
    Route::post('review-queue/{parsedTransaction}/assign-expense', [ReviewQueueController::class, 'assignExpense'])->name('review-queue.assign-expense');
    Route::post('review-queue/{parsedTransaction}/dismiss', [ReviewQueueController::class, 'dismiss'])->name('review-queue.dismiss');
});
