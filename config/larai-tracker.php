<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Password
    |--------------------------------------------------------------------------
    |
    | A single password to protect the Larai Tracker dashboard.
    | Set via ENV: LARAI_TRACKER_PASSWORD=your_secret
    | Or override from the Settings page (stored in DB, takes highest priority).
    |
    | When null and not in "local" environment, access is denied until a
    | password is configured.
    |
    */

    'password' => env('LARAI_TRACKER_PASSWORD', null),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime (minutes)
    |--------------------------------------------------------------------------
    |
    | How long the authenticated session lasts before requiring re-login.
    |
    */

    'session_lifetime' => 120,

    /*
    |--------------------------------------------------------------------------
    | Login Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Maximum failed login attempts before lockout. The lockout will last for
    | the number of minutes specified in 'lockout_minutes'.
    |
    */

    'max_attempts' => 5,

    'lockout_minutes' => 15,
];
