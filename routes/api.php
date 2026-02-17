<?php

use App\Http\Controllers\Api\CalculationController;
use Illuminate\Support\Facades\Route;

Route::prefix('calculations')->group(function () {
    Route::get('/', [CalculationController::class, 'index'])->name('calculations.index');
    Route::post('/', [CalculationController::class, 'store'])->name('calculations.store');
    Route::delete('/{calculation}', [CalculationController::class, 'destroy'])->name('calculations.destroy');
    Route::delete('/', [CalculationController::class, 'clearAll'])->name('calculations.clearAll');
});
