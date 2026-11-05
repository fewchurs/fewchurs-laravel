@php
    $theme = in_array(config('fewchurs.theme.dark_mode'), ['light', 'dark'], true)
        ? config('fewchurs.theme.dark_mode')
        : null;
    $boardQuery = ($board === \Fewchurs\Laravel\Fewchurs::DEFAULT_APP ? [] : ['board' => $board])
        + ($embed ? ['embed' => 1] : []);
    $showCounts = config('fewchurs.board.show_vote_counts', true) && ! $config->hideVoteCounts;
    $canSubmit = config('fewchurs.board.allow_submissions', true);
    $composing = $embed && $canSubmit && (
        request()->boolean('compose') || ($errors ?? null)?->hasAny(['title', 'description', 'email'])
    );
@endphp

@include('fewchurs::partials.styles')

<div class="{{ $embed ? 'fewchurs fewchurs--embed' : 'fewchurs' }}" @if($theme) data-theme="{{ $theme }}" @endif style="{{ $styles }}" id="fewchurs-board" data-fewchurs-board @if($embed)data-view="{{ $composing ? 'compose' : 'browse' }}"@endif>
    @isset($header)
        {{ $header }}
    @endisset

    @include('fewchurs::partials.alerts')

    @if($error !== null)
        @include('fewchurs::partials.load-error', ['retryUrl' => request()->fullUrl()])
    @else
        @if($localNote)
            @include('fewchurs::partials.local-note')
        @endif

        @if($canSubmit)
            <div class="fewchurs__compose">
                @if($embed)
                    <a class="fewchurs__back" href="{{ route($routeName.'index', $boardQuery + ['tab' => $tab]) }}" data-fewchurs-view="browse">
                        <span aria-hidden="true">&larr;</span> {{ __('fewchurs::messages.back') }}
                    </a>
                @endif

                @include('fewchurs::partials.submit-form')
            </div>
        @endif

        <div class="fewchurs__browse">
            @if(count($tabs) > 1)
                @include('fewchurs::partials.tabs')
            @endif

            @if($page->isStale)
                <p class="fewchurs__stale">{{ __('fewchurs::messages.stale') }}</p>
            @endif

            @if($page->isEmpty())
                @isset($empty)
                    {{ $empty }}
                @else
                    <p class="fewchurs__empty">{{ __('fewchurs::messages.empty') }}</p>
                @endisset
            @else
                <ul class="fewchurs__list">
                    @foreach($page->features as $feature)
                        @include('fewchurs::partials.feature-card', ['feature' => $feature])
                    @endforeach
                </ul>

                @if($page->nextCursor)
                    @include('fewchurs::partials.pagination')
                @endif
            @endif
        </div>

        @if($embed && $canSubmit)
            <a class="{{ $config->showBranding ? 'fewchurs__fab' : 'fewchurs__fab fewchurs__fab--low' }}"
               href="{{ route($routeName.'index', $boardQuery + ['tab' => $tab, 'compose' => 1]) }}" data-fewchurs-view="compose">
                <span aria-hidden="true">+</span> {{ __('fewchurs::messages.new_request') }}
            </a>
        @endif
    @endif

    @isset($footer)
        {{ $footer }}
    @endisset

    @if($config->showBranding)
        @include('fewchurs::partials.branding')
    @endif
</div>

@if(config('fewchurs.board.progressive_enhancement', true))
    @include('fewchurs::partials.scripts')
@endif
