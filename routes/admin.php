<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin|superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('users/{user}/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::delete('users/{user}/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    });
