@php
    $categoryId = $listing->getAttribute('category_id') === null ? null : (int) $listing->getAttribute('category_id');
    $isAnimal = \Modules\CatMarket\Support\WelfareRules::isAnimalCategory($categoryId);
    $values = (array) ($listing->getAttribute('custom_fields') ?? []);
    $find = static fn (string $suffix): mixed => \Modules\CatMarket\Support\WelfareRules::find($values, $suffix);
    $sellerType = $find('seller_type');
    $licence = $find('licence_number');
    $council = $find('licence_council');
    $charity = $find('charity_number');
    $sellerId = $listing->getAttribute('user_id') === null ? null : (int) $listing->getAttribute('user_id');
    $badges = \Modules\CatMarket\Models\SellerVerification::badgesFor($sellerId);
    $isBreederMember = $isAnimal && \App\Support\MembershipDirectory::isBreederMember($sellerId);
    $isOwnerViewing = auth()->check() && $sellerId !== null && (int) auth()->id() === $sellerId;
@endphp

@if($isAnimal)
    @if($listing->statusValue() === 'pending' && $isOwnerViewing)
        <div class="notice notice--caution" role="status">
            <p class="notice__title">{{ __('catmarket::messages.pending_notice_title') }}</p>
            <p>{{ __('catmarket::messages.pending_notice') }}</p>
        </div>
    @endif

    <div class="stack stack--tight">
        <h2 class="card__title">{{ __('catmarket::messages.welfare_title') }}</h2>
        <ul class="welfare-list">
            @if(filled($sellerType))
                <li class="welfare-list__item"><span>{{ __('catmarket::messages.seller') }}</span><strong>{{ $sellerType }}</strong></li>
            @endif
            @if(filled($licence))
                <li class="welfare-list__item"><span>{{ __('catmarket::messages.licence') }}</span><strong>{{ $licence }}@if(filled($council)) · {{ $council }}@endif</strong></li>
            @endif
            @if(filled($charity))
                <li class="welfare-list__item"><span>{{ __('catmarket::messages.charity') }}</span><strong>{{ $charity }}</strong></li>
            @endif
            @foreach($badges as $badge)
                <li class="welfare-list__item" title="{{ __('catmarket::messages.verified_hint') }}">
                    <span>{{ __('catmarket::messages.verified') }}</span>
                    <span class="badge badge--positive">{{ $badge['label'] }}</span>
                </li>
            @endforeach
            @if($isBreederMember)
                <li class="welfare-list__item"><span>{{ __('catmarket::messages.seller') }}</span><span class="badge badge--accent">{{ __('catmarket::messages.breeder_member') }}</span></li>
            @endif
        </ul>
    </div>

    <div class="notice">
        <p class="notice__title">{{ __('catmarket::messages.buyer_tips_title') }}</p>
        <p>{{ __('catmarket::messages.buyer_tips') }}
            <a class="text-link" href="{{ route('pages.show', 'safety-tips') }}">{{ __('catmarket::messages.read_safety') }}</a>
        </p>
    </div>
@endif
