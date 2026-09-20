@if($feature->isFollowing)
    <form method="POST" action="{{ route($routeName.'unfollow', ['feature' => $feature->id]) }}">
        @csrf
        @method('DELETE')
        @include('fewchurs::partials.board-input')
        <button class="fewchurs__link" type="submit"
                style="background: none; border: 0; padding: 0; font: inherit; cursor: pointer;">
            {{ __('fewchurs::messages.unfollow') }}
        </button>
    </form>
@elseif($openFeature === $feature->id)
    <form class="fewchurs__form fewchurs__form--inline" method="POST"
          action="{{ route($routeName.'follow', ['feature' => $feature->id]) }}" style="margin: 0; width: 100%;">
        @csrf
        @include('fewchurs::partials.board-input')
        <div class="fewchurs__field">
            <label class="fewchurs__label" for="fewchurs-follow-{{ $feature->id }}">
                {{ __('fewchurs::messages.follow_label') }}
            </label>
            <input class="fewchurs__input" id="fewchurs-follow-{{ $feature->id }}" name="email" type="email" required
                   maxlength="{{ \Fewchurs\Laravel\Validation\Rules::EMAIL_MAX }}" autocomplete="email"
                   value="{{ auth()->user()->email ?? '' }}">
            @error('email')
                <span class="fewchurs__error">{{ $message }}</span>
            @enderror
        </div>
        <button class="fewchurs__btn fewchurs__btn--ghost" type="submit" style="margin-top: 1.35rem;">
            {{ __('fewchurs::messages.follow') }}
        </button>
    </form>
@else
    <a class="fewchurs__link" href="{{ route($routeName.'index', $boardQuery + ['tab' => $tab, 'feature' => $feature->id]) }}#fewchurs-feature-{{ $feature->id }}">
        {{ __('fewchurs::messages.follow') }}
    </a>
@endif
