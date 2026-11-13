<?php

declare(strict_types=1);

namespace Modules\Rescue\Http\Controllers;

use App\Support\SpamGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Rescue\Models\Donation;
use Modules\Rescue\Models\RescueOrganisation;
use Modules\Rescue\Support\DonationGateway;
use Throwable;

class DonationController extends Controller
{
    public function donate(Request $request, string $slug): RedirectResponse
    {
        $organisation = RescueOrganisation::query()->where('slug', $slug)->firstOrFail();

        abort_unless($organisation->acceptsDonations() && DonationGateway::isConfigured(), 404);
        SpamGuard::check($request, 'amount');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.config('rescue.donation_min', 2), 'max:'.config('rescue.donation_max', 5000)],
            'cover_fees' => ['nullable', 'boolean'],
            'donor_name' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:300'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $amount = round((float) $data['amount'], 2);
        $currency = $organisation->currency();

        $donation = new Donation();
        $donation->fill([
            'amount' => $amount,
            'fee_cover' => $request->boolean('cover_fees') ? Donation::feeCoverFor($amount, $currency) : 0,
            'currency' => $currency,
            'donor_name' => $data['donor_name'] ?? null,
            'message' => $data['message'] ?? null,
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);
        $donation->forceFill([
            'organisation_id' => $organisation->getKey(),
            'user_id' => $request->user()?->getKey(),
            'status' => Donation::STATUS_PENDING,
        ])->save();

        try {
            return redirect()->away(DonationGateway::checkout($donation, $organisation));
        } catch (Throwable $exception) {
            report($exception);
            $donation->forceFill(['status' => Donation::STATUS_FAILED])->save();

            return back()->with('error', 'Card payments are not available for this rescue right now. Please try again later.');
        }
    }

    public function thanks(Request $request, int $donation): View
    {
        $record = Donation::query()->with('organisation')->findOrFail($donation);

        abort_unless(hash_equals((string) $record->getAttribute('stripe_session_id'), (string) $request->query('session_id', '')), 404);
        DonationGateway::sync($record);

        return view('rescue::donation-thanks', ['donation' => $record->refresh()->load('organisation')]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $organisation = $this->ownOrganisation($request);

        abort_unless($organisation->isVerified(), 403);

        if (! DonationGateway::isConfigured()) {
            return back()->with('error', 'Card payments are not switched on for the site yet.');
        }

        try {
            return redirect()->away(DonationGateway::onboardingUrl($organisation, (string) ($organisation->getAttribute('contact_email') ?: $request->user()->email)));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'We could not start Stripe set-up right now. Please try again later or contact us.');
        }
    }

    public function connectReturn(Request $request): RedirectResponse
    {
        $organisation = $this->ownOrganisation($request);
        DonationGateway::refreshAccount($organisation);

        return redirect()->route('rescue.panel')->with(
            $organisation->getAttribute('payouts_ready') ? 'success' : 'error',
            $organisation->getAttribute('payouts_ready')
                ? 'Donations are switched on. A Donate button now shows on your rescue page.'
                : 'Stripe still needs some details before donations can start. Use Continue set-up when you are ready.'
        );
    }

    public function connectRefresh(Request $request): RedirectResponse
    {
        return $this->connect($request);
    }

    public function mine(Request $request): View
    {
        return view('rescue::panel.donations', [
            'donations' => Donation::query()->paid()->where('user_id', $request->user()->getKey())->with('organisation:id,name,slug')->latest()->get(),
        ]);
    }

    private function ownOrganisation(Request $request): RescueOrganisation
    {
        $organisation = RescueOrganisation::forUser((int) $request->user()->getKey());

        abort_if($organisation === null, 404);

        return $organisation;
    }
}
