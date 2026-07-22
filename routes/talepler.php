<?php

use App\Http\Controllers\TalepController;
use App\Http\Controllers\TalepVoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('talepler', [TalepController::class, 'store'])->name('talepler.store');
    Route::patch('talepler/{talep}', [TalepController::class, 'update'])->name('talepler.update');
    Route::patch('talepler/{talep}/status', [TalepController::class, 'updateStatus'])->name('talepler.status.update');
    Route::delete('talepler/{talep}', [TalepController::class, 'destroy'])->name('talepler.destroy');
    Route::post('talepler/{talep}/votes', [TalepVoteController::class, 'store'])->name('talepler.votes.store');
});
