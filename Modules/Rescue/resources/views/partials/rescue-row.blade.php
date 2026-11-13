<a href="{{ route('rescue.show', $rescue->getAttribute('slug')) }}" class="card">
    <div class="card__body stack stack--tight">
        <div class="row row--between row--wrap">
            <h3 class="title-card">{{ $rescue->getAttribute('name') }}</h3>
            <span class="row">
                @if($rescue->acceptsDonations())<span class="badge badge--accent">Accepts donations</span>@endif
                <span class="badge badge--positive"><x-ui.icon name="shield"/> Verified rescue</span>
            </span>
        </div>
        <p class="text-meta">{{ $rescue->getAttribute('town') }}, {{ $rescue->getAttribute('country') === 'US' ? 'United States' : 'United Kingdom' }} · {{ implode(', ', $rescue->speciesLabels()) }}</p>
        <p class="text-muted text-clamp-2">{{ $rescue->getAttribute('description') }}</p>
    </div>
</a>
