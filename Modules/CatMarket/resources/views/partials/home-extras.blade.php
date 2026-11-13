@if(\Illuminate\Support\Facades\Route::has('rescue.adopt'))
    <section class="section">
        <div class="stack">
            <h2 class="title-section">Rescue, rehoming and lost pets</h2>
            <div class="grid grid--three">
                <a href="{{ route('rescue.adopt') }}" class="card">
                    @if($img = \Modules\CatMarket\Support\SiteImages::url('rehoming'))<img class="card__image" src="{{ $img }}" alt="A rescue cat waiting for a home" loading="lazy">@endif
                    <div class="card__body stack stack--tight">
                        <p class="text-eyebrow">Adopt</p>
                        <p class="title-card">Pets from verified rescues and charities</p>
                    </div>
                </a>
                <a href="{{ route('rescue.rehome') }}" class="card">
                    @if($img = \Modules\CatMarket\Support\SiteImages::url('dogs'))<img class="card__image" src="{{ $img }}" alt="A puppy and an adult dog at home" loading="lazy">@endif
                    <div class="card__body stack stack--tight">
                        <p class="text-eyebrow">Rehome responsibly</p>
                        <p class="title-card">Need to rehome your pet? Your options, free</p>
                    </div>
                </a>
                <a href="{{ route('rescue.lost-found') }}" class="card">
                    @if($img = \Modules\CatMarket\Support\SiteImages::url('lost-found'))<img class="card__image" src="{{ $img }}" alt="A lost pet poster" loading="lazy">@endif
                    <div class="card__body stack stack--tight">
                        <p class="text-eyebrow">Lost &amp; found</p>
                        <p class="title-card">Report a lost or found pet free, or <span class="text-link">found an injured animal?</span></p>
                    </div>
                </a>
            </div>
        </div>
    </section>
@endif
<section class="section">
    <div class="grid grid--three">
        @if(\Illuminate\Support\Facades\Route::has('directory.index'))
            <a href="{{ route('directory.index') }}" class="card">
                @if($img = \Modules\CatMarket\Support\SiteImages::url('services'))<img class="card__image" src="{{ $img }}" alt="A groomer at work" loading="lazy">@endif
                <div class="card__body stack stack--tight">
                    <p class="text-eyebrow">{{ __('catmarket::messages.directory') }}</p>
                    <p class="title-card">Vets, rescues, sitters, groomers and kennels near you</p>
                </div>
            </a>
        @endif
        @if(\Illuminate\Support\Facades\Route::has('guides.index'))
            <a href="{{ route('guides.index') }}" class="card">
                @if($img = \Modules\CatMarket\Support\SiteImages::url('products'))<img class="card__image" src="{{ $img }}" alt="Pet supplies" loading="lazy">@endif
                <div class="card__body stack stack--tight">
                    <p class="text-eyebrow">{{ __('catmarket::messages.guides') }}</p>
                    <p class="title-card">Breed guides, new pet checklists and product comparisons</p>
                </div>
            </a>
        @endif
        <a href="{{ route('pages.show', 'safety-tips') }}" class="card">
            @if($img = \Modules\CatMarket\Support\SiteImages::url('kittens'))<img class="card__image" src="{{ $img }}" alt="Kittens with their mother in a basket" loading="lazy">@endif
            <div class="card__body stack stack--tight">
                <p class="text-eyebrow">{{ __('catmarket::messages.read_safety') }}</p>
                <p class="title-card">How to spot scams and poorly bred puppies and kittens</p>
            </div>
        </a>
    </div>
</section>
@if(\Illuminate\Support\Facades\Route::has('community.index') && \Illuminate\Support\Facades\Schema::hasTable('community_threads'))
    @php
        $communityLatest = \Modules\Community\Models\Thread::query()->visible()->with(['board', 'user'])->latest('last_post_at')->limit(5)->get();
    @endphp
    @if($communityLatest->isNotEmpty())
        <section class="section">
            <div class="stack">
                <div class="forum-hero">
                    <div class="stack stack--tight">
                        <p class="text-eyebrow">{{ __('community::messages.community') }}</p>
                        <h2 class="title-section">{{ __('community::messages.latest') }}</h2>
                    </div>
                    <a href="{{ route('community.index') }}" class="button button--secondary">{{ __('community::messages.boards') }}</a>
                </div>
                <div class="card forum-list">
                    @foreach($communityLatest as $thread)
                        @include('community::partials.thread-row', ['thread' => $thread, 'showBoard' => true])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endif
