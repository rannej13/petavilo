@php $tier = (string) $business->getAttribute('tier'); @endphp
<article class="card directory-card {{ $tier === 'premium' ? 'directory-card--premium' : '' }}">
    <div class="card__body stack stack--tight">
        <div class="directory-card__head">
            <h3 class="title-card">
                <a href="{{ route('directory.show', [$business->getAttribute('type'), $business->getAttribute('slug')]) }}">{{ $business->getAttribute('name') }}</a>
            </h3>
            @if($tier === 'premium')
                <span class="badge badge--accent">{{ __('directory::messages.premium') }}</span>
            @elseif($tier === 'plus')
                <span class="badge">{{ __('directory::messages.plus') }}</span>
            @endif
        </div>
        <p class="text-meta">
            {{ \Modules\Directory\Models\Business::typeName((string) $business->getAttribute('type'), true) }}
            @if(filled($business->getAttribute('town'))) · {{ $business->getAttribute('town') }}@endif
            @if($business->getAttribute('is_verified')) · <span class="badge badge--positive">{{ __('directory::messages.verified') }}</span>@endif
            @if(! $business->isClaimed() && filled($business->getAttribute('source'))) · <span class="badge" title="{{ __('directory::messages.unclaimed_hint') }}">{{ __('directory::messages.unclaimed') }}</span>@endif
        </p>
        @if(($business->accreditationLabel() && $business->getAttribute('accreditation_verified')) || $business->getAttribute('emergency_care') || ($business->isPaid() && filled($business->getAttribute('new_client_offer'))))
            <div class="row row--wrap">
                @if($business->accreditationLabel() && $business->getAttribute('accreditation_verified'))<span class="badge badge--solid">{{ $business->accreditationLabel() }}</span>@endif
                @if($business->getAttribute('emergency_care'))<span class="badge badge--caution">{{ __('directory::messages.emergency_care') }}</span>@endif
                @if($business->isPaid() && filled($business->getAttribute('new_client_offer')))<span class="badge badge--accent">{{ __('directory::messages.offer') }}</span>@endif
            </div>
        @endif
        @if($business->isPaid() && filled($business->getAttribute('description')))
            <p class="text-clamp-2">{{ $business->getAttribute('description') }}</p>
        @endif
        @if(filled($business->getAttribute('phone')))
            <a class="text-link" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $business->getAttribute('phone')) }}">{{ $business->getAttribute('phone') }}</a>
        @endif
    </div>
</article>
