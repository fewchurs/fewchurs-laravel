@php($fewchursHoneypot = \Fewchurs\Laravel\Support\Honeypot::class)
@if($fewchursHoneypot::enabled())
    <div class="fewchurs__hp" aria-hidden="true">
        <label>
            {{ __('fewchurs::messages.honeypot') }}
            <input type="text" name="{{ $fewchursHoneypot::field() }}" tabindex="-1" autocomplete="off" value="">
        </label>
    </div>
    <input type="hidden" name="{{ $fewchursHoneypot::timestampField() }}" value="{{ $fewchursHoneypot::token() }}">
@endif
