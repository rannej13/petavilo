@extends('site::layouts.app')

@section('title', 'Free pet tools')
@section('description', 'Free tools for pet owners: check if a food or plant is poisonous to your pet, find the right food, and keep vaccinations, insurance and an emergency QR pass in one Pet Passport.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')
        <header class="stack stack--tight">
            <p class="text-eyebrow">Free tools</p>
            <h1 class="title-page">Free tools for pet owners</h1>
            <p class="text-lead">Quick answers and a safe place for your pet's records. Free, with no adverts inside your Passport.</p>
        </header>

        <div class="grid grid--three">
            <a href="{{ route('pettools.poison') }}" class="card"><div class="card__body stack stack--tight">
                <p class="text-eyebrow">Is it poisonous?</p>
                <h2 class="title-card">Check food, plants, medicines and household products</h2>
                <p class="text-muted">For dogs, cats, rabbits and birds, with who to call if your pet has eaten something.</p>
            </div></a>
            <a href="{{ route('pettools.pain') }}" class="card"><div class="card__body stack stack--tight">
                <p class="text-eyebrow">Cat pain check</p>
                <h2 class="title-card">Is my cat in pain, or is it an emergency?</h2>
                <p class="text-muted">A two-minute face check plus the warning signs that mean "call a vet now".</p>
            </div></a>
            <a href="{{ route('pettools.food') }}" class="card"><div class="card__body stack stack--tight">
                <p class="text-eyebrow">Food matcher</p>
                <h2 class="title-card">What should my pet eat?</h2>
                <p class="text-muted">Answer six quick questions for cats, dogs, rabbits and guinea pigs.</p>
            </div></a>
            <a href="{{ route('pettools.calories') }}" class="card"><div class="card__body stack stack--tight">
                <p class="text-eyebrow">Calorie calculator</p>
                <h2 class="title-card">How much should I feed my cat or dog?</h2>
                <p class="text-muted">Daily calories and grams, plus a 14-day plan for switching food.</p>
            </div></a>
            <a href="{{ route('pettools.passport.about') }}" class="card"><div class="card__body stack stack--tight">
                <p class="text-eyebrow">Pet Passport</p>
                <h2 class="title-card">Health records, insurance and an emergency QR pass</h2>
                <p class="text-muted">Vaccination reminders, weight, vet details and insurance renewal in one place.</p>
            </div></a>
        </div>
    </div>
</div>
@endsection
