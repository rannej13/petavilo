@props(['sponsor' => null])

<aside {{ $attributes->merge(['class' => 'notice notice--accent']) }} role="note">
    @if($sponsor)
        <p class="notice__title">{{ __('ads::messages.sponsored_by', ['name' => $sponsor]) }}</p>
    @endif
    <p>{{ __('ads::messages.affiliate_disclosure') }}
        @if(\Illuminate\Support\Facades\Route::has('pages.show'))
            <a class="text-link" href="{{ route('pages.show', 'affiliate-disclosure') }}">{{ __('ads::messages.how_we_earn') }}</a>
        @endif
    </p>
</aside>
