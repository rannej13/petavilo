<?php

declare(strict_types=1);

namespace Modules\Rescue\Http\Controllers;

use App\Support\SpamGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Listing\Models\Listing;
use Modules\Notification\Models\UserNotification;
use Modules\Rescue\Models\AdoptionApplication;
use Modules\Rescue\Models\RescueOrganisation;

class AdoptionController extends Controller
{
    public function create(int $listing): View
    {
        [$record, $organisation] = $this->adoptable($listing);

        return view('rescue::apply', [
            'listing' => $record,
            'organisation' => $organisation,
            'questions' => AdoptionApplication::questions(),
        ]);
    }

    public function store(Request $request, int $listing): RedirectResponse
    {
        [$record, $organisation] = $this->adoptable($listing);
        SpamGuard::check($request, 'message');

        abort_if((int) $organisation->getAttribute('user_id') === (int) $request->user()->getKey(), 403);

        $rules = ['message' => ['required', 'string', 'min:30', 'max:3000']];

        foreach (AdoptionApplication::questions() as $key => $question) {
            $rules['answers.'.$key] = ['required', Rule::in($question['options'])];
        }

        $data = $request->validate($rules);
        $application = AdoptionApplication::submit($organisation, (int) $record->getKey(), (int) $request->user()->getKey(), $data['answers'], $data['message']);

        if ($organisation->getAttribute('user_id') !== null) {
            UserNotification::publish(
                (int) $organisation->getAttribute('user_id'),
                UserNotification::TYPE_LISTING,
                'New adoption application',
                'For '.(string) $record->getAttribute('title'),
                route('rescue.panel'),
            );
        }

        return redirect()->route('rescue.panel.mine')->with('success', 'Application sent to '.$organisation->getAttribute('name').'. They will reply through the site. Reference #'.$application->getKey().'.');
    }

    public function dashboard(Request $request): View
    {
        $organisation = RescueOrganisation::forUser((int) $request->user()->getKey());

        if ($organisation !== null && filled($organisation->getAttribute('stripe_account_id')) && ! $organisation->getAttribute('payouts_ready')) {
            \Modules\Rescue\Support\DonationGateway::refreshAccount($organisation);
        }

        $applications = $organisation === null ? collect() : AdoptionApplication::query()
            ->where('organisation_id', $organisation->getKey())
            ->with('applicant:id,name')
            ->latest()
            ->limit(100)
            ->get();

        return view('rescue::panel.dashboard', [
            'organisation' => $organisation,
            'applications' => $applications,
            'listingTitles' => $this->titles($applications->pluck('listing_id')->all()),
            'donationTotals' => $organisation === null ? ['count' => 0, 'sum' => 0.0, 'currency' => 'GBP'] : \Modules\Rescue\Models\Donation::totalsFor($organisation),
        ]);
    }

    public function update(Request $request, int $application): RedirectResponse
    {
        $record = AdoptionApplication::query()->findOrFail($application);
        $organisation = RescueOrganisation::forUser((int) $request->user()->getKey());

        abort_unless($organisation !== null && (int) $record->getAttribute('organisation_id') === (int) $organisation->getKey(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([AdoptionApplication::STATUS_REVIEWING, AdoptionApplication::STATUS_MORE_INFO, AdoptionApplication::STATUS_APPROVED, AdoptionApplication::STATUS_DECLINED])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $record->moveTo($data['status'], (int) $request->user()->getKey(), $data['note'] ?? null);

        UserNotification::publish(
            (int) $record->getAttribute('applicant_id'),
            UserNotification::TYPE_LISTING,
            'Adoption application update: '.$record->statusLabel(),
            $data['note'] ?? null,
            route('rescue.panel.mine'),
        );

        return back()->with('success', 'Application updated and the applicant has been told.');
    }

    public function mine(Request $request): View
    {
        $applications = AdoptionApplication::query()
            ->forApplicant((int) $request->user()->getKey())
            ->with('organisation:id,name,slug')
            ->latest()
            ->get();

        return view('rescue::panel.mine', [
            'applications' => $applications,
            'listingTitles' => $this->titles($applications->pluck('listing_id')->all()),
        ]);
    }

    public function withdraw(Request $request, int $application): RedirectResponse
    {
        $record = AdoptionApplication::query()->forApplicant((int) $request->user()->getKey())->findOrFail($application);
        $record->moveTo(AdoptionApplication::STATUS_WITHDRAWN, (int) $request->user()->getKey(), null);

        return back()->with('success', 'Application withdrawn.');
    }

    private function adoptable(int $listingId): array
    {
        $listing = Listing::query()->active()->findOrFail($listingId);
        $organisation = RescueOrganisation::verifiedForUser($listing->getAttribute('user_id') === null ? null : (int) $listing->getAttribute('user_id'));

        abort_if($organisation === null, 404);

        return [$listing, $organisation];
    }

    private function titles(array $ids): array
    {
        return Listing::query()->withTrashed()->whereIn('id', $ids === [] ? [0] : $ids)->get(['id', 'title', 'slug'])
            ->mapWithKeys(static fn (Listing $listing): array => [(int) $listing->getKey() => ['title' => (string) $listing->getAttribute('title'), 'slug' => (string) $listing->getAttribute('slug')]])
            ->all();
    }
}
