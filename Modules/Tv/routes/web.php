<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Tv\Http\Controllers\PetOfWeekController;
use Modules\Tv\Http\Controllers\TvController;

Route::middleware('web')->name('tv.')->group(function (): void {
    Route::get('/tv', [TvController::class, 'index'])->name('index');
    Route::post('/tv/{video}/played', [TvController::class, 'played'])->whereNumber('video')->middleware('throttle:30,1')->name('played');
    Route::get('/pet-of-the-week', [PetOfWeekController::class, 'index'])->name('potw');

    Route::middleware('auth')->group(function (): void {
        Route::get('/pet-of-the-week/enter', [PetOfWeekController::class, 'create'])->name('potw.enter');
        Route::post('/pet-of-the-week/enter', [PetOfWeekController::class, 'store'])->middleware('throttle:5,10')->name('potw.store');
        Route::post('/pet-of-the-week/{entry}/vote', [PetOfWeekController::class, 'vote'])->whereNumber('entry')->middleware('throttle:20,1')->name('potw.vote');
    });
});
