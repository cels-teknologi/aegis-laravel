<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project Authentication
    |--------------------------------------------------------------------------
    |
    | This is your project details which is used for authentication.
    | It contains a project key and a token.
    |
    | Check your project details by visiting https://aegis.cels.co.id.
    |
    */

    'project' => [
        'key' => env('AEGIS_PROJECT_KEY'),
        'token' => env('AEGIS_PROJECT_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Report
    |--------------------------------------------------------------------------
    |
    | Here you may specify Aegis reporting settings:
    |  - only_throwables: Only report if it is an instance of \Throwable,
    |                     default is true.
    |  - environments: Environments Aegis should handle logging,
    |                  separated by commas, default is 'production'.
    |  - rate: The sampling rate in decimal
    |          of Log::() calls to report, e.g.
    |     - 0.1 means 10% (default)
    |     - 0 means to never report
    |     - 1 means to always report
    |
    */

    'only_throwables' => true,
    'environments' => \explode(',', env('AEGIS_REPORT_ENV', 'production')),
    'rate' => env(
        'AEGIS_RATE',
        env('APP_ENV', 'production') === 'production'
            ? 0.1
            : 0,
    ),

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
        'endpoint' => env('AEGIS_ENDPOINT', '/api/_report'),
        'verify_ssl' => (bool) env('AEGIS_VERIFY_SSL', true),
        'timeout' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Collection
    |--------------------------------------------------------------------------
    |
    | Here you may configure the data collection performed when reporting.
    |
    */

    'collect' => [
        'env' => false,
        'user' => (bool) env('AEGIS_COLLECT_USER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Directories
    |--------------------------------------------------------------------------
    |
    | Here you may specify the folders to ignore, use relative paths from the
    | project root directory.
    | 
    | Files in ignored directories are still logged but will not be considered
    | as the "error-source" (a.k.a. your code that causes an exception).
    |
    */

    'ignore' => \explode(',', env('AEGIS_IGNORE_DIRECTORIES', '')),

    /*
    |--------------------------------------------------------------------------
    | Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of lines before & after for file preview.
    |
    */

    'lines' => env('AEGIS_LINES', 15),

];
