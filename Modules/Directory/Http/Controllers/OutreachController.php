<?php

declare(strict_types=1);

namespace Modules\Directory\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Directory\Mail\ClaimInvitation;
use Modules\Directory\Support\OutreachPolicy;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $limit = max(1, (int) config('directory.outreach.daily_limit', 30));
        $rows = [];

        foreach (OutreachPolicy::candidates()->cursor() as $business) {
            if (count($rows) >= $limit) {
                break;
            }

            if (OutreachPolicy::reasonToSkip($business) !== null) {
                $business->forceFill(['invite_count' => 99])->saveQuietly();

                continue;
            }

            if (blank($business->getAttribute('invite_token'))) {
                $business->forceFill(['invite_token' => Str::random(40)])->saveQuietly();
            }

            $isFollowUp = (int) $business->getAttribute('invite_count') > 0;
            $mail = new ClaimInvitation($business, $isFollowUp);
            $content = $mail->content();

            $rows[] = [
                (string) $business->getAttribute('email'),
                (string) $business->getAttribute('name'),
                (string) $business->getAttribute('country'),
                $mail->envelope()->subject,
                trim(view((string) $content->text, $content->with)->render()),
            ];

            $business->forceFill([
                'invited_at' => now(),
                'invite_count' => (int) $business->getAttribute('invite_count') + 1,
            ])->saveQuietly();
        }

        return response()->streamDownload(static function () use ($rows): void {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['email', 'business', 'country', 'subject', 'message']);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, 'petavilo-invites-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function findByToken(string $token, bool $mustBeUnclaimed = true): Business
    {
        abort_if(strlen($token) < 20, 404);

        $business = Business::query()->where('invite_token', $token)->first();

        abort_if($business === null, 404);

        return $business;
    }
}
