<?php

use Gometap\LaraiTracker\Http\Controllers\LaraiDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('larai-tracker')->middleware(['web'])->group(function () {
    Route::get('/', [LaraiDashboardController::class, 'index'])->name('larai.dashboard');
});
