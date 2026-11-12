<section class="card">
    <div class="card__head"><h2 class="card__title">{{ __('directory::messages.benefits_title') }}</h2></div>
    <div class="card__body">
        <div class="benefit-grid">
            <div class="stack stack--tight">
                <p class="title-card">{{ __('directory::messages.plan_free') }}</p>
                <ul class="check-list">
                    @foreach(__('directory::messages.benefits_free') as $line)<li>{{ $line }}</li>@endforeach
                </ul>
            </div>
            <div class="stack stack--tight">
                <p class="title-card">{{ __('directory::messages.plan_plus') }}</p>
                <ul class="check-list">
                    @foreach(__('directory::messages.benefits_plus') as $line)<li>{{ $line }}</li>@endforeach
                </ul>
            </div>
            <div class="stack stack--tight">
                <p class="title-card">{{ __('directory::messages.plan_premium') }}</p>
                <ul class="check-list">
                    @foreach(__('directory::messages.benefits_premium') as $line)<li>{{ $line }}</li>@endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
