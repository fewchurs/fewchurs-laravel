# Fewchurs for Laravel

A feature-request board in any Laravel app: install, add one env var, drop one Blade tag.
Server-rendered by default, reactive with Livewire, and a plain PHP client for fully custom UIs.

```bash
composer require fewchurs/laravel
php artisan fewchurs:install
```

```dotenv
FEWCHURS_API_KEY=fr_live_…
```

```blade
<x-fewchurs::board />
```

That's it — the board renders, votes and comments work with JavaScript disabled, and your
API key never reaches the browser.

---

## What you get

| | |
|---|---|
| `<x-fewchurs::board />` | The board, anywhere in your own layout. |
| `/feedback` | A ready-made page, rendered inside your app's layout. |
| `<livewire:fewchurs-board />` | The same board, reactive, if you use Livewire. |
| `Fewchurs::features()` | The PHP client, for a UI you build yourself. |
| `php artisan fewchurs:check` | Answers "is my key right?" in one command. |

## Requirements

PHP 8.2+, Laravel 10, 11, 12 or 13. No dependencies beyond Laravel and Guzzle (which Laravel
already ships with), and no build step —
the board ships its own scoped CSS, so it does not assume Tailwind.

## Using the client

```php
use Fewchurs\Laravel\Facades\Fewchurs;

$page = Fewchurs::features()->top(limit: 20);
$page = Fewchurs::features()->latest(limit: 20, cursor: $page->nextCursor);

$feature = Fewchurs::features()->submit(
    title: 'Add dark mode',
    description: 'Would love a dark theme.',
    email: auth()->user()?->email,   // the server follows this email for you
);

Fewchurs::features()->vote($feature->id);
Fewchurs::features()->unvote($feature->id);
Fewchurs::features()->follow($feature->id, email: 'a@b.com');

Fewchurs::comments()->list($feature->id, limit: 50);
Fewchurs::comments()->create($feature->id, text: '+1', authorName: 'Sam');

Fewchurs::config()->showBranding;
```

Every call throws a typed exception on failure — `RateLimitedException`, `NotFoundException`,
`InvalidApiKeyException`, `ConnectionException` and friends, each with a
`userMessage()` that is safe to show a visitor.

## Identity

The server needs an `X-Device-Id` per visitor. The package resolves one for you:

```php
'identity' => ['mode' => env('FEWCHURS_IDENTITY', 'hybrid')], // cookie | user | hybrid
```

- **cookie** — a random value in a signed, `httpOnly`, `SameSite=Lax` cookie.
- **user / hybrid** — the signed-in user's id, so votes follow them across browsers and devices; guests fall back to the cookie.

Everything is HMAC-hashed with your `APP_KEY` before it leaves your server — the raw cookie,
the user id and the email never reach Fewchurs.

Multi-tenant or team-level identity:

```php
Fewchurs::resolveDeviceIdUsing(fn (Request $request) => hash('sha256', $request->user()->team_id));
```

Always hash. Passing an email or a raw id throws.

**Privacy policy note:** the cookie is functional — it records which requests this visitor
voted on — so most consent setups class it as necessary rather than tracking. It is still a
first-party identifier and belongs in your privacy policy. Sample wording:

> We set a `fewchurs_did` cookie so we can remember which feature requests you have voted on
> or followed. It contains a random identifier, not your name or email, and expires after a year.

## Customising

**1. Config** — `config/fewchurs.php` covers the route prefix, tabs, per-page count, whether
submissions, comments and follows are open, whether the email field is `hidden`, `optional` or
`required`, and whether writes require a signed-in user.

**2. Theme tokens**

```blade
<x-fewchurs::board :theme="['primary' => '#0F766E', 'radius' => '4px']" />
```

**3. Publish the views and the strings**

```bash
php artisan vendor:publish --tag=fewchurs-views
php artisan vendor:publish --tag=fewchurs-lang
```

The views are small, single-purpose Blade files meant to be edited. Translations are ordinary
Laravel lang files, so `lang/vendor/fewchurs/fr/messages.php` just works.

**4. Slots**

```blade
<x-fewchurs::board>
    <x-slot:header>
        <h1>Help shape {{ config('app.name') }}</h1>
    </x-slot:header>

    <x-slot:empty>
        <x-my-empty-illustration />
    </x-slot:empty>
</x-fewchurs::board>
```

**5. Headless** — set `route.prefix` to `null`, use the client, and build whatever you like.

### More than one board

```php
'apps' => ['docs' => ['api_key' => env('FEWCHURS_DOCS_KEY')]],
```

```blade
<x-fewchurs::board app="docs" />
```

```php
Fewchurs::app('docs')->features()->top();
```

### Events

```php
Event::listen(FeatureSubmitted::class, fn ($e) => Slack::send("New request: {$e->feature->title}"));
```

`FeatureSubmitted`, `Voted`, `Unvoted`, `Commented`, `Followed`, `Unfollowed`, `RequestFailed`.

## Testing your app

```php
$fake = Fewchurs::fake([['id' => 'feat1', 'title' => 'Add dark mode']]);

// … exercise your app …

$fake->assertSubmitted(fn ($feature) => $feature->title === 'Add dark mode');
$fake->assertVoted('feat1');
$fake->assertNothingSubmitted();
```

The fake sits behind the real client, so your tests exercise the same headers, validation and
error mapping as production.

## Security

The key is a real secret here, and the package keeps it that way.

- **The key never reaches the browser.** All calls are server-to-server; the browser talks only
  to your app's own routes. A test asserts the rendered HTML and JSON never contain it, and CI
  fails if a key-shaped string is committed anywhere in the repo.
- **It never reaches your logs either.** Every log line and exception message is scrubbed of
  bearer tokens and `fr_…` keys before it is written.
- **CSRF** on every write route (they run in the `web` group).
- **Throttling per visitor, not per IP** — a whole site shares one server IP, so writes are
  limited by the resolved device id (`route.throttle`, 30/min by default).
- **Honeypot plus a signed minimum-time check** on the submit and comment forms, so basic bots
  are dropped without a CAPTCHA. The timestamp is encrypted, so it cannot be forged or replayed.
- **Output is escaped.** Titles, descriptions and comments are written by strangers. The views
  use `{{ }}` throughout and say so in a comment — if you publish them, keep it that way.
- **Ids are validated** before they are interpolated into an API path, so a crafted id cannot
  reach a different endpoint.
- **Redirects are built from the package's own route**, never from the `Referer` header.
- **Device ids are stripped** from everything handed back to the page; features and comments
  carry `isMine` instead.
- **Theme values are allowlisted** before being inlined into a `style` attribute.
- **HTTPS is required** for the API base URL outside local development, with certificate
  verification on.

Set `board.require_auth` to `true` if the board should only accept writes from signed-in users.

## Commands

```bash
php artisan fewchurs:install   # publish config, print next steps
php artisan fewchurs:check     # verify the key, connectivity and plan
php artisan fewchurs:warm      # pre-warm the config cache after a deploy
```

## Caching

`/config` is cached app-wide for 5 minutes (`cache.config_ttl`), so plan changes and the
"Hide vote counts" switch show up quickly. Feature pages are cached per visitor for 15 seconds,
because `hasVoted` and `isFollowing` are per visitor and a shared entry would hand one
visitor's vote state to everyone. A longer-lived stale copy is kept so an unreachable API
degrades into "showing recent data" instead of an error page.

## License

Proprietary. You may use the package in your own apps to connect them to Fewchurs, and edit
the config, views and translations you publish. The source is public for transparency, not open
source. See [LICENSE](LICENSE).
