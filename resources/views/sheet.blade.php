@include('fewchurs::partials.sheet-assets')

<div class="fewchurs-sheet" data-side="{{ $side }}" data-fewchurs-sheet
     @if($darkMode) data-theme="{{ $darkMode }}" @endif style="{{ $styles }}">
    @if($trigger)
        {{-- A plain link first: without JavaScript it opens the full board page. --}}
        <a class="fewchurs-sheet__trigger" href="{{ $pageUrl }}" data-fewchurs-sheet-open aria-haspopup="dialog"
           aria-controls="{{ $id }}">
            @if($slot->isNotEmpty())
                {{ $slot }}
            @else
                {{ $label ?? __('fewchurs::messages.sheet.open') }}
            @endif
        </a>
    @endif

    <dialog class="fewchurs-sheet__panel" id="{{ $id }}" aria-labelledby="{{ $id }}-title">
        <div class="fewchurs-sheet__head">
            <h2 class="fewchurs-sheet__title" id="{{ $id }}-title">{{ __('fewchurs::messages.page_title') }}</h2>
            <button class="fewchurs-sheet__close" type="button" data-fewchurs-sheet-close
                    aria-label="{{ __('fewchurs::messages.sheet.close') }}">&times;</button>
        </div>
        <div class="fewchurs-sheet__body" data-state="loading">
            <div class="fewchurs-sheet__loading">
                @include('fewchurs::partials.skeleton')
            </div>

            <div class="fewchurs-sheet__failed" role="alert">
                <p class="fewchurs-sheet__failed-text">{{ __('fewchurs::messages.sheet.failed') }}</p>
                <button class="fewchurs-sheet__btn" type="button" data-fewchurs-sheet-retry>
                    {{ __('fewchurs::messages.retry') }}
                </button>
                <a class="fewchurs-sheet__link" href="{{ $pageUrl }}" target="_blank" rel="noopener">
                    {{ __('fewchurs::messages.sheet.open_page') }}
                </a>
            </div>

            <iframe class="fewchurs-sheet__frame" data-src="{{ $embedUrl }}"
                    title="{{ __('fewchurs::messages.page_title') }}"></iframe>
        </div>
    </dialog>
</div>
