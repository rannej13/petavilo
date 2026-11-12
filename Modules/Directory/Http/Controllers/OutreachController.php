<?php

declare(strict_types=1);

namespace Modules\Directory\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Directory\Models\Business;
use Modules\Directory\Models\BusinessClaim;
use Modules\Directory\Models\DirectorySuppression;

class OutreachController extends Controller
{
    public function claimForm(Request $request, string $token): View|RedirectResponse
    {
        $business = $this->findByToken($token);

        if (! $request->user()) {
            $request->session()->put('url.intended', $request->fullUrl());

            return view('directory::claim-token', ['business' => $business, 'token' => $token, 'guest' => true]);
        }

        return view('directory::claim-token', ['business' => $business, 'token' => $token, 'guest' => false]);
    }

    public function claim(Request $request, string $token): RedirectResponse
    {
        $business = $this->findByToken($token);

        if ($business->isClaimed()) {
            return redirect()->route('directory.panel.index');
        }

        $userId = (int) $request->user()->getKey();

        $claim = BusinessClaim::query()->create([
            'business_id' => $business->getKey(),
            'user_id' => $userId,
            'role_at_business' => 'Claimed from emailed link',
            'phone' => (string) ($business->getAttribute('phone') ?? ''),
            'message' => 'Verified by the claim link sent to '.$business->getAttribute('email'),
            'status' => BusinessClaim::PENDING,
        ]);

        $claim->approve();
        $business->forceFill(['invite_token' => null])->saveQuietly();

        return redirect()->route('directory.panel.index')->with('success', __('directory::messages.claimed_now'));
    }

    public function unsubscribeForm(string $token): View
    {
        return view('directory::unsubscribe', ['business' => $this->findByToken($token, false), 'token' => $token, 'done' => false]);
    }

    public function unsubscribe(string $token): View
    {
        $business = $this->findByToken($token, false);

        $business->forceFill(['unsubscribed_at' => now()])->saveQuietly();

        if (filled($business->getAttribute('email'))) {
            DirectorySuppression::add((string) $business->getAttribute('email'));
        }

        return view('directory::unsubscribe', ['business' => $business, 'token' => $token, 'done' => true]);
    }

    public function out(Business $business, string $kind): RedirectResponse
    {
        $url = $kind === 'booking' ? $business->bookingUrl() : $business->websiteUrl();

        abort_if($url === null, 404);

        Business::query()->whereKey($business->getKey())->increment('website_clicks');

        return redirect()->away($url);
    }

    private function findByToken(string $token, bool $mustBeUnclaimed = true): Business
    {
        abort_if(strlen($token) < 20, 404);

        $business = Business::query()->where('invite_token', $token)->first();

        abort_if($business === null, 404);

        return $business;
    }
}
