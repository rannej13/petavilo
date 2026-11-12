<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Cashier\Checkout;
use Modules\Billing\Support\MembershipCatalog;
use Modules\Billing\Support\PaymentGateway;
use Modules\CatMarket\Support\CatMarketSettings;
use Symfony\Component\HttpFoundation\Response;

class MembershipController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('billing::membership', [
            'plans' => collect(MembershipCatalog::all())->map(static fn (array $plan, string $key): array => $plan + ['key' => $key]),
            'activeKeys' => MembershipCatalog::activeKeys($user),
            'paymentsReady' => PaymentGateway::isConfigured(),
            'hasStripeCustomer' => $user->hasStripeId(),
            'currency' => self::currency($request),
        ]);
    }

    public function checkout(Request $request, string $plan): Checkout|RedirectResponse
    {
        $definition = MembershipCatalog::find($plan);

        abort_if($definition === null, 404);

        $currency = self::currency($request);

        if (! MembershipCatalog::isPurchasable($definition, $currency)) {
            return back()->with('error', __('billing::messages.payments_unavailable'));
        }

        $user = $request->user();
        $type = (string) $definition['type'];
        $priceId = (string) MembershipCatalog::priceIdFor($definition, $currency);
        $current = $user->subscription($type);

        if ($current !== null && $current->valid()) {
            if (! $current->hasPrice($priceId)) {
                $current->swap($priceId);
            }

            return redirect()->route('billing.membership.index')->with('success', __('billing::messages.membership_updated'));
        }

        return $user->newSubscription($type, $priceId)->checkout([
            'success_url' => route('billing.membership.index').'?welcome=1',
            'cancel_url' => route('billing.membership.index'),
            'metadata' => ['membership' => (string) $definition['key']],
        ]);
    }

    public function portal(Request $request): Response
    {
        abort_unless($request->user()->hasStripeId(), 404);

        return $request->user()->redirectToBillingPortal(route('billing.membership.index'));
    }

    private static function currency(Request $request): string
    {
        $requested = strtoupper((string) $request->input('currency', ''));

        if (in_array($requested, ['GBP', 'USD'], true)) {
            return $requested;
        }

        return CatMarketSettings::visitorCountry() === 'US' ? 'USD' : 'GBP';
    }
}
