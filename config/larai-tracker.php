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
    | Initial Setup Token
    |--------------------------------------------------------------------------
    |
    | Required to create the first dashboard password outside local
    | development. Generate a long random value and remove it after setup.
    |
    */

    'setup_token' => env('LARAI_TRACKER_SETUP_TOKEN', null),

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
    | Price Catalog
    |--------------------------------------------------------------------------
    */

    'price_catalog_url' => env(
        'LARAI_TRACKER_PRICE_CATALOG_URL',
        'https://raw.githubusercontent.com/gometap/larai-tracker/main/resources/data/prices.json'
    ),
    'price_catalog_timeout' => 5,
];
