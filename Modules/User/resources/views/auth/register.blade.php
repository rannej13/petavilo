@extends('user::layouts.auth')

@section('title', __('site::messages.register'))
@section('auth_title', __('user::messages.create_account'))
@section('auth_lead', __('user::messages.register_lead'))

@section('auth_body')
<form method="POST" action="{{ route('register') }}" class="stack">
    @csrf
    {{ \App\Support\SpamGuard::fields() }}

    <div class="field">
        <label class="field__label" for="name">{{ __('user::messages.name') }}</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" class="input" required autofocus autocomplete="name"
               aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
        @error('name')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label class="field__label" for="email">{{ __('user::messages.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autocomplete="username"
               aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
        @error('email')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label class="field__label" for="password">{{ __('user::messages.password') }}</label>
        <input id="password" type="password" name="password" class="input" required autocomplete="new-password"
               aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
        <p class="field__hint">At least 8 characters.</p>
        @error('password')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label class="field__label" for="password_confirmation">{{ __('user::messages.confirm_password') }}</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="input" required autocomplete="new-password">
    </div>

    <div class="field">
        <label class="row" style="align-items:flex-start;gap:var(--space-2)">
            <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>
            <span>I agree to the <a href="{{ route('pages.show', 'terms') }}" class="text-link" target="_blank">terms</a> and <a href="{{ route('pages.show', 'privacy') }}" class="text-link" target="_blank">privacy policy</a>, and I am 18 or over.</span>
        </label>
        @error('terms')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <label class="row" style="align-items:flex-start;gap:var(--space-2)">
        <input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in'))>
        <span class="text-meta">Send me occasional emails about new features and cat care tips (optional).</span>
    </label>

    <button type="submit" class="button button--primary button--block button--large">{{ __('site::messages.register') }}</button>

    @include('user::auth.partials.social-buttons')
</form>
@endsection

@section('auth_footer')
{{ __('user::messages.have_account') }} <a href="{{ route('login') }}" class="text-link">{{ __('site::messages.login') }}</a>
@endsection
