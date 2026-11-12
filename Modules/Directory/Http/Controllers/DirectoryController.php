<?php

declare(strict_types=1);

namespace Modules\Directory\Http\Controllers;

use App\Support\MembershipDirectory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\CatMarket\Support\CatMarketSettings;
use Modules\Directory\Models\Business;
use Modules\Directory\Models\BusinessClaim;

class DirectoryController extends Controller
{
    public function index(): View
    {
        $region = CatMarketSettings::visitorCountry();

        return view('directory::index', [
            'types' => Business::types(),
            'counts' => Business::countsByType($region),
            'featured' => Business::featured(6, $region),
            'region' => $region,
        ]);
    }

    public function type(Request $request, string $type): View
    {
        abort_unless(array_key_exists($type, Business::types()), 404);

        $town = trim((string) $request->query('town', ''));
        $search = trim(mb_substr((string) $request->query('q', ''), 0, 80));
        $region = CatMarketSettings::visitorCountry();

        return view('directory::type', [
            'type' => $type,
            'typeName' => Business::typeName($type),
            'types' => Business::types(),
            'town' => $town,
            'search' => $search,
            'towns' => Business::towns($region, $type),
            'businesses' => Business::browse($type, $town, $search, $region),
            'region' => $region,
        ]);
    }

    public function show(Request $request, string $type, string $slug): View
    {
        $business = Business::findPublished($type, $slug);

        abort_if($business === null, 404);

        $business->trackView();

        return view('directory::show', [
            'business' => $business,
            'typeName' => Business::typeName($type),
            'hasPendingClaim' => $request->user() !== null && BusinessClaim::hasPending((int) $business->getKey(), (int) $request->user()->getKey()),
        ]);
    }

    public function create(): View
    {
        return view('directory::create', ['types' => Business::types(), 'countries' => Business::countries(), 'region' => CatMarketSettings::visitorCountry()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(array_keys(Business::types()))],
            'country' => ['required', Rule::in(array_keys(Business::countries()))],
            'town' => ['required', 'string', 'max:80'],
            'postcode' => ['nullable', 'string', 'max:12'],
            'address_line' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'website' => ['nullable', 'string', 'max:200'],
            'licence_number' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        $business = new Business($data + ['owner_user_id' => (int) $request->user()->getKey(), 'is_published' => false]);
        $business->save();

        Business::syncTierForOwner((int) $request->user()->getKey(), MembershipDirectory::directoryTierFor((int) $request->user()->getKey()));

        return redirect()->route('directory.panel.index')->with('success', __('directory::messages.submitted'));
    }

    public function claim(Request $request, string $type, string $slug): RedirectResponse
    {
        $business = Business::findPublished($type, $slug);

        abort_if($business === null, 404);

        if ($business->isClaimed()) {
            return back()->with('error', __('directory::messages.already_claimed'));
        }

        $data = $request->validate([
            'role_at_business' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = (int) $request->user()->getKey();

        if (! BusinessClaim::hasPending((int) $business->getKey(), $userId)) {
            BusinessClaim::query()->create($data + [
                'business_id' => $business->getKey(),
                'user_id' => $userId,
                'status' => BusinessClaim::PENDING,
            ]);
        }

        return back()->with('success', __('directory::messages.claim_received'));
    }
}
