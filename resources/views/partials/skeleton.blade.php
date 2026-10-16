{{-- A placeholder shaped like the board, shown while it loads. --}}
@php
    $skeletonTheme = in_array(config('fewchurs.theme.dark_mode'), ['light', 'dark'], true)
        ? config('fewchurs.theme.dark_mode')
        : null;
@endphp

@once
<style>
.fewchurs-skeleton {
    --sk-base: #e4e4e7;
    --sk-shine: #efeff1;
    --sk-card: #f4f4f5;
    display: flex; flex-direction: column; gap: 0.75rem;
    max-width: 44rem; margin: 0 auto;
}
@media (prefers-color-scheme: dark) {
    .fewchurs-skeleton:not([data-theme="light"]) { --sk-base: #232326; --sk-shine: #2e2e33; --sk-card: #161618; }
}
.fewchurs-skeleton[data-theme="dark"] { --sk-base: #232326; --sk-shine: #2e2e33; --sk-card: #161618; }
.fewchurs-skeleton__bar {
    border-radius: 0.5rem;
    background: linear-gradient(90deg, var(--sk-base) 25%, var(--sk-shine) 50%, var(--sk-base) 75%);
    background-size: 300% 100%;
    animation: fewchurs-shimmer 1.4s ease-in-out infinite;
}
.fewchurs-skeleton__card {
    display: flex; gap: 0.875rem; padding: 1rem;
    background: var(--sk-card); border-radius: var(--fewchurs-radius, 0.75rem);
}
.fewchurs-skeleton__lines { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; padding-top: 0.2rem; }
.fewchurs-skeleton__sr {
    position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap;
}
@keyframes fewchurs-shimmer {
    from { background-position: 100% 0; }
    to { background-position: -100% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .fewchurs-skeleton__bar { animation: none; }
}
</style>
@endonce

<div class="fewchurs-skeleton" role="status" @if($skeletonTheme) data-theme="{{ $skeletonTheme }}" @endif>
    <span class="fewchurs-skeleton__sr">{{ __('fewchurs::messages.loading') }}</span>

    @if($form ?? true)
        <div class="fewchurs-skeleton__bar" style="width: 45%; height: 0.8rem;"></div>
        <div class="fewchurs-skeleton__bar" style="height: 2.4rem;"></div>
        <div class="fewchurs-skeleton__bar" style="width: 30%; height: 0.8rem;"></div>
        <div class="fewchurs-skeleton__bar" style="height: 4.5rem;"></div>
        <div class="fewchurs-skeleton__bar" style="width: 8.5rem; height: 2.4rem;"></div>
        <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
            <div class="fewchurs-skeleton__bar" style="width: 3.2rem; height: 1.9rem; border-radius: 999px;"></div>
            <div class="fewchurs-skeleton__bar" style="width: 3.2rem; height: 1.9rem; border-radius: 999px;"></div>
        </div>
    @endif

    @foreach([70, 55, 80] as $width)
        <div class="fewchurs-skeleton__card" aria-hidden="true">
            <div class="fewchurs-skeleton__bar" style="width: 3rem; height: 3rem;"></div>
            <div class="fewchurs-skeleton__lines">
                <div class="fewchurs-skeleton__bar" style="width: {{ $width }}%; height: 0.95rem;"></div>
                <div class="fewchurs-skeleton__bar" style="width: 90%; height: 0.75rem;"></div>
                <div class="fewchurs-skeleton__bar" style="width: 35%; height: 0.7rem;"></div>
            </div>
        </div>
    @endforeach
</div>
