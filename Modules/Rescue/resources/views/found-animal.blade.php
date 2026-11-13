@extends('site::layouts.app')

@section('title', 'Found a stray, lost or injured animal? What to do')
@section('description', 'Calm, safe steps if you find a stray, lost or injured cat, dog or wild animal in the UK or US, with who to call.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Found an animal?</p>
            <h1 class="title-page">Found a stray, lost or injured animal?</h1>
            <p class="text-lead">Simple, safe steps. {{ config('catmarket.site_name') }} cannot send anyone out, so the numbers below are who to call.</p>
        </header>

        <div class="notice notice--caution" role="note">
            <p class="notice__title">Is it badly hurt, bleeding, struggling to breathe or hit by a car?</p>
            <p>Call a vet now. Any vet will give emergency first aid to an injured stray or wild animal, usually without charge. If you cannot get through, call the number for your area below.</p>
        </div>

        <section class="stack">
            <h2 class="title-section">Who to call</h2>
            <div class="grid grid--three">
                <div class="card"><div class="card__body stack stack--tight">
                    <h3 class="title-card">England and Wales</h3>
                    <p>RSPCA 24-hour line: <a class="text-link" href="tel:03001234999">0300 1234 999</a></p>
                    <p class="text-muted">Stray dogs: your council's dog warden.</p>
                </div></div>
                <div class="card"><div class="card__body stack stack--tight">
                    <h3 class="title-card">Scotland</h3>
                    <p>Scottish SPCA: <a class="text-link" href="tel:03000999999">03000 999 999</a></p>
                    <p class="text-muted">Stray dogs: your council.</p>
                </div></div>
                <div class="card"><div class="card__body stack stack--tight">
                    <h3 class="title-card">Northern Ireland</h3>
                    <p>USPCA: <a class="text-link" href="tel:02830251000">028 3025 1000</a></p>
                    <p class="text-muted">Stray dogs: your council's dog warden.</p>
                </div></div>
                <div class="card"><div class="card__body stack stack--tight">
                    <h3 class="title-card">United States</h3>
                    <p>Your local animal control or police non-emergency line. For a suspected poisoning, the ASPCA Animal Poison Control Center: <a class="text-link" href="tel:8884264435">(888) 426-4435</a> (a fee may apply).</p>
                </div></div>
            </div>
            <p class="text-muted">Numbers checked November 2026. <a class="text-link" href="{{ route('directory.type', ['type' => 'vets']) }}">Find a vet near you</a></p>
        </section>

        <section class="stack">
            <h2 class="title-section">If the animal is not hurt</h2>
            <div class="stack">
                <div class="card"><div class="card__body stack stack--tight"><h3 class="title-card">A cat</h3><p>Many cats roam and have a home nearby. Look for a collar, ask neighbours and post a free Found advert. If it stays for days, looks thin or unwell, take it to a vet to be scanned for a microchip.</p></div></div>
                <div class="card"><div class="card__body stack stack--tight"><h3 class="title-card">A dog</h3><p>Check the collar tag, keep it safe and away from roads if it is calm, and get it scanned at a vet. In the UK a found dog must be reported to the council dog warden.</p></div></div>
                <div class="card"><div class="card__body stack stack--tight"><h3 class="title-card">A rabbit, guinea pig, bird or reptile</h3><p>Pets like these struggle outdoors. If you can contain it safely in a box with air holes, keep it somewhere quiet and call a local rescue or vet.</p></div></div>
                <div class="card"><div class="card__body stack stack--tight"><h3 class="title-card">Wildlife</h3><p>Baby birds and fox cubs are often not abandoned. Watch from a distance and call a wildlife rescue before moving them.</p></div></div>
            </div>
        </section>

        <section class="stack stack--tight">
            <h2 class="title-section">Do not</h2>
            <ul class="stack stack--tight">
                <li>Put yourself in danger on a road or try to catch an animal that is resisting. Frightened animals bite.</li>
                <li>Give medicines meant for people. Many are poisonous to pets.</li>
                <li>Give cow's milk. Water is best.</li>
                <li>Rehome a found pet yourself before the owner has had a chance to come forward.</li>
            </ul>
        </section>

        <div class="row row--wrap">
            <a href="{{ route('panel.listings.create') }}" class="button button--primary">Post a free Found advert</a>
            <a href="{{ route('rescue.lost-found') }}" class="button button--ghost">Search lost pets</a>
        </div>

        <p class="text-meta">This page is general guidance, not veterinary advice.</p>
    </div>
</div>
@endsection
