@once
<style>
.fewchurs {
    --fc-bg: #ffffff;
    --fc-surface: #ffffff;
    --fc-muted: #f6f6f7;
    --fc-border: #e5e5e7;
    --fc-text: #18181b;
    --fc-subtle: #6b7280;
    --fc-primary: var(--fewchurs-primary, #ED5F18);
    --fc-radius: var(--fewchurs-radius, 0.75rem);
    --fc-danger: #b91c1c;
    color: var(--fc-text);
    font-size: 15px;
    line-height: 1.5;
    max-width: 44rem;
    margin: 0 auto;
}
@media (prefers-color-scheme: dark) {
    .fewchurs:not([data-theme="light"]) {
        --fc-bg: #0b0b0c;
        --fc-surface: #161618;
        --fc-muted: #1f1f22;
        --fc-border: #2a2a2e;
        --fc-text: #f4f4f5;
        --fc-subtle: #a1a1aa;
    }
}
.fewchurs[data-theme="dark"] {
    --fc-bg: #0b0b0c;
    --fc-surface: #161618;
    --fc-muted: #1f1f22;
    --fc-border: #2a2a2e;
    --fc-text: #f4f4f5;
    --fc-subtle: #a1a1aa;
}
.fewchurs * { box-sizing: border-box; }
.fewchurs__tabs { display: flex; gap: 0.25rem; margin-bottom: 1rem; }
.fewchurs__tab {
    padding: 0.4rem 0.85rem; border-radius: 999px; text-decoration: none;
    color: var(--fc-subtle); font-weight: 500; font-size: 0.9rem;
}
.fewchurs__tab[aria-current="page"] { background: var(--fc-muted); color: var(--fc-text); }
.fewchurs__tab:focus-visible, .fewchurs__btn:focus-visible, .fewchurs__vote:focus-visible,
.fewchurs__input:focus-visible, .fewchurs__link:focus-visible {
    outline: 2px solid var(--fc-primary); outline-offset: 2px;
}
.fewchurs__list { display: flex; flex-direction: column; gap: 0.75rem; list-style: none; padding: 0; margin: 0; }
.fewchurs__card {
    display: flex; gap: 0.875rem; padding: 1rem;
    background: var(--fc-surface); border: 1px solid var(--fc-border); border-radius: var(--fc-radius);
}
.fewchurs__vote {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-width: 3rem; padding: 0.4rem 0.5rem; gap: 0.1rem; cursor: pointer;
    background: var(--fc-muted); border: 1px solid transparent; border-radius: 0.6rem;
    color: var(--fc-text); font: inherit; line-height: 1.1;
}
.fewchurs__vote[aria-pressed="true"] { border-color: var(--fc-primary); color: var(--fc-primary); }
.fewchurs__vote-count { font-weight: 600; font-size: 0.95rem; }
.fewchurs__vote-arrow { font-size: 0.7rem; }
.fewchurs__body { flex: 1; min-width: 0; }
.fewchurs__title { margin: 0 0 0.25rem; font-size: 1rem; font-weight: 600; overflow-wrap: anywhere; }
.fewchurs__desc { margin: 0; color: var(--fc-subtle); font-size: 0.9rem; overflow-wrap: anywhere; white-space: pre-line; }
.fewchurs__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; margin-top: 0.6rem; font-size: 0.82rem; color: var(--fc-subtle); }
.fewchurs__status {
    display: inline-block; padding: 0.1rem 0.5rem; border-radius: 999px;
    background: var(--fc-muted); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.01em;
}
.fewchurs__status--planned { color: #1d4ed8; }
.fewchurs__status--in_progress { color: #b45309; }
.fewchurs__status--done { color: #15803d; }
.fewchurs__status--declined { color: var(--fc-danger); }
.fewchurs__link { color: var(--fc-subtle); text-decoration: none; }
.fewchurs__link:hover { color: var(--fc-text); }
.fewchurs__form { display: flex; flex-direction: column; gap: 0.6rem; margin: 0 0 1.25rem; }
.fewchurs__form--inline { flex-direction: row; flex-wrap: wrap; align-items: flex-start; }
.fewchurs__field { display: flex; flex-direction: column; gap: 0.25rem; flex: 1; min-width: 12rem; }
.fewchurs__label { font-size: 0.82rem; font-weight: 600; }
.fewchurs__input, .fewchurs__textarea {
    width: 100%; padding: 0.55rem 0.7rem; font: inherit; color: var(--fc-text);
    background: var(--fc-bg); border: 1px solid var(--fc-border); border-radius: 0.55rem;
}
.fewchurs__textarea { min-height: 5rem; resize: vertical; }
.fewchurs__btn {
    padding: 0.55rem 1rem; font: inherit; font-weight: 600; cursor: pointer;
    color: #fff; background: var(--fc-primary); border: 1px solid transparent; border-radius: 0.55rem;
}
.fewchurs__btn--ghost { background: transparent; color: var(--fc-subtle); border-color: var(--fc-border); }
.fewchurs__alert { padding: 0.65rem 0.85rem; border-radius: 0.55rem; margin-bottom: 1rem; font-size: 0.9rem; }
.fewchurs__alert--ok { background: var(--fc-muted); }
.fewchurs__alert--error { background: var(--fc-muted); color: var(--fc-danger); }
.fewchurs__error { color: var(--fc-danger); font-size: 0.8rem; }
.fewchurs__empty { padding: 2.5rem 1rem; text-align: center; color: var(--fc-subtle); }
.fewchurs__comments { margin-top: 0.9rem; padding-top: 0.9rem; border-top: 1px solid var(--fc-border); display: flex; flex-direction: column; gap: 0.75rem; }
.fewchurs__comment { font-size: 0.88rem; }
.fewchurs__comment-author { font-weight: 600; }
.fewchurs__comment-text { margin: 0.15rem 0 0; overflow-wrap: anywhere; white-space: pre-line; }
.fewchurs__badge { font-size: 0.7rem; font-weight: 600; color: var(--fc-primary); }
.fewchurs__pagination { display: flex; justify-content: center; margin-top: 1.25rem; }
.fewchurs__branding { margin-top: 1.5rem; text-align: center; font-size: 0.78rem; color: var(--fc-subtle); }
.fewchurs__hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
.fewchurs__failed {
    display: flex; flex-direction: column; align-items: center; gap: 0.35rem;
    padding: 2.5rem 1rem; text-align: center;
    border: 1px dashed var(--fc-border); border-radius: var(--fc-radius);
}
.fewchurs__failed-title { margin: 0; font-weight: 600; }
.fewchurs__failed-text { margin: 0 0 0.75rem; color: var(--fc-subtle); font-size: 0.9rem; }
.fewchurs__stale { font-size: 0.78rem; color: var(--fc-subtle); margin-bottom: 0.75rem; }
</style>
@endonce
