@props(['name', 'format' => 'auto', 'layout' => null])

@if(\Modules\Ads\Support\AdPlacement::shows($name))
    <div {{ $attributes->merge(['class' => 'ad-slot ad-slot--'.$name]) }} aria-label="{{ __('ads::messages.advertisement') }}">
        <span class="ad-slot__label">{{ __('ads::messages.advertisement') }}</span>
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="{{ \Modules\Ads\Support\AdPlacement::client() }}"
             data-ad-slot="{{ \Modules\Ads\Support\AdPlacement::slotId($name) }}"
             data-ad-format="{{ $format }}"
             @if($layout) data-ad-layout-key="{{ $layout }}" @endif
             data-full-width-responsive="true"></ins>
    </div>
@endif
