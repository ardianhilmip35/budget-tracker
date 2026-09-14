<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\RequireBudgetPin;
use Illuminate\Support\Facades\Route;

Route::get('/pin', [PinController::class, 'form'])->name('pin.form');
Route::post('/pin', [PinController::class, 'verify'])->name('pin.verify');

Route::middleware(RequireBudgetPin::class)->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [PinController::class, 'logout'])->name('logout');

    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    Route::get('/history', HistoryController::class)->name('history');

    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::post('/settings/active-profile', [ProfileController::class, 'activate'])->name('settings.activate');
    Route::put('/profiles/{profile}', [ProfileController::class, 'update'])->name('profiles.update');

    Route::get('/export/monthly', [ExportController::class, 'monthly'])->name('export.monthly');
    Route::get('/export/history', [ExportController::class, 'history'])->name('export.history');
});
