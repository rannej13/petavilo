@if($televet)
    <div class="card"><div class="card__body stack stack--tight">
        <h3 class="title-card">Talk to a vet by video</h3>
        <p class="text-muted">Can't get to your vet, or your cat hates the carrier? A video vet can tell you how urgent it is and what to do next.</p>
        <a href="{{ $televet['url'] }}" class="button button--secondary" rel="sponsored noopener" target="_blank">Speak to {{ $televet['name'] }}</a>
        <p class="text-meta">Partner link. We may earn a commission at no extra cost to you. A video call is not a substitute for an emergency vet visit.</p>
    </div></div>
@endif
