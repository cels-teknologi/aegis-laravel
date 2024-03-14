<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project Authentication
    |--------------------------------------------------------------------------
    |
    | This is your project details which is used for authentication.
    | It contains a project slug name in the form of {team}-{project-name}
    | and a token which contains at least 128 characters.
    |
    | Check your project details by visiting https://aegis.cels.co.id.
    |
    */

    'project' => [
        'slug' => env('AEGIS_PROJECT_SLUG'),
        'token' => env('AEGIS_PROJECT_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Here you may specify which environment Aegis will handle exceptions
    | for reporting.
    |
    | If you're not sure, set this to the same value of APP_ENV variable.
    |
    */

    'environments' => [
        env('APP_ENV', 'production'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Versioning Scheme
    |--------------------------------------------------------------------------
    |
    | Here you may specify the versioning scheme used in your project to track
    | exceptions and possible fixes or regressions.
    |
    | Leave it by default to automatically fetch with Git commit SHA
    |
    */

    'release' => env('AEGIS_RELEASE'),

    /*
    |--------------------------------------------------------------------------
    | Distributions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the distribution for reporting, if there are more
    | than a single client (e.g. to differentiate from native applications).
    |
    */

    'dist' => env('AEGIS_DIST', 'laravel'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify the HTTP Connection used for communication.
    |
    */

    'http' => [
        'base_uri' => env('AEGIS_BASE_URI', 'https://aegis.cels.co.id/'),
        'endpoint' => env('AEGIS_ENDPOINT', '/api/report'),
        'verify_ssl' => !!env('AEGIS_VERIFY_SSL', true),
        'timeout' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Data Collection
    |--------------------------------------------------------------------------
    |
    | Here you may toggle the data collection performed for currently
    | authenticated user.
    |
    | If you're not sure, the default value is good enough.
    |
    */

    'user' => [
        'collect' => true,
        'force' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Directories
    |--------------------------------------------------------------------------
    |
    | Here you may specify the folders to ignore, use relative paths from the
    | project directory.
    |
    | If you're not sure, the default value is good enough.
    |
    */

    'ignore' => [
        'vendor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of lines before & after the exception
    | instantiated.
    |
    */

    'lines' => env('AEGIS_LINES', 15),

    /*
    |--------------------------------------------------------------------------
    | Sampling Rate
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of sampling rate, in decimal.
    |
    | The default value will report 10% of all exceptions in production,
    | otherwise 0%.
    |
    */

    'rate' => env(
        'AEGIS_RATE',
        env('APP_ENV', 'production') === 'production'
            ? 0.1
            : 0,
    ),
];
