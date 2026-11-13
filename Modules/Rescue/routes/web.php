<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Rescue\Http\Controllers\AdoptionController;
use Modules\Rescue\Http\Controllers\RescueController;

Route::middleware('web')->name('rescue.')->group(function (): void {
    Route::get('/adopt', [RescueController::class, 'adopt'])->name('adopt');
    Route::get('/rehome', [RescueController::class, 'rehome'])->name('rehome');
    Route::get('/lost-found', [RescueController::class, 'lostFound'])->name('lost-found');
    Route::get('/found-an-animal', [RescueController::class, 'foundAnimal'])->name('found-animal');
    Route::get('/foster', [RescueController::class, 'foster'])->name('foster');
    Route::get('/welfare', [RescueController::class, 'welfare'])->name('welfare');
    Route::post('/welfare/{campaign}', [RescueController::class, 'answer'])->middleware('throttle:20,10')->name('welfare.answer');
    Route::get('/rescues', [RescueController::class, 'rescues'])->name('rescues');

    Route::middleware('auth')->group(function (): void {
        Route::get('/rescues/join', [RescueController::class, 'join'])->name('join');
        Route::post('/rescues/join', [RescueController::class, 'storeJoin'])->middleware('throttle:5,10')->name('join.store');
        Route::get('/adopt/{listing}/apply', [AdoptionController::class, 'create'])->whereNumber('listing')->name('apply');
        Route::post('/adopt/{listing}/apply', [AdoptionController::class, 'store'])->whereNumber('listing')->middleware('throttle:10,10')->name('apply.store');
        Route::get('/panel/rescue', [AdoptionController::class, 'dashboard'])->name('panel');
        Route::post('/panel/rescue/applications/{application}', [AdoptionController::class, 'update'])->whereNumber('application')->name('panel.update');
        Route::get('/panel/adoptions', [AdoptionController::class, 'mine'])->name('panel.mine');
        Route::post('/panel/adoptions/{application}/withdraw', [AdoptionController::class, 'withdraw'])->whereNumber('application')->name('panel.withdraw');
    });

    Route::get('/rescues/{slug}', [RescueController::class, 'show'])->name('show');
});
