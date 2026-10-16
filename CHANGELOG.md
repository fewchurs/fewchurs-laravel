# Changelog

## 1.1.0 — 2026-10-16

- Supports Laravel 10, 11, 12 and 13 (was 11 and 12 only).
- Requires `guzzlehttp/guzzle` (7 or 8) directly, since Laravel 10 does not install it for you.
- CI now tests every supported Laravel and PHP pair.

## 1.0.0 — 2026-10-16

- Server-rendered feature-request board: `<x-fewchurs::board />`, a ready-made `/feedback` route, and a Livewire component.
- PHP client: `Fewchurs::features()`, `Fewchurs::comments()`, `Fewchurs::config()`.
- Per-visitor identity (cookie, user, or a custom resolver), all HMAC-hashed.
- CSRF, per-visitor throttling, honeypot, escaped output, and a key-leak test.
- `fewchurs:install`, `fewchurs:check`, `fewchurs:warm`.
- `Fewchurs::fake()` for host-app tests.
- Defaults to the live API at `https://www.fewchurs.shop/api/v1`.
- `fewchurs:check` and `fewchurs:warm` now fail on a rejected key or an unreachable API instead of reporting success.
- `/config` is cached for 5 minutes, so plan changes and "Hide vote counts" show up quickly.
- `isMine` comes from the server when it sends one.
- Released under the proprietary Fewchurs SDK License.
