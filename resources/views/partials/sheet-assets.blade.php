@once
<style>
.fewchurs-sheet {
    --fcs-bg: #ffffff;
    --fcs-text: #18181b;
    --fcs-border: #e5e5e7;
    --fcs-subtle: #6b7280;
    --fcs-primary: var(--fewchurs-primary, #ED5F18);
    --fcs-radius: var(--fewchurs-radius, 0.75rem);
}
@media (prefers-color-scheme: dark) {
    .fewchurs-sheet:not([data-theme="light"]) {
        --fcs-bg: #0b0b0c;
        --fcs-text: #f4f4f5;
        --fcs-border: #2a2a2e;
        --fcs-subtle: #a1a1aa;
    }
}
.fewchurs-sheet[data-theme="dark"] {
    --fcs-bg: #0b0b0c;
    --fcs-text: #f4f4f5;
    --fcs-border: #2a2a2e;
    --fcs-subtle: #a1a1aa;
}
.fewchurs-sheet__trigger {
    position: fixed; z-index: 2147483000; right: 1.25rem; bottom: 1.25rem;
    padding: 0.7rem 1.15rem; border-radius: 999px;
    background: var(--fcs-primary); color: #ffffff; text-decoration: none;
    font: 600 15px/1.2 ui-sans-serif, system-ui, sans-serif;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
}
.fewchurs-sheet[data-side="left"] .fewchurs-sheet__trigger { right: auto; left: 1.25rem; }
.fewchurs-sheet__trigger:focus-visible, .fewchurs-sheet__close:focus-visible {
    outline: 2px solid var(--fcs-primary); outline-offset: 2px;
}
.fewchurs-sheet__panel {
    position: fixed; margin: 0; padding: 0; border: 0;
    max-width: none; max-height: none; overflow: hidden;
    background: var(--fcs-bg); color: var(--fcs-text);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
}
.fewchurs-sheet__panel[open] {
    display: flex; flex-direction: column;
    transform: none; transition: transform 0.25s ease;
}
.fewchurs-sheet__panel::backdrop { background: rgba(0, 0, 0, 0.4); }
.fewchurs-sheet[data-side="right"] .fewchurs-sheet__panel {
    inset: 0 0 0 auto; width: min(28rem, 100vw); height: 100vh; height: 100dvh;
}
.fewchurs-sheet[data-side="left"] .fewchurs-sheet__panel {
    inset: 0 auto 0 0; width: min(28rem, 100vw); height: 100vh; height: 100dvh;
}
.fewchurs-sheet[data-side="bottom"] .fewchurs-sheet__panel {
    inset: auto 0 0 0; margin: 0 auto; width: min(48rem, 100vw); height: min(85vh, 48rem); height: min(85dvh, 48rem);
    border-radius: var(--fcs-radius) var(--fcs-radius) 0 0;
}
@starting-style {
    .fewchurs-sheet[data-side="right"] .fewchurs-sheet__panel[open] { transform: translateX(100%); }
    .fewchurs-sheet[data-side="left"] .fewchurs-sheet__panel[open] { transform: translateX(-100%); }
    .fewchurs-sheet[data-side="bottom"] .fewchurs-sheet__panel[open] { transform: translateY(100%); }
}
@media (prefers-reduced-motion: reduce) {
    .fewchurs-sheet__panel[open] { transition: none; }
}
html:has(.fewchurs-sheet__panel[open]) { overflow: hidden; }
.fewchurs-sheet__head {
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    padding: 0.75rem 0.75rem 0.75rem 1.25rem; border-bottom: 1px solid var(--fcs-border);
}
.fewchurs-sheet__title { margin: 0; font: 600 1rem/1.3 ui-sans-serif, system-ui, sans-serif; }
.fewchurs-sheet__close {
    width: 2.25rem; height: 2.25rem; border: 0; border-radius: 999px; cursor: pointer;
    background: transparent; color: var(--fcs-subtle); font-size: 1.5rem; line-height: 1;
}
.fewchurs-sheet__close:hover { color: var(--fcs-text); }
.fewchurs-sheet__frame { flex: 1; width: 100%; border: 0; background: var(--fcs-bg); }
</style>
<script>
(function () {
    function sheetFor(trigger) {
        return trigger.closest('[data-fewchurs-sheet]') || document.querySelector('[data-fewchurs-sheet]');
    }

    function open(sheet) {
        var dialog = sheet.querySelector('dialog');
        var frame = dialog.querySelector('iframe');
        if (!frame.getAttribute('src')) frame.setAttribute('src', frame.getAttribute('data-src'));
        if (!dialog.open) dialog.showModal();
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-fewchurs-sheet-open], [data-fewchurs-open]');
        if (trigger) {
            var sheet = sheetFor(trigger);
            // Old browsers without <dialog> follow the link to the full page instead.
            if (!sheet || typeof sheet.querySelector('dialog').showModal !== 'function') return;
            event.preventDefault();
            open(sheet);
            return;
        }

        var close = event.target.closest('[data-fewchurs-sheet-close]');
        if (close) {
            close.closest('dialog').close();
            return;
        }

        // A click on the dimmed backdrop lands on the dialog itself; its content fills it.
        if (event.target.matches('.fewchurs-sheet__panel')) event.target.close();
    });
})();
</script>
@endonce
