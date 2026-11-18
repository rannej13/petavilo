@extends('site::layouts.app')

@section('title', 'Enter Pet of the Week')

@section('content')
<div class="shell page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow"><a href="{{ route('tv.potw') }}" class="text-link">Pet of the Week</a></p>
            <h1 class="title-page">Enter your pet</h1>
            <p class="text-lead">One entry per member each week. A person checks every photo before it goes up, usually within a day.</p>
        </header>

        <form method="POST" action="{{ route('tv.potw.store') }}" enctype="multipart/form-data" class="card"><div class="card__body stack">
            @csrf
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label" for="pet_name">Pet's name</label>
                    <input id="pet_name" name="pet_name" class="input" required maxlength="60" value="{{ old('pet_name') }}" list="my-pets">
                    @if($pets->isNotEmpty())<datalist id="my-pets">@foreach($pets as $pet)<option value="{{ $pet->getAttribute('name') }}">@endforeach</datalist>@endif
                    @error('pet_name')<p class="field__error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="field__label" for="species">Type of pet</label>
                    <select id="species" name="species" class="select" required>
                        @foreach($species as $key => $label)<option value="{{ $key }}" @selected(old('species') === $key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="caption">A line about them (optional)</label>
                <input id="caption" name="caption" class="input" maxlength="200" value="{{ old('caption') }}" placeholder="Steals socks, sleeps 20 hours a day">
            </div>
            <div class="field">
                <label class="field__label" for="photo">Photo</label>
                <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="input" required data-image-preview>
                <p class="field__hint">A clear photo of your own pet. JPG, PNG or WebP, up to 8 MB.</p>
                @error('photo')<p class="field__error">{{ $message }}</p>@enderror
            </div>
            <label class="checkbox"><input type="checkbox" name="own_photo" value="1" required @checked(old('own_photo'))> <span>This is my pet and I took this photo (or have permission to use it).</span></label>
            @error('own_photo')<p class="field__error">{{ $message }}</p>@enderror
            <label class="checkbox"><input type="checkbox" name="consent_feature" value="1" required @checked(old('consent_feature'))> <span>Petavilo can show this photo, my pet's name and my first name on the site and on Petavilo's social pages.</span></label>
            @error('consent_feature')<p class="field__error">{{ $message }}</p>@enderror
            <div class="row row--wrap"><button type="submit" class="button button--primary">Enter Pet of the Week</button></div>
        </div></form>
    </div>
</div>
@endsection
