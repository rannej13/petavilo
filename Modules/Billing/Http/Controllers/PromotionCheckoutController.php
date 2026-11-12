<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Support\PaymentGateway;
use Modules\Notification\Models\UserNotification;

class PromotionCheckoutController extends Controller
{
    public function success(Request $request): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id', '');
        $session = $sessionId !== '' ? PaymentGateway::retrieveSession($sessionId) : null;

        if ($session === null || (int) ($session->metadata['user_id'] ?? 0) !== (int) $request->user()->getKey()) {
            return redirect()->route('panel.promotions.index')->with('error', __('billing::messages.payment_not_found'));
        }

        $order = PaymentGateway::fulfilPromotion($session);

        if ($order === null) {
            return redirect()->route('panel.promotions.index')->with('error', __('billing::messages.payment_pending'));
        }

        if ($order->wasRecentlyCreated) {
            UserNotification::publish(
                (int) $request->user()->getKey(),
                UserNotification::TYPE_PROMOTION,
                __('promotion::messages.notification_title'),
                __('promotion::messages.notification_body', [
                    'plan' => (string) $order->getAttribute('plan_name'),
                    'days' => $order->remainingDays(),
                ]),
                route('panel.promotions.index'),
            );
        }

        return redirect()->route('panel.promotions.index')->with('success', __('billing::messages.payment_received'));
    }
}
