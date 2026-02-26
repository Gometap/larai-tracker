<?php

use Gometap\LaraiTracker\Http\Controllers\LaraiAuthController;
use Gometap\LaraiTracker\Http\Controllers\LaraiDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('larai-tracker')->middleware(['web'])->group(function () {

    // Auth routes
    Route::get('/login', [LaraiAuthController::class, 'showLogin'])->name('larai.auth.login');
    Route::post('/login', [LaraiAuthController::class, 'login'])->name('larai.auth.login.submit');
    Route::get('/logout', [LaraiAuthController::class, 'logout'])->name('larai.auth.logout');

    // Protected routes
    Route::middleware('larai.auth')->group(function () {
        Route::get('/', [LaraiDashboardController::class, 'index'])->name('larai.dashboard');
        Route::get('/logs', [LaraiDashboardController::class, 'logs'])->name('larai.logs');
        Route::get('/export/{format}', [LaraiDashboardController::class, 'export'])->name('larai.export');
        Route::get('/settings', [LaraiDashboardController::class, 'settings'])->name('larai.settings');
        Route::post('/settings', [LaraiDashboardController::class, 'updateSettings'])->name('larai.settings.update');
        Route::post('/sync-prices', [LaraiDashboardController::class, 'syncPrices'])->name('larai.sync-prices');
    });
});
