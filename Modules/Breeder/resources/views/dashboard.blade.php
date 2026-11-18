@extends('panel::layouts.panel', ['panelSection' => 'breeder'])

@section('title', 'Breeder tools')

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">Breeder tools</h1>
        <p class="text-muted">Litters, weights, health records, waiting list and your licence register. Free for every breeder, every species.</p>
    </div>
    <div class="row row--wrap">
        <a href="{{ route('breeder.litters.create') }}" class="button button--primary">New litter</a>
        <a href="{{ route('breeder.animals.create') }}" class="button button--secondary">Add a dam or sire</a>
    </div>
</header>

@foreach($warnings as $litterId => $lines)
    <div class="notice notice--caution">
        <p class="notice__title">Check before going ahead: {{ $litters->firstWhere('id', $litterId)?->getAttribute('name') }}</p>
        <ul style="margin: 0; padding-left: 1.1em">@foreach($lines as $line)<li>{{ $line }}</li>@endforeach</ul>
    </div>
@endforeach

<section class="card">
    <div class="card__head"><h2 class="card__title">Litters</h2></div>
    @if($litters->isEmpty())
        <div class="card__body"><p class="text-muted">No litters yet. Add a planned mating, an expected litter or one that's already born.</p></div>
    @else
        <div class="data-list">
            @foreach($litters as $litter)
                @php $counts = $litter->counts(); @endphp
                <a href="{{ route('breeder.litters.show', $litter->getKey()) }}" class="data-row">
                    <div class="data-row__main stack stack--tight">
                        <p class="data-row__title">{{ $litter->getAttribute('name') }}</p>
                        <div class="data-row__meta">
                            <span class="badge">{{ \Modules\Breeder\Models\BreederLitter::STATUSES[$litter->getAttribute('status')] ?? '' }}</span>
                            @if($litter->getAttribute('born_on'))<span>{{ $litter->ageWeeks() }} weeks old</span>
                            @elseif($litter->getAttribute('due_on'))<span>Due {{ $litter->getAttribute('due_on')->isoFormat('D MMM') }}</span>@endif
                            @if($litter->getAttribute('ready_on') && $litter->getAttribute('ready_on')->isFuture())<span>Go home from {{ $litter->getAttribute('ready_on')->isoFormat('D MMM') }}</span>@endif
                            @if($counts['total'] > 0)<span>{{ $counts['available'] }} available · {{ $counts['reserved'] }} reserved · {{ $counts['sold'] }} home</span>@endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

<section class="card">
    <div class="card__head"><h2 class="card__title">Waiting list</h2></div>
    <div class="card__body stack">
        @if($waitlist->isNotEmpty())
            <div class="data-table-wrap"><table class="data-table">
                <thead><tr><th>Name</th><th>Wants</th><th>Contact</th><th>Litter</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($waitlist as $entry)
                        <tr>
                            <td>{{ $entry->getAttribute('name') }}@if($entry->getAttribute('town'))<br><span class="text-muted">{{ $entry->getAttribute('town') }}</span>@endif</td>
                            <td>{{ $entry->getAttribute('preference') }}</td>
                            <td>{{ $entry->getAttribute('email') }}@if($entry->getAttribute('phone'))<br>{{ $entry->getAttribute('phone') }}@endif</td>
                            <td>{{ $entry->litter?->getAttribute('name') ?? 'Any' }}</td>
                            <td>
                                <form method="POST" action="{{ route('breeder.waitlist.update', $entry->getKey()) }}" class="row">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $entry->getAttribute('name') }}">
                                    <input type="hidden" name="email" value="{{ $entry->getAttribute('email') }}">
                                    <input type="hidden" name="phone" value="{{ $entry->getAttribute('phone') }}">
                                    <input type="hidden" name="town" value="{{ $entry->getAttribute('town') }}">
                                    <input type="hidden" name="preference" value="{{ $entry->getAttribute('preference') }}">
                                    <input type="hidden" name="litter_id" value="{{ $entry->getAttribute('litter_id') }}">
                                    <label class="visually-hidden" for="wl-{{ $entry->getKey() }}">Status</label>
                                    <select id="wl-{{ $entry->getKey() }}" name="status" class="select" onchange="this.form.submit()">
                                        @foreach(\Modules\Breeder\Models\BreederWaitlist::STATUSES as $key => $label)<option value="{{ $key }}" @selected($entry->getAttribute('status') === $key)>{{ $label }}</option>@endforeach
                                    </select>
                                    <noscript><button type="submit" class="button button--ghost button--small">Save</button></noscript>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @else
            <p class="text-muted">Nobody waiting yet. Add people as they enquire, so you can offer in order when a litter arrives.</p>
        @endif
        <details>
            <summary class="text-link">Add someone to the waiting list</summary>
            <form method="POST" action="{{ route('breeder.waitlist.store') }}" class="stack" style="margin-top: var(--space-3)">
                @csrf
                <div class="field__row field__row--three">
                    <div class="field"><label class="field__label" for="wl-name">Name</label><input id="wl-name" name="name" class="input" required maxlength="120"></div>
                    <div class="field"><label class="field__label" for="wl-email">Email</label><input id="wl-email" name="email" type="email" class="input" maxlength="160"></div>
                    <div class="field"><label class="field__label" for="wl-phone">Phone</label><input id="wl-phone" name="phone" class="input" maxlength="40"></div>
                </div>
                <div class="field__row field__row--three">
                    <div class="field"><label class="field__label" for="wl-town">Town</label><input id="wl-town" name="town" class="input" maxlength="80"></div>
                    <div class="field"><label class="field__label" for="wl-pref">Looking for</label><input id="wl-pref" name="preference" class="input" maxlength="200" placeholder="Girl, any colour"></div>
                    <div class="field"><label class="field__label" for="wl-litter">Litter</label><select id="wl-litter" name="litter_id" class="select"><option value="">Any</option>@foreach($litters->where('status', '!=', 'closed') as $litter)<option value="{{ $litter->getKey() }}">{{ $litter->getAttribute('name') }}</option>@endforeach</select></div>
                </div>
                <div class="row"><button type="submit" class="button button--secondary">Add</button></div>
            </form>
        </details>
    </div>
</section>

<section class="card">
    <div class="card__head"><h2 class="card__title">Dams and sires</h2></div>
    @if($animals->isEmpty())
        <div class="card__body"><p class="text-muted">Add your breeding animals once, then pick them for each litter. We'll warn you if a planned litter would break licence conditions.</p></div>
    @else
        <div class="data-list">
            @foreach($animals as $animal)
                <a href="{{ route('breeder.animals.edit', $animal->getKey()) }}" class="data-row">
                    <div class="data-row__main stack stack--tight">
                        <p class="data-row__title">{{ $animal->getAttribute('name') }}</p>
                        <div class="data-row__meta"><span>{{ $animal->getAttribute('sex') === 'female' ? 'Dam' : 'Sire' }}</span><span>{{ $animal->getAttribute('breed') }}</span>@if($animal->ageLabel())<span>{{ $animal->ageLabel() }}</span>@endif</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

<section class="card"><div class="card__body row row--between row--wrap">
    <div class="stack stack--tight"><h2 class="card__title">Licence register</h2><p class="text-muted">Every litter, dam, microchip and new owner in one printable record for your council inspection.</p></div>
    <div class="row row--wrap"><a href="{{ route('breeder.register') }}" class="button button--secondary">View and print</a><a href="{{ route('breeder.register.csv') }}" class="button button--ghost">Download CSV</a></div>
</div></section>
@endsection
