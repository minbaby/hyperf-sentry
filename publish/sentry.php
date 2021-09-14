<?php

declare(strict_types=1);
return [
    'dsn' => env('SENTRY_DSN', ''),

    // capture release as git sha
    // 'release' => trim(exec('git --git-dir ' . base_path('.git') . ' log --pretty="%h" -n1 HEAD')),

    'environment' => env('APP_ENV'),

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#send-default-pii
    'send_default_pii' => false,
];
