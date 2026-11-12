<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Community\Http\Controllers\CommunityController;

Route::middleware('web')->prefix('community')->name('community.')->group(function (): void {
    Route::get('/', [CommunityController::class, 'index'])->name('index');
    Route::get('/t/{thread}/{slug?}', [CommunityController::class, 'thread'])->whereNumber('thread')->name('thread');

    Route::middleware('auth')->group(function (): void {
        Route::get('/new', [CommunityController::class, 'create'])->name('create');
        Route::post('/threads', [CommunityController::class, 'store'])->middleware('throttle:5,10')->name('store');
        Route::post('/t/{thread}/reply', [CommunityController::class, 'reply'])->whereNumber('thread')->middleware('throttle:10,1')->name('reply');
        Route::post('/report', [CommunityController::class, 'report'])->middleware('throttle:20,10')->name('report');
    });

    Route::get('/{slug}', [CommunityController::class, 'board'])->name('board');
});
