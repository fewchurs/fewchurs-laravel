# Changelog

## 1.0.0 — unreleased

- Server-rendered feature-request board: `<x-fewchurs::board />`, a ready-made `/feedback` route, and a Livewire component.
- PHP client: `Fewchurs::features()`, `Fewchurs::comments()`, `Fewchurs::config()`.
- Per-visitor identity (cookie, user, or a custom resolver), all HMAC-hashed.
- CSRF, per-visitor throttling, honeypot, escaped output, and a key-leak test.
- `fewchurs:install`, `fewchurs:check`, `fewchurs:warm`.
- `Fewchurs::fake()` for host-app tests.
