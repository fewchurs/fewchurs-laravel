<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('fewchurs::messages.page_title') }}</title>
</head>
<body style="margin: 0; padding: 2rem 1rem; font-family: ui-sans-serif, system-ui, sans-serif;">
    @yield(config('fewchurs.board.section', 'content'))
</body>
</html>
