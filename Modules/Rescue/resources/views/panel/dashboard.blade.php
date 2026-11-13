@extends('panel::layouts.panel', ['panelSection' => 'rescue'])

@section('title', 'Rescue dashboard')

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">Rescue dashboard</h1>
        @if($organisation)
            <p class="text-muted">{{ $organisation->getAttribute('name') }} · {{ $organisation->statusLabel() }}</p>
        @endif
    </div>
    @if($organisation)
        <a href="{{ route('rescue.join') }}" class="button button--ghost">Edit rescue</a>
    @endif
</header>

@if(! $organisation)
    <x-ui.empty-state icon="shield" title="Run a rescue or charity?" text="Add your rescue free to list animals for adoption and take applications.">
        <a href="{{ route('rescue.join') }}" class="button button--primary">Add your rescue</a>
    </x-ui.empty-state>
@else
    @unless($organisation->isVerified())
        <div class="notice notice--caution"><p class="notice__title">{{ $organisation->statusLabel() }}</p><p>Your adverts work as normal now. The Verified rescue badge, the Adopt page and online applications switch on once we have checked your details.</p></div>
    @endunless

    @if($organisation->isVerified())
        <section class="card"><div class="card__body stack stack--tight">
            <h2 class="card__title">Donations</h2>
            @if($organisation->acceptsDonations())
                <p>Donations are on. People can donate from <a class="text-link" href="{{ route('rescue.show', $organisation->getAttribute('slug')) }}#donate">your rescue page</a>. Stripe sends 100% of each donation to your own Stripe account automatically and pays it out to your bank. Card fees are covered by the donor or by us, never taken from the donation.</p>
                <p class="text-muted">{{ $donationTotals['count'] }} {{ \Illuminate\Support\Str::plural('donation', $donationTotals['count']) }} so far, {{ \Modules\Rescue\Models\Donation::money($donationTotals['sum'], $donationTotals['currency']) }} in total.</p>
            @elseif(! $organisation->getAttribute('donations_enabled'))
                <p class="text-muted">Donations have been paused for your rescue. Contact us if you think this is a mistake.</p>
            @else
                <p class="text-muted">Take card donations on your rescue page. Connect your own Stripe account and Stripe sends you 100% of every donation. {{ config('catmarket.site_name') }} takes no cut; donors can add an optional tip to us separately.</p>
                <form method="POST" action="{{ route('rescue.donations.connect') }}">
                    @csrf
                    <button type="submit" class="button button--primary">{{ $organisation->getAttribute('stripe_account_id') ? 'Continue Stripe set-up' : 'Set up donations with Stripe' }}</button>
                </form>
            @endif
        </div></section>
    @endif

    <section class="card">
        <div class="card__head"><h2 class="card__title">Adoption applications</h2></div>
        @if($applications->isEmpty())
            <div class="card__body"><p class="text-muted">No applications yet. Post animals in a "for rehoming" category and they appear on the Adopt page with an Apply button.</p>
                <a href="{{ route('panel.listings.create') }}" class="button button--secondary">List an animal</a></div>
        @else
            <div class="data-list">
                @foreach($applications as $application)
                    @php $listing = $listingTitles[(int) $application->getAttribute('listing_id')] ?? null; @endphp
                    <article class="data-row">
                        <div class="data-row__main stack stack--tight">
                            <p class="data-row__title">{{ $application->applicant?->name ?? 'Applicant' }} for {{ $listing['title'] ?? '#'.$application->getAttribute('listing_id') }}</p>
                            <div class="data-row__meta">
                                <span class="badge">{{ $application->statusLabel() }}</span>
                                <span>{{ $application->getAttribute('created_at')?->diffForHumans() }}</span>
                            </div>
                            <details>
                                <summary class="text-link">Read application</summary>
                                <ul class="welfare-list">
                                    @foreach(\Modules\Rescue\Models\AdoptionApplication::questions() as $key => $question)
                                        <li class="welfare-list__item"><span>{{ $question['label'] }}</span><strong>{{ $application->getAttribute('answers')[$key] ?? 'Not given' }}</strong></li>
                                    @endforeach
                                </ul>
                                <p>{!! nl2br(e((string) $application->getAttribute('message'))) !!}</p>
                            </details>
                        </div>
                        <form method="POST" action="{{ route('rescue.panel.update', $application->getKey()) }}" class="data-row__actions stack stack--tight">
                            @csrf
                            <select name="status" class="select">
                                @foreach(['reviewing' => 'Reviewing', 'more_info' => 'Ask for more details', 'approved' => 'Approve', 'declined' => 'Decline'] as $value => $label)
                                    <option value="{{ $value }}" @selected($application->getAttribute('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input name="note" class="input" maxlength="1000" placeholder="Message to the applicant (optional)">
                            <button type="submit" class="button button--secondary button--small">Update</button>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endif
@endsection
