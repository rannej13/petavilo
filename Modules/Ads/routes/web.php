<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Ads\Http\Controllers\AdsTxtController;

Route::get('/ads.txt', AdsTxtController::class)->name('ads.txt');
