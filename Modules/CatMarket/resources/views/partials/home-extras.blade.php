<section class="section">
    <div class="grid grid--three">
        @if(\Illuminate\Support\Facades\Route::has('directory.index'))
            <a href="{{ route('directory.index') }}" class="card">
                <div class="card__body stack stack--tight">
                    <p class="text-eyebrow">{{ __('catmarket::messages.directory') }}</p>
                    <p class="title-card">Vets, sitters, groomers and catteries near you</p>
                </div>
            </a>
        @endif
        @if(\Illuminate\Support\Facades\Route::has('guides.index'))
            <a href="{{ route('guides.index') }}" class="card">
                <div class="card__body stack stack--tight">
                    <p class="text-eyebrow">{{ __('catmarket::messages.guides') }}</p>
                    <p class="title-card">Breed guides, kitten checklists and product comparisons</p>
                </div>
            </a>
        @endif
        <a href="{{ route('pages.show', 'safety-tips') }}" class="card">
            <div class="card__body stack stack--tight">
                <p class="text-eyebrow">{{ __('catmarket::messages.read_safety') }}</p>
                <p class="title-card">How to spot scams and poorly bred kittens</p>
            </div>
        </a>
    </div>
</section>
