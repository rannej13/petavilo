<nav class="chip-nav" aria-label="Rescue and rehoming">
    <a href="{{ route('rescue.adopt') }}" class="pill {{ request()->routeIs('rescue.adopt') ? 'is-active' : '' }}">Adopt</a>
    <a href="{{ route('rescue.rehome') }}" class="pill {{ request()->routeIs('rescue.rehome') ? 'is-active' : '' }}">Rehome responsibly</a>
    <a href="{{ route('rescue.lost-found') }}" class="pill {{ request()->routeIs('rescue.lost-found') ? 'is-active' : '' }}">Lost &amp; found</a>
    <a href="{{ route('rescue.found-animal') }}" class="pill {{ request()->routeIs('rescue.found-animal') ? 'is-active' : '' }}">Found an animal?</a>
    <a href="{{ route('rescue.foster') }}" class="pill {{ request()->routeIs('rescue.foster') ? 'is-active' : '' }}">Foster</a>
    <a href="{{ route('rescue.rescues') }}" class="pill {{ request()->routeIs('rescue.rescues', 'rescue.show') ? 'is-active' : '' }}">Rescues</a>
    <a href="{{ route('rescue.welfare') }}" class="pill {{ request()->routeIs('rescue.welfare') ? 'is-active' : '' }}">Would you help?</a>
</nav>
