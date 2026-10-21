<?php

declare(strict_types=1);

return [

    'api_key' => env('FEWCHURS_API_KEY'),

    'base_url' => env('FEWCHURS_BASE_URL', 'https://www.fewchurs.com/api/v1'),

    /*
    | Extra boards, each with its own key: Fewchurs::app('docs') and
    | <x-fewchurs::board app="docs" />.
    */
    'apps' => [
        // 'docs' => ['api_key' => env('FEWCHURS_DOCS_KEY')],
    ],

    'http' => [
        'timeout' => 8,
        'connect_timeout' => 4,
        'retries' => 2,
        'retry_delay' => 200,
        'verify' => true,
    ],

    'identity' => [
        'mode' => env('FEWCHURS_IDENTITY', 'hybrid'),
        'cookie' => [
            'name' => 'fewchurs_did',
            'lifetime_days' => 365,
        ],
    ],

    'route' => [
        'prefix' => 'feedback',
        'name' => 'fewchurs.',
        'middleware' => ['web'],
        'throttle' => '30,1',
        'honeypot' => true,
    ],

    'board' => [
        'layout' => null,
        'section' => 'content',
        'tabs' => ['top', 'new'],   // a roadmap tab needs a status filter on /v1/features
        'default_tab' => 'top',
        'allow_submissions' => true,
        'allow_comments' => true,
        'allow_follow' => true,
        'email_field' => 'optional',
        'per_page' => 20,
        'require_auth' => false,
        'show_vote_counts' => true,
        'progressive_enhancement' => true,
    ],

    'theme' => [
        'primary' => '#ED5F18',
        'radius' => '0.75rem',
        'dark_mode' => 'auto',
    ],

    'cache' => [
        'store' => null,
        'config_ttl' => 300,     // plan changes and "Hide vote counts" show up within 5 minutes
        'list_ttl' => 15,
        'stale_ttl' => 86400,
    ],

    'honeypot' => [
        'field' => 'fewchurs_hp',
        'timestamp_field' => 'fewchurs_ts',
        'min_seconds' => 2,
        'max_seconds' => 10800,
    ],

    'logging' => [
        'channel' => null,
    ],

];
