<section class="section">
    <div class="grid grid--three">
        @if(\Illuminate\Support\Facades\Route::has('directory.index'))
            <a href="{{ route('directory.index') }}" class="card">
                @if($img = \Modules\CatMarket\Support\SiteImages::url('services'))<img class="card__image" src="{{ $img }}" alt="A groomer at work" loading="lazy">@endif
                <div class="card__body stack stack--tight">
                    <p class="text-eyebrow">{{ __('catmarket::messages.directory') }}</p>
                    <p class="title-card">Vets, sitters, groomers and catteries near you</p>
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
