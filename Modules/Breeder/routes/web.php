<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Breeder\Http\Controllers\BreederController;
use Modules\Breeder\Http\Controllers\HandoverController;

Route::middleware('web')->group(function (): void {
    Route::view('/for-breeders', 'breeder::landing')->name('breeder.landing');
    Route::get('/handover/{token}', [HandoverController::class, 'show'])->where('token', '[A-Za-z0-9]{40}')->name('breeder.handover');
    Route::post('/handover/{token}', [HandoverController::class, 'claim'])->where('token', '[A-Za-z0-9]{40}')->middleware(['auth', 'throttle:10,1'])->name('breeder.handover.claim');

    Route::middleware('auth')->prefix('panel/breeder')->name('breeder.')->group(function (): void {
        Route::get('/', [BreederController::class, 'dashboard'])->name('dashboard');
        Route::get('/animals/new', [BreederController::class, 'createAnimal'])->name('animals.create');
        Route::post('/animals', [BreederController::class, 'storeAnimal'])->name('animals.store');
        Route::get('/animals/{animal}/edit', [BreederController::class, 'editAnimal'])->whereNumber('animal')->name('animals.edit');
        Route::put('/animals/{animal}', [BreederController::class, 'updateAnimal'])->whereNumber('animal')->name('animals.update');
        Route::get('/litters/new', [BreederController::class, 'createLitter'])->name('litters.create');
        Route::post('/litters', [BreederController::class, 'storeLitter'])->name('litters.store');
        Route::get('/litters/{litter}', [BreederController::class, 'showLitter'])->whereNumber('litter')->name('litters.show');
        Route::get('/litters/{litter}/edit', [BreederController::class, 'editLitter'])->whereNumber('litter')->name('litters.edit');
        Route::put('/litters/{litter}', [BreederController::class, 'updateLitter'])->whereNumber('litter')->name('litters.update');
        Route::post('/litters/{litter}/offspring', [BreederController::class, 'addOffspring'])->whereNumber('litter')->name('offspring.add');
        Route::post('/litters/{litter}/weights', [BreederController::class, 'storeWeights'])->whereNumber('litter')->name('weights.store');
        Route::post('/litters/{litter}/records', [BreederController::class, 'storeRecord'])->whereNumber('litter')->name('records.store');
        Route::put('/offspring/{offspring}', [BreederController::class, 'updateOffspring'])->whereNumber('offspring')->name('offspring.update');
        Route::post('/offspring/{offspring}/handover', [BreederController::class, 'startHandover'])->whereNumber('offspring')->name('handover.start');
        Route::get('/offspring/{offspring}/pack', [BreederController::class, 'pack'])->whereNumber('offspring')->name('pack');
        Route::post('/waitlist', [BreederController::class, 'storeWaitlist'])->name('waitlist.store');
        Route::put('/waitlist/{entry}', [BreederController::class, 'updateWaitlist'])->whereNumber('entry')->name('waitlist.update');
        Route::get('/register', [BreederController::class, 'register'])->name('register');
        Route::get('/register.csv', [BreederController::class, 'registerCsv'])->name('register.csv');
    });
});
