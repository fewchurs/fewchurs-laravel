@php
    $emailField = config('fewchurs.board.email_field', 'optional');
    $rules = \Fewchurs\Laravel\Validation\Rules::class;
@endphp

<form class="fewchurs__form" method="POST" action="{{ route($routeName.'submit') }}" data-fewchurs-form>
    @csrf
    @include('fewchurs::partials.board-input')
    @include('fewchurs::partials.honeypot')

    <div class="fewchurs__field">
        <label class="fewchurs__label" for="fewchurs-title">{{ __('fewchurs::messages.form.title') }}</label>
        <input class="fewchurs__input" id="fewchurs-title" name="title" type="text" required
               minlength="{{ $rules::TITLE_MIN }}" maxlength="{{ $rules::TITLE_MAX }}"
               placeholder="{{ __('fewchurs::messages.form.title_placeholder') }}"
               value="{{ old('title') }}"
               @error('title') aria-invalid="true" aria-describedby="fewchurs-title-error" @enderror>
        @error('title')
            <span class="fewchurs__error" id="fewchurs-title-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="fewchurs__field">
        <label class="fewchurs__label" for="fewchurs-description">{{ __('fewchurs::messages.form.description') }}</label>
        <textarea class="fewchurs__textarea" id="fewchurs-description" name="description"
                  maxlength="{{ $rules::DESCRIPTION_MAX }}"
                  placeholder="{{ __('fewchurs::messages.form.description_placeholder') }}">{{ old('description') }}</textarea>
        @error('description')
            <span class="fewchurs__error">{{ $message }}</span>
        @enderror
    </div>

    @if($emailField !== 'hidden')
        <div class="fewchurs__field">
            <label class="fewchurs__label" for="fewchurs-email">
                {{ __('fewchurs::messages.form.email') }}
                @if($emailField === 'optional')
                    <span class="fewchurs__error" style="color: var(--fc-subtle); font-weight: 400;">
                        {{ __('fewchurs::messages.form.optional') }}
                    </span>
                @endif
            </label>
            <input class="fewchurs__input" id="fewchurs-email" name="email" type="email"
                   maxlength="{{ $rules::EMAIL_MAX }}" autocomplete="email"
                   @if($emailField === 'required') required @endif
                   value="{{ old('email', auth()->user()->email ?? '') }}">
            <span class="fewchurs__error" style="color: var(--fc-subtle);">
                {{ __('fewchurs::messages.form.email_hint') }}
            </span>
            @error('email')
                <span class="fewchurs__error">{{ $message }}</span>
            @enderror
        </div>
    @endif

    <div>
        <button class="fewchurs__btn" type="submit">{{ __('fewchurs::messages.form.submit') }}</button>
    </div>
</form>
