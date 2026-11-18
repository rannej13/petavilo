@extends('site::layouts.app')

@section('title', 'Free breeder tools: litters, weights, health records and licence register')
@section('description', 'Free litter management for every species on Petavilo: weigh-ins, health records, waiting list, licence register and a handover pack that goes straight into the new owner\'s Pet Passport.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow">For breeders</p>
            <h1 class="title-page">Run your litters in one place. Free.</h1>
            <p class="text-lead">Weigh-ins, vaccinations, waiting list, advert text and your licence register, for dogs, cats, rabbits and more. Works in any browser, with nothing to install.</p>
            <div class="row row--wrap">
                <a href="{{ route('breeder.dashboard') }}" class="button button--primary">Start a litter</a>
                @guest<a href="{{ route('register', ['redirect' => route('breeder.dashboard')]) }}" class="button button--secondary">Create a free account</a>@endguest
            </div>
        </header>

        <div class="grid grid--three">
            <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Every species</p><h2 class="title-card">Not just dogs</h2><p class="text-muted">Kittens, rabbits and guinea pigs get the same growth charts and records as puppies, with sensible due and go-home dates for each.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Weigh-in in seconds</p><h2 class="title-card">One form for the whole litter</h2><p class="text-muted">Type each weight in grams; each one gets its own growth line, so a slow gainer stands out.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Licence checks</p><h2 class="title-card">Warnings before you mate</h2><p class="text-muted">We flag a dam under 12 months, a second litter within 12 months, more than 6 litters or 2 caesareans, under the dog breeding licence conditions in England.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Inspection-ready</p><h2 class="title-card">Printable breeding register</h2><p class="text-muted">Litters, dams, microchips, new owners and dates, printed or as a spreadsheet for your council.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Honest adverts</p><h2 class="title-card">Advert text from your records</h2><p class="text-muted">Vaccinated, wormed and microchipped only appear when you have recorded them, so buyers can trust what they read.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Handover pack</p><h2 class="title-card">Records go home with them</h2><p class="text-muted">New owners scan a QR code and every weight, vaccination and worming record goes into their free Pet Passport, with reminders for what's due next.</p></div></div>
        </div>

        <aside class="notice notice--accent"><p class="notice__title">Free for every breeder</p><p>No licence or paid advert needed to use the tools. Your records stay private to you; buyers only see what you put in a handover pack.</p></aside>
    </div>
</div>
@endsection
