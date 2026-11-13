@extends('site::layouts.app')

@section('title', 'After-hours triage widget for vet practices')
@section('description', 'Add a triage form to your practice website. Owners get clear red, amber or green advice; your team gets pre-exam notes in SOAP format. 14-day free trial.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow">For vet practices</p>
            <h1 class="title-page">Fewer after-hours phone calls. Better-prepared appointments.</h1>
            <p class="text-lead">A triage form for your website, in your practice name. Owners answer structured questions, including the Feline Grimace Scale. They get clear advice on how urgent it is. You get a pre-exam note in SOAP format by email.</p>
            <div class="row row--wrap">
                <a href="{{ route('pettools.clinic.dashboard') }}" class="button button--primary">Start a 14-day free trial</a>
                <a href="#how" class="button button--ghost">How it works</a>
            </div>
        </header>

        <section id="how" class="grid grid--three">
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">1. Paste one line</h2><p class="text-muted">Copy the embed code onto your website's contact or emergency page.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">2. Owners triage</h2><p class="text-muted">Ten quick questions and an optional grimace score. Red flags like a blocked bladder go straight to "call now".</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">3. You get the notes</h2><p class="text-muted">A SOAP-formatted pre-exam note lands in your inbox, and the owner sees your booking link.</p></div></div>
        </section>

        <section class="card"><div class="card__body stack stack--tight">
            <h2 class="card__title">Pricing</h2>
            <p class="title-section">{{ $country === 'US' ? '$129' : '£99' }} <span class="text-meta">per month per practice after the free trial</span></p>
            <ul class="stack stack--tight">
                <li>Your practice name on the form, with "powered by {{ config('catmarket.site_name') }}" in small print</li>
                <li>Unlimited triage forms and emailed notes</li>
                <li>Fixed, transparent rules; no AI guesswork</li>
                <li>Cancel any time</li>
            </ul>
        </div></section>

        <p class="text-meta">The widget gives owners general urgency guidance based on their answers. It is not a clinical examination or a diagnosis, and it says so to owners. Your practice remains responsible for clinical decisions.</p>
    </div>
</div>
@endsection
