@if(\Modules\Ads\Support\AdPlacement::enabledHere())
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ \Modules\Ads\Support\AdPlacement::client() }}" crossorigin="anonymous"></script>
@endif
