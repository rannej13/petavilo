@props(['category'])
@php $photo = $category->photoUrl(); @endphp
<a href="{{ route('listings.index', ['category' => $category->getKey()]) }}" {{ $attributes->class(['category-tile']) }}>
    <span class="category-tile__media">
        @if($photo)
            <img src="{{ $photo }}" alt="" loading="lazy">
        @else
            <x-ui.icon name="grid"/>
        @endif
    </span>
    <span class="category-tile__name">{{ $category->getAttribute('name') }}</span>
</a>
