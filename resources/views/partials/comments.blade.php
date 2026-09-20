<div class="fewchurs__comments">
    @forelse($comments->comments as $comment)
        @include('fewchurs::partials.comment', ['comment' => $comment])
    @empty
        <p class="fewchurs__desc">{{ __('fewchurs::messages.no_comments') }}</p>
    @endforelse

    @if($comments->hasMore())
        <a class="fewchurs__link"
           href="{{ route($routeName.'index', $boardQuery + ['tab' => $tab, 'feature' => $feature->id, 'cursor' => $comments->nextCursor]) }}">
            {{ __('fewchurs::messages.load_more') }}
        </a>
    @endif

    @if(config('fewchurs.board.allow_comments', true))
        <form class="fewchurs__form" method="POST"
              action="{{ route($routeName.'comment', ['feature' => $feature->id]) }}" style="margin: 0;">
            @csrf
            @include('fewchurs::partials.board-input')
            @include('fewchurs::partials.honeypot')
            <div class="fewchurs__field">
                <label class="fewchurs__label" for="fewchurs-comment-{{ $feature->id }}">
                    {{ __('fewchurs::messages.form.comment') }}
                </label>
                <textarea class="fewchurs__textarea" id="fewchurs-comment-{{ $feature->id }}" name="text" required
                          minlength="{{ \Fewchurs\Laravel\Validation\Rules::COMMENT_MIN }}"
                          maxlength="{{ \Fewchurs\Laravel\Validation\Rules::COMMENT_MAX }}">{{ old('text') }}</textarea>
                @error('text')
                    <span class="fewchurs__error">{{ $message }}</span>
                @enderror
            </div>
            @guest
                <div class="fewchurs__field">
                    <label class="fewchurs__label" for="fewchurs-author-{{ $feature->id }}">
                        {{ __('fewchurs::messages.form.name') }}
                        <span style="color: var(--fc-subtle); font-weight: 400;">{{ __('fewchurs::messages.form.optional') }}</span>
                    </label>
                    <input class="fewchurs__input" id="fewchurs-author-{{ $feature->id }}" name="author_name" type="text"
                           maxlength="{{ \Fewchurs\Laravel\Validation\Rules::AUTHOR_NAME_MAX }}" value="{{ old('author_name') }}">
                    @error('author_name')
                        <span class="fewchurs__error">{{ $message }}</span>
                    @enderror
                </div>
            @endguest
            <div>
                <button class="fewchurs__btn" type="submit">{{ __('fewchurs::messages.form.post_comment') }}</button>
            </div>
        </form>
    @endif
</div>
