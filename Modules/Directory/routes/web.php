<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Directory\Http\Controllers\BusinessPanelController;
use Modules\Directory\Http\Controllers\DirectoryController;

Route::middleware('web')->group(function (): void {
    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index');

    Route::middleware('auth')->group(function (): void {
        Route::get('/directory/add', [DirectoryController::class, 'create'])->name('directory.create');
        Route::post('/directory/add', [DirectoryController::class, 'store'])->name('directory.store')->middleware('throttle:5,60');
        Route::post('/directory/{type}/{slug}/claim', [DirectoryController::class, 'claim'])->name('directory.claim')->middleware('throttle:5,60');
        Route::get('/panel/business', [BusinessPanelController::class, 'index'])->name('directory.panel.index');
        Route::put('/panel/business/{business}', [BusinessPanelController::class, 'update'])->name('directory.panel.update');
    });

    Route::get('/directory/{type}', [DirectoryController::class, 'type'])->name('directory.type');
    Route::get('/directory/{type}/{slug}', [DirectoryController::class, 'show'])->name('directory.show');
});
