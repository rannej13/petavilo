<div class="chip-nav">
    <a href="{{ request()->fullUrlWithQuery(['species' => null, 'page' => null]) }}" class="pill {{ $species === null ? 'is-active' : '' }}">All</a>
    @foreach($filters as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['species' => $key, 'page' => null]) }}" class="pill {{ $species === $key ? 'is-active' : '' }}">{{ $label }}</a>
    @endforeach
</div>
