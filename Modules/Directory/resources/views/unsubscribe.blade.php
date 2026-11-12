@extends('site::layouts.app')

@section('title', __('directory::messages.unsubscribe_title'))

@section('content')
<div class="shell shell--form page">
    <div class="stack stack--loose">
        <h1 class="title-page">{{ __('directory::messages.unsubscribe_title') }}</h1>
        @if($done)
            <div class="notice notice--accent"><p>{{ __('directory::messages.unsubscribed', ['email' => $business->getAttribute('email')]) }}</p></div>
            <p class="text-muted">{{ __('directory::messages.unsubscribe_remove') }} <a class="text-link" href="mailto:{{ \Modules\CatMarket\Support\CatMarketSettings::contactEmail() }}?subject={{ rawurlencode('Remove listing: '.$business->getAttribute('name').' (#'.$business->getKey().')') }}">{{ __('directory::messages.remove_listing') }}</a></p>
        @else
            <form method="POST" action="{{ route('directory.unsubscribe.store', $token) }}" class="card">
                <div class="card__body stack">
                    <p>{{ __('directory::messages.unsubscribe_lead', ['email' => $business->getAttribute('email')]) }}</p>
                    <button type="submit" class="button button--primary">{{ __('directory::messages.unsubscribe_button') }}</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
