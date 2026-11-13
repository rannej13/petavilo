<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pet->getAttribute('name') }}: emergency pass</title>
    @vite(['resources/ts/app.ts'])
</head>
<body>
<main class="shell shell--narrow page">
    <div class="stack stack--loose">
        @if($pet->getAttribute('is_missing'))
            <div class="notice notice--caution" role="alert">
                <p class="notice__title">{{ $pet->getAttribute('name') }} is missing</p>
                <p>If you have found {{ $pet->getAttribute('name') }}, please call the owner straight away. Keep them safe and indoors if you can.</p>
            </div>
        @endif

        <header class="stack stack--tight">
            <p class="text-eyebrow">Emergency pass</p>
            <h1 class="title-page">{{ $pet->getAttribute('name') }}</h1>
            <p class="text-muted">{{ $pet->speciesLabel() }}@if($pet->getAttribute('breed')) · {{ $pet->getAttribute('breed') }}@endif @if($pet->getAttribute('colour')) · {{ $pet->getAttribute('colour') }}@endif</p>
        </header>

        @if($photo = $pet->photoUrl())
            <img src="{{ $photo }}" alt="{{ $pet->getAttribute('name') }}" style="width:100%;max-height:360px;object-fit:cover;border-radius:12px">
        @endif

        <ul class="welfare-list">
            @if($pet->shows('owner_phone') && $pet->getAttribute('owner_phone'))
                <li class="welfare-list__item"><span>Owner</span><strong><a class="button button--primary" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $pet->getAttribute('owner_phone')) }}">Call {{ $pet->getAttribute('owner_phone') }}</a></strong></li>
            @endif
            @if($pet->shows('emergency_contact') && $pet->getAttribute('emergency_phone'))
                <li class="welfare-list__item"><span>Back-up contact</span><strong>{{ $pet->getAttribute('emergency_contact') }} · <a class="text-link" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $pet->getAttribute('emergency_phone')) }}">{{ $pet->getAttribute('emergency_phone') }}</a></strong></li>
            @endif
            @if($pet->shows('vet') && $pet->getAttribute('vet_name'))
                <li class="welfare-list__item"><span>Vet</span><strong>{{ $pet->getAttribute('vet_name') }}@if($pet->getAttribute('vet_phone')) · <a class="text-link" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $pet->getAttribute('vet_phone')) }}">{{ $pet->getAttribute('vet_phone') }}</a>@endif</strong></li>
            @endif
            @if($pet->shows('medical'))
                @foreach(['allergies' => 'Allergies', 'medications' => 'Medicines', 'medical_notes' => 'Medical notes'] as $field => $label)
                    @if(filled($pet->getAttribute($field)))<li class="welfare-list__item"><span>{{ $label }}</span><strong>{{ $pet->getAttribute($field) }}</strong></li>@endif
                @endforeach
            @endif
            @if($pet->shows('diet') && $pet->getAttribute('diet'))
                <li class="welfare-list__item"><span>Diet</span><strong>{{ $pet->getAttribute('diet') }}</strong></li>
            @endif
            @if($pet->shows('insurance') && $pet->getAttribute('insurer'))
                <li class="welfare-list__item"><span>Insurer</span><strong>{{ $pet->getAttribute('insurer') }}@if($pet->getAttribute('insurer_claims_phone')) · {{ $pet->getAttribute('insurer_claims_phone') }}@endif</strong></li>
            @endif
        </ul>

        @if($pet->shows('sitter'))
            @include('pettools::partials.sitter-details', ['pet' => $pet])
        @endif

        <div class="notice">
            <p class="notice__title">Found this pet hurt?</p>
            <p>Take them to the nearest vet. Any vet can also scan their microchip. <a class="text-link" href="{{ route('rescue.found-animal') }}">Who to call</a></p>
        </div>

        <p class="text-meta">Pass made with {{ config('catmarket.site_name') }} Pet Passport. The owner chose what to show here.</p>
    </div>
</main>
</body>
</html>
