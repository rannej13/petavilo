@php
    $explore = collect([
        ['route' => 'listings.index', 'icon' => 'tag', 'title' => 'Pets for sale', 'text' => 'Kittens, puppies and more from checked sellers'],
        ['route' => 'rescue.adopt', 'icon' => 'heart', 'title' => 'Adopt a rescue pet', 'text' => 'Animals from verified rescues'],
        ['route' => 'rescue.lost-found', 'icon' => 'map-pin', 'title' => 'Lost & found', 'text' => 'Sightings map and free QR posters'],
        ['route' => 'directory.index', 'icon' => 'building', 'title' => 'Vets & pet services', 'text' => 'Groomers, sitters, walkers, kennels'],
        ['route' => 'pettools.match', 'icon' => 'sparkle', 'title' => 'Is this pet right for me?', 'text' => 'A 2-minute quiz with real costs'],
        ['route' => 'tv.index', 'icon' => 'video', 'title' => 'Petavilo TV', 'text' => 'Short pet videos and rescue stories'],
        ['route' => 'tv.potw', 'icon' => 'star', 'title' => 'Pet of the Week', 'text' => 'Enter your pet or vote'],
        ['route' => 'pettools.passport.about', 'icon' => 'shield', 'title' => 'Pet Passport', 'text' => 'Health records and an emergency QR pass'],
        ['route' => 'pettools.poison', 'icon' => 'check', 'title' => 'Is it poisonous?', 'text' => 'Check foods, plants and medicines'],
        ['route' => 'breeder.landing', 'icon' => 'list', 'title' => 'Breeder tools', 'text' => 'Free litter records and buyer handover'],
    ])->filter(fn (array $item): bool => \Illuminate\Support\Facades\Route::has($item['route']));
@endphp
@if($explore->isNotEmpty())
<section class="section" aria-labelledby="explore-title">
    <div class="section__head">
        <h2 class="title-section" id="explore-title">Everything on Petavilo</h2>
    </div>
    <div class="explore-grid">
        @foreach($explore as $item)
            <a href="{{ route($item['route']) }}" class="explore-tile">
                <span class="explore-tile__icon"><x-ui.icon :name="$item['icon']"/></span>
                <span class="explore-tile__body">
                    <span class="explore-tile__title">{{ $item['title'] }}</span>
                    <span class="explore-tile__text">{{ $item['text'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</section>
@endif
