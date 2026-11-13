<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\PetTools\Http\Controllers\ClinicTriageController;
use Modules\PetTools\Http\Controllers\PassportController;
use Modules\PetTools\Http\Controllers\ToolsController;

Route::middleware('web')->name('pettools.')->group(function (): void {
    Route::get('/tools', [ToolsController::class, 'index'])->name('index');
    Route::get('/tools/poison-checker', [ToolsController::class, 'poison'])->name('poison');
    Route::get('/tools/food-matcher', [ToolsController::class, 'food'])->name('food');
    Route::get('/tools/calorie-calculator', [ToolsController::class, 'calories'])->name('calories');
    Route::get('/tools/litter-box-problems', [ToolsController::class, 'litter'])->name('litter');
    Route::get('/tools/pet-insurance-or-savings', [ToolsController::class, 'insurance'])->name('insurance');
    Route::get('/tools/dental-and-senior-check', [ToolsController::class, 'dental'])->name('dental');
    Route::post('/tools/vet-check-request', [ToolsController::class, 'storeLead'])->middleware('throttle:5,10')->name('lead');
    Route::get('/tools/cat-pain-check', [ToolsController::class, 'painCheck'])->name('pain');
    Route::get('/tools/pet-passport', [ToolsController::class, 'passportAbout'])->name('passport.about');
    Route::get('/for-vets', [ClinicTriageController::class, 'forVets'])->name('clinic.landing');
    Route::get('/widget/triage/{token}', [ClinicTriageController::class, 'widget'])->where('token', '[A-Za-z0-9]{32}')->name('widget');
    Route::post('/widget/triage/{token}', [ClinicTriageController::class, 'submit'])->where('token', '[A-Za-z0-9]{32}')->middleware('throttle:10,10')->name('widget.submit');
    Route::middleware('auth')->group(function (): void {
        Route::get('/panel/clinic-triage', [ClinicTriageController::class, 'dashboard'])->name('clinic.dashboard');
        Route::post('/panel/clinic-triage', [ClinicTriageController::class, 'save'])->name('clinic.save');
    });
    Route::get('/pass/{token}', [PassportController::class, 'pass'])->where('token', '[A-Za-z0-9]{32}')->name('pass');

    Route::middleware('auth')->prefix('panel/pets')->name('passport.')->group(function (): void {
        Route::get('/', [PassportController::class, 'index'])->name('index');
        Route::get('/new', [PassportController::class, 'create'])->name('create');
        Route::post('/', [PassportController::class, 'store'])->name('store');
        Route::get('/{pet}', [PassportController::class, 'show'])->whereNumber('pet')->name('show');
        Route::get('/{pet}/edit', [PassportController::class, 'edit'])->whereNumber('pet')->name('edit');
        Route::put('/{pet}', [PassportController::class, 'update'])->whereNumber('pet')->name('update');
        Route::delete('/{pet}', [PassportController::class, 'destroy'])->whereNumber('pet')->name('destroy');
        Route::post('/{pet}/records', [PassportController::class, 'storeRecord'])->whereNumber('pet')->name('records.store');
        Route::delete('/{pet}/records/{record}', [PassportController::class, 'destroyRecord'])->whereNumber('pet')->whereNumber('record')->name('records.destroy');
        Route::post('/{pet}/pass', [PassportController::class, 'updatePass'])->whereNumber('pet')->name('pass.update');
        Route::get('/{pet}/sitter-sheet', [PassportController::class, 'sitterSheet'])->whereNumber('pet')->name('sitter');
        Route::post('/{pet}/missing', [PassportController::class, 'toggleMissing'])->whereNumber('pet')->name('missing');
    });
});
