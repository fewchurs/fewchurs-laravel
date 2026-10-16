{{-- Shown in place of the list when the API fails and there is no recent copy to fall back to. --}}
<div class="fewchurs__failed" role="alert">
    <p class="fewchurs__failed-title">{{ __('fewchurs::messages.load_failed') }}</p>
    <p class="fewchurs__failed-text">{{ $error }}</p>
    @isset($retryUrl)
        <a class="fewchurs__btn fewchurs__btn--ghost" href="{{ $retryUrl }}" style="text-decoration: none;">
            {{ __('fewchurs::messages.retry') }}
        </a>
    @else
        <button class="fewchurs__btn fewchurs__btn--ghost" type="button" wire:click="$refresh">
            {{ __('fewchurs::messages.retry') }}
        </button>
    @endisset
</div>
