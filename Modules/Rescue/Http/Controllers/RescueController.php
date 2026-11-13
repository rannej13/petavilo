<?php

declare(strict_types=1);

namespace Modules\Rescue\Http\Controllers;

use App\Support\SpamGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\CatMarket\Support\CatMarketSettings;
use Modules\Rescue\Models\RescueOrganisation;
use Modules\Rescue\Models\WelfareResponse;
use Modules\Rescue\Support\RescueListings;
use Modules\Rescue\Support\WelfareCampaigns;

class RescueController extends Controller
{
    public function adopt(Request $request): View
    {
        $species = $this->species($request);

        return view('rescue::adopt', [
            'species' => $species,
            'filters' => RescueListings::speciesFilters(),
            'listings' => RescueListings::adoptable($species),
            'privateListings' => RescueListings::privateRehoming($species),
            'rehomingCategoryId' => RescueListings::rehomingCategoryId($species),
            'rescueCount' => RescueOrganisation::query()->verified()->inCountry($this->country())->count(),
        ]);
    }

    public function rehome(): View
    {
        return view('rescue::rehome', [
            'rescues' => RescueOrganisation::query()->verified()->inCountry($this->country())
                ->where('takes_rehoming_requests', true)->orderBy('name')->limit(12)->get(),
        ]);
    }

    public function lostFound(Request $request): View
    {
        $type = in_array($request->query('type'), ['lost', 'found'], true) ? (string) $request->query('type') : null;
        $species = array_key_exists((string) $request->query('species'), RescueListings::lostFoundSpeciesLabels()) ? (string) $request->query('species') : null;

        return view('rescue::lost-found', [
            'type' => $type,
            'species' => $species,
            'filters' => RescueListings::lostFoundSpeciesLabels(),
            'listings' => RescueListings::lostAndFound($type, $species),
        ]);
    }

    public function foundAnimal(): View
    {
        return view('rescue::found-animal', ['country' => $this->country()]);
    }

    public function foster(): View
    {
        return view('rescue::foster', [
            'rescues' => RescueOrganisation::query()->verified()->inCountry($this->country())
                ->where('accepts_fosters', true)->orderBy('name')->limit(24)->get(),
        ]);
    }

    public function rescues(Request $request): View
    {
        $species = $this->species($request);

        return view('rescue::rescues', [
            'species' => $species,
            'filters' => RescueOrganisation::speciesOptions(),
            'rescues' => RescueOrganisation::query()->verified()->inCountry($this->country())
                ->caringFor($species)->orderBy('name')->paginate(24)->withQueryString(),
            'mine' => RescueOrganisation::forUser($request->user()?->getKey() === null ? null : (int) $request->user()->getKey()),
        ]);
    }

    public function show(string $slug): View
    {
        $organisation = RescueOrganisation::query()->where('slug', $slug)->firstOrFail();

        abort_unless($organisation->isVerified() || (int) auth()->id() === (int) $organisation->getAttribute('user_id'), 404);

        return view('rescue::show', [
            'organisation' => $organisation,
            'listings' => RescueListings::forOrganisation($organisation),
        ]);
    }

    public function join(Request $request): View
    {
        return view('rescue::join', [
            'organisation' => RescueOrganisation::forUser((int) $request->user()->getKey()),
            'speciesOptions' => RescueOrganisation::speciesOptions(),
        ]);
    }

    public function storeJoin(Request $request): RedirectResponse
    {
        SpamGuard::check($request, 'name');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'country' => ['required', Rule::in(['GB', 'US'])],
            'town' => ['required', 'string', 'max:80'],
            'species' => ['required', 'array', 'min:1'],
            'species.*' => [Rule::in(array_keys(RescueOrganisation::speciesOptions()))],
            'charity_number' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:200'],
            'contact_email' => ['required', 'email', 'max:160'],
            'description' => ['required', 'string', 'min:40', 'max:3000'],
            'accepts_fosters' => ['nullable', 'boolean'],
            'takes_rehoming_requests' => ['nullable', 'boolean'],
        ]);

        $data['accepts_fosters'] = $request->boolean('accepts_fosters');
        $data['takes_rehoming_requests'] = $request->boolean('takes_rehoming_requests');

        $organisation = RescueOrganisation::register($request->user(), $data);

        return redirect()->route('rescue.panel')->with('success', $organisation->isVerified()
            ? 'Your rescue profile has been updated.'
            : 'Thanks. We will check your charity details and email you when your rescue is verified, usually within two working days.');
    }

    public function welfare(Request $request): View
    {
        $slug = 'helping-hand';
        $campaign = WelfareCampaigns::find($slug);
        $answered = $request->session()->get('welfare.'.$slug);

        return view('rescue::welfare', [
            'slug' => $slug,
            'campaign' => $campaign,
            'answered' => $answered,
            'results' => $answered === null ? [] : WelfareResponse::results($slug, $campaign['answers']),
            'total' => WelfareResponse::totalFor($slug),
        ]);
    }

    public function answer(Request $request, string $campaign): RedirectResponse
    {
        $definition = WelfareCampaigns::find($campaign);

        abort_if($definition === null, 404);

        $answer = (string) $request->input('answer');

        abort_unless(array_key_exists($answer, $definition['answers']), 422);

        WelfareResponse::record($campaign, $answer, $this->country(), hash('sha256', $campaign.'|'.$request->ip().'|'.$request->userAgent()));
        $request->session()->put('welfare.'.$campaign, $answer);

        return redirect()->route('rescue.welfare')->withFragment('results');
    }

    private function species(Request $request): ?string
    {
        $value = (string) $request->query('species', '');

        return array_key_exists($value, RescueOrganisation::speciesOptions()) ? $value : null;
    }

    private function country(): string
    {
        return class_exists(CatMarketSettings::class) ? CatMarketSettings::visitorCountry() : 'GB';
    }
}
