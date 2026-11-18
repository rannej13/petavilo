@extends('panel::layouts.panel', ['panelSection' => 'breeder'])

@section('title', $litter->getAttribute('name'))

@php
    $spark = static function (array $weights): string {
        if (count($weights) < 2) {
            return '';
        }
        $values = array_column($weights, 'grams');
        $min = min($values); $max = max($values); $span = max(1, $max - $min);
        $n = count($values); $points = [];
        foreach ($values as $i => $g) {
            $points[] = round($i * (96 / ($n - 1)), 1).','.round(26 - (($g - $min) / $span) * 22, 1);
        }
        return '<svg class="spark" width="100" height="30" viewBox="-2 0 100 30" role="img" aria-label="Weight from '.number_format($values[0]).' g to '.number_format(end($values)).' g"><polyline points="'.implode(' ', $points).'" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/></svg>';
    };
    $offspring = $litter->offspring;
@endphp

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <p class="text-eyebrow"><a href="{{ route('breeder.dashboard') }}" class="text-link">Breeder tools</a></p>
        <h1 class="title-page">{{ $litter->getAttribute('name') }}</h1>
        <p class="text-muted">
            {{ \Modules\Breeder\Models\BreederLitter::STATUSES[$litter->getAttribute('status')] ?? '' }}
            · Dam: {{ $litter->dam?->getAttribute('name') ?? 'not recorded' }} · Sire: {{ $litter->sireLabel() }}
            @if($litter->getAttribute('born_on')) · Born {{ $litter->getAttribute('born_on')->isoFormat('D MMM YYYY') }} ({{ $litter->ageWeeks() }} weeks)@elseif($litter->getAttribute('due_on')) · Due {{ $litter->getAttribute('due_on')->isoFormat('D MMM YYYY') }}@endif
            @if($litter->getAttribute('ready_on')) · New homes from {{ $litter->getAttribute('ready_on')->isoFormat('D MMM') }}@endif
        </p>
    </div>
    <a href="{{ route('breeder.litters.edit', $litter->getKey()) }}" class="button button--ghost">Edit litter</a>
</header>

@if($warnings !== [])
    <div class="notice notice--caution">
        <p class="notice__title">Licence conditions to check</p>
        <ul style="margin: 0; padding-left: 1.1em">@foreach($warnings as $line)<li>{{ $line }}</li>@endforeach</ul>
        <p style="font-size: var(--text-xs)">Based on the dog breeding licence conditions in England (<a class="text-link" href="{{ \Modules\Breeder\Support\BreedingChecks::SOURCE_URL }}" target="_blank" rel="noopener">Schedule 6</a>). Not legal advice; check with your council.</p>
    </div>
@endif

<section class="card">
    <div class="card__head"><h2 class="card__title">{{ $offspring->count() }} in this litter</h2></div>
    <div class="card__body stack">
        @if($offspring->isEmpty())
            <p class="text-muted">Add them once they're born. You can name them or use collar colours.</p>
        @else
            <div class="data-table-wrap"><table class="data-table">
                <thead><tr><th>Name</th><th>Sex</th><th>Latest weight</th><th>Growth</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($offspring as $o)
                    @php $w = $o->weights(); $last = end($w) ?: null; @endphp
                    <tr>
                        <td><strong>{{ $o->getAttribute('name') }}</strong>@if($o->getAttribute('colour'))<br><span class="text-muted">{{ $o->getAttribute('colour') }}</span>@endif</td>
                        <td>{{ ucfirst((string) $o->getAttribute('sex')) }}</td>
                        <td>@if($last){{ number_format($last['grams']) }} g<br><span class="text-muted">{{ \Illuminate\Support\Carbon::parse($last['date'])->isoFormat('D MMM') }}</span>@else<span class="text-muted">None yet</span>@endif</td>
                        <td>{!! $spark($w) !!}</td>
                        <td><span class="badge">{{ \Modules\Breeder\Models\BreederOffspring::STATUSES[$o->getAttribute('status')] ?? '' }}</span>@if($o->getAttribute('buyer_name'))<br><span class="text-muted">{{ $o->getAttribute('buyer_name') }}</span>@endif</td>
                        <td><a href="#o-{{ $o->getKey() }}" class="text-link">Edit</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
        <form method="POST" action="{{ route('breeder.offspring.add', $litter->getKey()) }}" class="row row--wrap">
            @csrf
            <label class="visually-hidden" for="add-count">How many</label>
            <input id="add-count" name="count" type="number" min="1" max="20" value="1" class="input" style="width: 5rem">
            <label class="visually-hidden" for="add-sex">Sex</label>
            <select id="add-sex" name="sex" class="select" style="width: auto"><option value="">Sex not set</option><option value="male">Male</option><option value="female">Female</option></select>
            <button type="submit" class="button button--secondary">Add to litter</button>
        </form>
    </div>
</section>

@if($offspring->isNotEmpty())
<section class="card">
    <div class="card__head"><h2 class="card__title">Weigh-in</h2></div>
    <form method="POST" action="{{ route('breeder.weights.store', $litter->getKey()) }}" class="card__body stack">
        @csrf
        <div class="field" style="max-width: 14rem"><label class="field__label" for="w-date">Date</label><input id="w-date" name="recorded_on" type="date" class="input" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required></div>
        <div class="grid grid--three">
            @foreach($offspring->whereNotIn('status', ['died']) as $o)
                <div class="field"><label class="field__label" for="w-{{ $o->getKey() }}">{{ $o->getAttribute('name') }} (grams)</label><input id="w-{{ $o->getKey() }}" name="grams[{{ $o->getKey() }}]" type="number" min="1" max="200000" inputmode="numeric" class="input"></div>
            @endforeach
        </div>
        <div class="row"><button type="submit" class="button button--primary">Save weights</button></div>
    </form>
</section>

<section class="card">
    <div class="card__head"><h2 class="card__title">Health and care records</h2></div>
    <div class="card__body stack">
        <form method="POST" action="{{ route('breeder.records.store', $litter->getKey()) }}" class="stack">
            @csrf
            <div class="field__row field__row--three">
                <div class="field"><label class="field__label" for="r-kind">What</label><select id="r-kind" name="kind" class="select">@foreach(\Modules\Breeder\Models\BreederRecord::KINDS as $key => $label)@if($key !== 'weight')<option value="{{ $key }}">{{ $label }}</option>@endif @endforeach</select></div>
                <div class="field"><label class="field__label" for="r-date">Date</label><input id="r-date" name="recorded_on" type="date" class="input" value="{{ now()->toDateString() }}" required></div>
                <div class="field"><label class="field__label" for="r-title">Product or detail</label><input id="r-title" name="title" class="input" maxlength="120" placeholder="e.g. First vaccination, Nobivac DHP"></div>
            </div>
            <fieldset class="field" style="border: 0; padding: 0; margin: 0"><legend class="field__label">For</legend>
                <div class="row row--wrap">@foreach($offspring->whereNotIn('status', ['died']) as $o)<label class="pill" style="cursor: pointer"><input type="checkbox" name="offspring[]" value="{{ $o->getKey() }}" checked> {{ $o->getAttribute('name') }}</label>@endforeach</div>
            </fieldset>
            <div class="field"><label class="field__label" for="r-details">Notes</label><input id="r-details" name="details" class="input" maxlength="1000" placeholder="Batch number, vet practice, dose"></div>
            <div class="row"><button type="submit" class="button button--secondary">Record it</button></div>
        </form>
        @if($shared->isNotEmpty())
            <div class="data-list">
                @foreach($shared as $group)
                    @php $first = $group->first(); @endphp
                    <div class="data-row"><div class="data-row__main stack stack--tight">
                        <p class="data-row__title">{{ $first->label() }}</p>
                        <div class="data-row__meta"><span>{{ $first->getAttribute('recorded_on')->isoFormat('D MMM YYYY') }}</span><span>{{ $group->count() }} of {{ $offspring->count() }}</span>@if($first->getAttribute('details'))<span>{{ $first->getAttribute('details') }}</span>@endif</div>
                    </div></div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

<section class="card">
    <div class="card__head"><h2 class="card__title">Advert text</h2></div>
    <div class="card__body stack">
        <p class="text-muted">Written from your litter's real records. Copy it into a new advert, then add photos.</p>
        <div class="field"><label class="field__label" for="ad-title">Title</label><input id="ad-title" class="input" readonly value="{{ $advert['title'] }}" onclick="this.select()"></div>
        <div class="field"><label class="field__label" for="ad-body">Description</label><textarea id="ad-body" class="textarea" rows="6" readonly onclick="this.select()">{{ $advert['body'] }}</textarea></div>
        <div class="row row--wrap"><a href="{{ route('panel.listings.create') }}" class="button button--primary">Post an advert</a></div>
    </div>
</section>

@if($offspring->isNotEmpty())
<section class="stack">
    <h2 class="title-section">Each one</h2>
    @foreach($offspring as $o)
        <article class="card" id="o-{{ $o->getKey() }}">
            <div class="card__head"><h3 class="card__title">{{ $o->getAttribute('name') }}</h3><span class="badge">{{ \Modules\Breeder\Models\BreederOffspring::STATUSES[$o->getAttribute('status')] ?? '' }}</span></div>
            <div class="card__body stack">
                <form method="POST" action="{{ route('breeder.offspring.update', $o->getKey()) }}" class="stack">
                    @csrf @method('PUT')
                    <div class="field__row field__row--three">
                        <div class="field"><label class="field__label" for="n-{{ $o->getKey() }}">Name or collar</label><input id="n-{{ $o->getKey() }}" name="name" class="input" required maxlength="80" value="{{ $o->getAttribute('name') }}"></div>
                        <div class="field"><label class="field__label" for="s-{{ $o->getKey() }}">Sex</label><select id="s-{{ $o->getKey() }}" name="sex" class="select"><option value="">Not set</option><option value="male" @selected($o->getAttribute('sex') === 'male')>Male</option><option value="female" @selected($o->getAttribute('sex') === 'female')>Female</option></select></div>
                        <div class="field"><label class="field__label" for="c-{{ $o->getKey() }}">Colour and markings</label><input id="c-{{ $o->getKey() }}" name="colour" class="input" maxlength="120" value="{{ $o->getAttribute('colour') }}"></div>
                    </div>
                    <div class="field__row field__row--three">
                        <div class="field"><label class="field__label" for="m-{{ $o->getKey() }}">Microchip number</label><input id="m-{{ $o->getKey() }}" name="microchip_number" class="input" maxlength="30" autocomplete="off" value="{{ $o->getAttribute('microchip_number') }}"></div>
                        <div class="field"><label class="field__label" for="st-{{ $o->getKey() }}">Status</label><select id="st-{{ $o->getKey() }}" name="status" class="select">@foreach(\Modules\Breeder\Models\BreederOffspring::STATUSES as $key => $label)<option value="{{ $key }}" @selected($o->getAttribute('status') === $key)>{{ $label }}</option>@endforeach</select></div>
                        <div class="field"><label class="field__label" for="b-{{ $o->getKey() }}">New owner</label><input id="b-{{ $o->getKey() }}" name="buyer_name" class="input" maxlength="120" value="{{ $o->getAttribute('buyer_name') }}"></div>
                    </div>
                    <div class="field__row field__row--two">
                        <div class="field"><label class="field__label" for="be-{{ $o->getKey() }}">New owner's email</label><input id="be-{{ $o->getKey() }}" name="buyer_email" type="email" class="input" maxlength="160" value="{{ $o->getAttribute('buyer_email') }}"></div>
                        <div class="field"><label class="field__label" for="bt-{{ $o->getKey() }}">New owner's town</label><input id="bt-{{ $o->getKey() }}" name="buyer_town" class="input" maxlength="80" value="{{ $o->getAttribute('buyer_town') }}"></div>
                    </div>
                    <div class="field"><label class="field__label" for="cn-{{ $o->getKey() }}">Care notes for the new owner</label><textarea id="cn-{{ $o->getKey() }}" name="care_notes" class="textarea" rows="2" maxlength="2000" placeholder="Food, feeding times, next vaccination due">{{ $o->getAttribute('care_notes') }}</textarea></div>
                    <div class="row row--wrap">
                        <button type="submit" class="button button--secondary">Save</button>
                        @if($o->getAttribute('handover_token'))<a href="{{ route('breeder.pack', $o->getKey()) }}" class="button button--ghost">Handover pack</a>@endif
                    </div>
                </form>
                @unless($o->getAttribute('handover_token'))
                    <details>
                        <summary class="text-link">Going home? Create the handover pack</summary>
                        <form method="POST" action="{{ route('breeder.handover.start', $o->getKey()) }}" class="stack" style="margin-top: var(--space-3)">
                            @csrf
                            <p class="text-muted">Marks {{ $o->getAttribute('name') }} as gone home and makes a pack with every weight and health record. The new owner can add it all to a free Pet Passport in one tap.</p>
                            <div class="field__row field__row--three">
                                <div class="field"><label class="field__label" for="hn-{{ $o->getKey() }}">New owner's name</label><input id="hn-{{ $o->getKey() }}" name="buyer_name" class="input" required maxlength="120" value="{{ $o->getAttribute('buyer_name') }}"></div>
                                <div class="field"><label class="field__label" for="he-{{ $o->getKey() }}">Email</label><input id="he-{{ $o->getKey() }}" name="buyer_email" type="email" class="input" maxlength="160" value="{{ $o->getAttribute('buyer_email') }}"></div>
                                <div class="field"><label class="field__label" for="ht-{{ $o->getKey() }}">Town</label><input id="ht-{{ $o->getKey() }}" name="buyer_town" class="input" maxlength="80" value="{{ $o->getAttribute('buyer_town') }}"></div>
                            </div>
                            <div class="row"><button type="submit" class="button button--primary">Create handover pack</button></div>
                        </form>
                    </details>
                @endunless
            </div>
        </article>
    @endforeach
</section>
@endif

<section class="card">
    <div class="card__head"><h2 class="card__title">Waiting for this litter</h2></div>
    <div class="card__body">
        @if($litter->waitlist->isEmpty())
            <p class="text-muted">Nobody yet. Add enquiries from <a class="text-link" href="{{ route('breeder.dashboard') }}">your dashboard</a>.</p>
        @else
            <ol style="margin: 0; padding-left: 1.2em">@foreach($litter->waitlist as $entry)<li>{{ $entry->getAttribute('name') }}@if($entry->getAttribute('preference')) · {{ $entry->getAttribute('preference') }}@endif · {{ \Modules\Breeder\Models\BreederWaitlist::STATUSES[$entry->getAttribute('status')] ?? '' }}</li>@endforeach</ol>
        @endif
    </div>
</section>
@endsection
