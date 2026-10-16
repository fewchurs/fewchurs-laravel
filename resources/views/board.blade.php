@php
    $theme = in_array(config('fewchurs.theme.dark_mode'), ['light', 'dark'], true)
        ? config('fewchurs.theme.dark_mode')
        : null;
    $boardQuery = ($board === \Fewchurs\Laravel\Fewchurs::DEFAULT_APP ? [] : ['board' => $board])
        + ($embed ? ['embed' => 1] : []);
    $showCounts = config('fewchurs.board.show_vote_counts', true) && ! $config->hideVoteCounts;
@endphp

@include('fewchurs::partials.styles')

<div class="fewchurs" @if($theme) data-theme="{{ $theme }}" @endif style="{{ $styles }}" id="fewchurs-board">
    @isset($header)
        {{ $header }}
    @endisset

    @include('fewchurs::partials.alerts')

    @if(config('fewchurs.board.allow_submissions', true))
        @include('fewchurs::partials.submit-form')
    @endif

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
