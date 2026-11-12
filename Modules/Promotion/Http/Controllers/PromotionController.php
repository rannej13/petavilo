<?php

declare(strict_types=1);

namespace Modules\Promotion\Http\Controllers;

use App\Support\ListingDirectory;
use App\Support\MembershipDirectory;
use Laravel\Cashier\Checkout;
use Modules\Billing\Support\PaymentGateway;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notification\Models\UserNotification;
use Modules\Promotion\Models\PromotionOrder;
use Modules\Promotion\Models\PromotionPlan;

class PromotionController extends Controller
{
    public function plans(): View
    {
        return view('promotion::plans', [
            'plans' => PromotionPlan::catalog(),
        ]);
    }

    public function index(Request $request): View
    {
        $userId = (int) $request->user()->getKey();
        $orders = PromotionOrder::paginateForUser($userId);

        return view('promotion::index', [
            'orders' => $orders,
            'plans' => PromotionPlan::catalog(),
            'listings' => ListingDirectory::resolve(
                $orders->getCollection()->map(static fn (PromotionOrder $order): int => $order->listingId())->all()
            ),
            'activeCount' => PromotionOrder::activeCountForUser($userId),
        ]);
    }

    public function store(Request $request): RedirectResponse|Checkout
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'integer', 'min:1'],
            'plan' => ['required', 'string', 'max:64'],
        ]);

        return $this->promote($request, (int) $validated['listing_id'], (string) $validated['plan']);
    }

    public function start(Request $request, int $listing, string $plan): RedirectResponse|Checkout
    {
        return $this->promote($request, $listing, $plan);
    }

    private function promote(Request $request, int $listingId, string $planSlug): RedirectResponse|Checkout
    {
        $plan = PromotionPlan::findActiveBySlug($planSlug);

        if ($plan === null) {
            abort(404);
        }

        $userId = (int) $request->user()->getKey();

        abort_unless(ListingDirectory::sellerIdFor($listingId) === $userId, 403);

        $reference = null;

        if ((float) $plan->getAttribute('price') > 0) {
            if ($this->canUseBreederCredit($plan, $userId)) {
                $reference = PaymentGateway::creditReference();
            } elseif (PaymentGateway::isConfigured()) {
                return PaymentGateway::promotionCheckout($request->user(), $plan, $listingId);
            } else {
                return redirect()->route('panel.promotions.index')->with('error', __('billing::messages.payments_unavailable'));
            }
        }

        $order = PromotionOrder::open($plan, $userId, $listingId, $reference);

        UserNotification::publish(
            $userId,
            UserNotification::TYPE_PROMOTION,
            __('promotion::messages.notification_title'),
            __('promotion::messages.notification_body', [
                'plan' => (string) $plan->getAttribute('name'),
                'days' => $order->remainingDays(),
            ]),
            route('panel.promotions.index'),
        );

        return redirect()
            ->route('panel.promotions.index')
            ->with('success', __('promotion::messages.activated'));
    }

    private function canUseBreederCredit(PromotionPlan $plan, int $userId): bool
    {
        if ((string) $plan->getAttribute('slug') !== (string) config('billing.breeder_credit_plan')) {
            return false;
        }

        if (! MembershipDirectory::isBreederMember($userId)) {
            return false;
        }

        return PromotionOrder::countFreeRedemptionsThisMonth($userId, (int) $plan->getKey())
            < (int) config('billing.breeder_credits_per_month', 0);
    }
}
