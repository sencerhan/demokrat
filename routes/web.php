<?php

use App\Http\Controllers\TalepController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TalepController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/talepler.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
