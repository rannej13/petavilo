@extends('site::layouts.app')

@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => (string) $guide->getAttribute('title'),
        'description' => $guide->metaDescriptionValue(),
        'datePublished' => $guide->getAttribute('published_at')?->toAtomString(),
        'dateModified' => $guide->getAttribute('updated_at')?->toAtomString(),
        'author' => ['@type' => filled($guide->getAttribute('author_name')) ? 'Person' : 'Organization', 'name' => (string) ($guide->getAttribute('author_name') ?: config('catmarket.site_name'))],
    ];
@endphp

@section('title', $guide->metaTitleValue())
@section('description', $guide->metaDescriptionValue())

@section('content')
<div class="shell shell--narrow page">
    <article class="stack stack--loose">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('guides.index') }}">{{ __('guide::messages.guides') }}</a>
            <span class="breadcrumb__separator">/</span>
            <a href="{{ route('guides.index', ['type' => $guide->getAttribute('type')]) }}">{{ $guide->typeLabel() }}</a>
        </nav>

        <header class="stack stack--tight">
            <h1 class="title-page">{{ $guide->getAttribute('title') }}</h1>
            @if(filled($guide->getAttribute('excerpt')))
                <p class="text-lead">{{ $guide->getAttribute('excerpt') }}</p>
            @endif
            <p class="text-meta">
                @if(filled($guide->getAttribute('author_name'))){{ __('guide::messages.by', ['name' => $guide->getAttribute('author_name')]) }} · @endif
                @if($guide->getAttribute('reviewed_on')){{ __('guide::messages.reviewed', ['date' => $guide->getAttribute('reviewed_on')->isoFormat('LL')]) }}@endif
            </p>
        </header>

        @if($guide->getAttribute('has_affiliate_links') || filled($guide->getAttribute('sponsor_name')))
            <x-ads::affiliate-disclosure :sponsor="$guide->getAttribute('sponsor_name')"/>
        @endif

        @if($guide->getAttribute('type') === 'insurance')
            <aside class="notice notice--caution" role="note"><p>{{ __('guide::messages.insurance_notice') }}</p></aside>
        @endif

        <div class="guide-body">{!! $intro !!}</div>

        <x-ads::slot name="guide_top"/>

        @if($rest !== '')
            <div class="guide-body">{!! $rest !!}</div>
        @endif

        <x-ads::slot name="guide_bottom"/>

        @if($related->isNotEmpty())
            <section class="stack">
                <h2 class="title-section">{{ __('guide::messages.related') }}</h2>
                <div class="grid grid--three">
                    @foreach($related as $item)
                        <a href="{{ route('guides.show', $item->getAttribute('slug')) }}" class="card">
                            <div class="card__body"><p class="title-card">{{ $item->getAttribute('title') }}</p></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</div>
<script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endsection
