<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pet->getAttribute('name') }}: sitter sheet</title>
    @vite(['resources/ts/app.ts'])
    <style>
        @media print { .no-print { display: none !important; } body { background: #fff; } .shell { max-width: none; padding: 0; } }
        .sheet-grid { display: grid; grid-template-columns: 1fr 180px; gap: 24px; align-items: start; }
    </style>
</head>
<body>
<main class="shell shell--narrow page">
    <div class="stack">
        <div class="row row--between no-print">
            <a href="{{ route('pettools.passport.show', $pet->getKey()) }}" class="button button--ghost">Back</a>
            <button type="button" class="button button--primary" onclick="window.print()">Print or save as PDF</button>
        </div>

        <div class="sheet-grid">
            <header class="stack stack--tight">
                <p class="text-eyebrow">Pet sitter sheet</p>
                <h1 class="title-page">{{ $pet->getAttribute('name') }}</h1>
                <p class="text-muted">{{ $pet->speciesLabel() }}@if($pet->getAttribute('breed')) · {{ $pet->getAttribute('breed') }}@endif @if($age = $pet->ageLabel()) · {{ $age }}@endif @if($pet->getAttribute('colour')) · {{ $pet->getAttribute('colour') }}@endif</p>
                @if($pet->getAttribute('owner_phone'))<p><strong>Owner:</strong> {{ $pet->getAttribute('owner_phone') }}</p>@endif
                @if($pet->getAttribute('emergency_phone'))<p><strong>Back-up:</strong> {{ $pet->getAttribute('emergency_contact') }} {{ $pet->getAttribute('emergency_phone') }}</p>@endif
                @if($pet->getAttribute('allergies'))<p><strong>Allergies:</strong> {{ $pet->getAttribute('allergies') }}</p>@endif
            </header>
            <div class="stack stack--tight">
                @if($photo = $pet->photoUrl())<img src="{{ $photo }}" alt="" style="width:180px;height:180px;object-fit:cover;border-radius:12px">@endif
                @if($pet->getAttribute('pass_enabled') && $pet->getAttribute('pass_token'))
                    <div data-qr="{{ route('pettools.pass', $pet->getAttribute('pass_token')) }}" data-qr-label="{{ $pet->getAttribute('name') }}" style="width:180px"></div>
                    <p class="text-meta">Scan for the emergency pass</p>
                @endif
            </div>
        </div>

        @include('pettools::partials.sitter-details', ['pet' => $pet])

        @if($pet->getAttribute('medical_notes'))
            <p><strong>Medical notes:</strong> {{ $pet->getAttribute('medical_notes') }}</p>
        @endif
        @if($pet->getAttribute('insurer'))
            <p><strong>Insurance:</strong> {{ $pet->getAttribute('insurer') }}@if($pet->getAttribute('insurer_claims_phone')) · claims {{ $pet->getAttribute('insurer_claims_phone') }}@endif</p>
        @endif

        <div class="notice">
            <p class="notice__title">In an emergency</p>
            <p>Call the vet above first. Out of hours, their answerphone gives the emergency vet. Suspected poisoning: {{ ($pet->user?->profile?->country ?? '') === 'United States' ? 'ASPCA Animal Poison Control (888) 426-4435' : 'Animal PoisonLine 01202 509000' }}.</p>
        </div>
        @if(! $pet->getAttribute('pass_enabled'))
            <p class="text-meta no-print">Switch on the emergency pass on {{ $pet->getAttribute('name') }}'s page to add a QR code to this sheet.</p>
        @endif
    </div>
</main>
</body>
</html>
