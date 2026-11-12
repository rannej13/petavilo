<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Guide\Http\Controllers\GuideController;

Route::middleware('web')->group(function (): void {
    Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
    Route::get('/guides/{slug}', [GuideController::class, 'show'])->name('guides.show');
});
