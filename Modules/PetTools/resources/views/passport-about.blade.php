@extends('site::layouts.app')

@section('title', 'Pet Passport: free health records, insurance organiser and emergency QR pass')
@section('description', 'Keep your pet\'s vaccinations, treatments, weight, vet and insurance details in one free Pet Passport, with reminders and an emergency QR pass for their collar.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Pet Passport</p>
            <h1 class="title-page">Everything about your pet, in one place</h1>
            <p class="text-lead">Free for every pet. Private unless you choose to share.</p>
        </header>

        <div class="stack">
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">Health records and reminders</h2><p class="text-muted">Vaccinations, flea and worm treatments, vet visits, medicines and weight. We remind you in your notifications before anything is due.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">Insurance organiser</h2><p class="text-muted">Insurer, policy type, excess, limit, renewal date, claims phone number and a log of claims. Policy numbers are stored encrypted.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">Emergency QR pass</h2><p class="text-muted">Print a QR code for the collar or keep it on your phone. Anyone who scans it sees only what you choose: your number, vet, allergies and medicines. Switch it off or make a new code at any time. Microchip numbers are never shown.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">Missing mode</h2><p class="text-muted">If your pet goes missing, one tap turns the pass into a "please call me" page.</p></div></div>
        </div>

        <div class="row row--wrap">
            @auth
                <a href="{{ route('pettools.passport.index') }}" class="button button--primary">Open my Pet Passport</a>
            @else
                <a href="{{ route('register') }}" class="button button--primary">Create a free account</a>
                <a href="{{ route('login') }}" class="button button--ghost">Log in</a>
            @endauth
        </div>

        <p class="text-meta">The Passport is a record-keeping tool. It does not replace your vet's records or your insurer's documents. We are not an insurer and do not give insurance advice.</p>
    </div>
</div>
@endsection
