<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\MembershipController;
use Modules\Billing\Http\Controllers\PromotionCheckoutController;

Route::middleware(['web', 'auth'])->prefix('panel')->name('billing.')->group(function (): void {
    Route::get('/promotions/paid', [PromotionCheckoutController::class, 'success'])->name('promotions.success');
    Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
    Route::post('/membership/{plan}', [MembershipController::class, 'checkout'])->name('membership.checkout');
    Route::get('/membership/billing', [MembershipController::class, 'portal'])->name('membership.portal');
});
