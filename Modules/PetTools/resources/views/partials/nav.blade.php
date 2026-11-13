<nav class="chip-nav" aria-label="Pet tools">
    <a href="{{ route('pettools.index') }}" class="pill {{ request()->routeIs('pettools.index') ? 'is-active' : '' }}">All tools</a>
    <a href="{{ route('pettools.poison') }}" class="pill {{ request()->routeIs('pettools.poison') ? 'is-active' : '' }}">Is it poisonous?</a>
    <a href="{{ route('pettools.pain') }}" class="pill {{ request()->routeIs('pettools.pain') ? 'is-active' : '' }}">Pain check</a>
    <a href="{{ route('pettools.food') }}" class="pill {{ request()->routeIs('pettools.food') ? 'is-active' : '' }}">Food matcher</a>
    <a href="{{ route('pettools.calories') }}" class="pill {{ request()->routeIs('pettools.calories') ? 'is-active' : '' }}">Calories</a>
    <a href="{{ route('pettools.passport.about') }}" class="pill {{ request()->routeIs('pettools.passport.*') ? 'is-active' : '' }}">Pet Passport</a>
</nav>
