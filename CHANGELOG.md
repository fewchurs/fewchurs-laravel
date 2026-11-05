# Changelog

## 1.5.3 — 2026-11-05

- While you test on localhost with `APP_DEBUG=true`, the board and the sheet show a small note reminding you to add your domain in the Fewchurs dashboard before going live. Visitors never see it.

## 1.5.2 — 2026-11-05

- A wrong key and a wrong app ID now show their own clear messages on the board and in `fewchurs:check`, with `APP_DEBUG` on or off.
- In the sheet, "Powered by Fewchurs" always stays at the bottom, and the sheet uses almost no borders.

## 1.5.1 — 2026-11-05

- With `APP_DEBUG=true` the board shows the real cause of a failure: a wrong key, a wrong app ID, no connection or a server error. With it off, visitors still see the plain message.

## 1.5.0 — 2026-11-04

- Sends the app ID so your API key only works in your own apps. Handles `wrong_app`.

## 1.4.1 — 2026-10-21

- Moves to the new domain: the API defaults to `https://www.fewchurs.com/api/v1`, and the "Powered by Fewchurs" link, homepages and license point to `https://www.fewchurs.com`.

## 1.4.0 — 2026-10-16

- Upvotes respond the moment you click: the button fills, the count changes and the arrow bounces, then the vote saves in the background. Quick repeat clicks settle on your last choice; a failed save puts the button back and shows the server's message.
- The Livewire board votes the same way and no longer re-renders the whole board after a vote; `toggleVote()` now returns the saved `hasVoted` and `upvoteCount`.
- Feature requests sit on a soft background with no border, and the vote button and status labels stand out on it.

## 1.3.1 — 2026-10-16

- Feature requests are a flat list separated by thin lines instead of boxed cards, and the loading shimmer matches.

## 1.3.0 — 2026-10-16

- The board no longer breaks the host page when the API fails with no recent copy: it shows a plain message and a "Try again" button inside the board (plain, embedded and Livewire).
- The sheet shows a shimmer shaped like the board while it loads, and again while moving between tabs or pages inside it.
- If the page inside the sheet fails, the sheet shows its own message with "Try again" and a link to the full page.
- `<livewire:fewchurs-board lazy />` shows the shimmer until it loads, and the Livewire board shimmers while switching tabs.

## 1.2.0 — 2026-10-16

- New `<x-fewchurs::sheet />`: a floating button that slides the board in from the right, left or bottom. `side`, `label`, `app`, `tab` and `:trigger="false"` with `data-fewchurs-open` for your own button.
- The board loads in the sheet only when it first opens, and falls back to a link to `/feedback` without JavaScript.
- `/feedback?embed=1` serves the bare board for the sheet, framed only by your own site.

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
