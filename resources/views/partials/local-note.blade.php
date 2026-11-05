@php
    [$noteStart, $noteEnd] = array_pad(explode(':dashboard', __('fewchurs::messages.local_note'), 2), 2, '');
@endphp
<p class="fewchurs__note">{{ $noteStart }}<a href="https://www.fewchurs.com/dashboard" target="_blank" rel="noopener noreferrer">{{ __('fewchurs::messages.local_note_link') }}</a>{{ $noteEnd }}</p>
