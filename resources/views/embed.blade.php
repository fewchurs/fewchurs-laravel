@php
    $mode = config('fewchurs.theme.dark_mode');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(in_array($mode, ['light', 'dark'], true)) data-theme="{{ $mode }}" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('fewchurs::messages.page_title') }}</title>
    <style>
        body {
            display: flex; flex-direction: column; box-sizing: border-box;
            min-height: 100vh; min-height: 100dvh; margin: 0; padding: 1.25rem 1.25rem 0;
            background: #ffffff; font-family: ui-sans-serif, system-ui, sans-serif;
        }
        @media (prefers-color-scheme: dark) { html:not([data-theme="light"]) body { background: #0b0b0c; } }
        html[data-theme="dark"] body { background: #0b0b0c; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
