<?php

/**
 * Application Configuration
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When debug mode is enabled, detailed error messages will be shown.
    | In production, set this to false to show generic error pages.
    |
    */
    'debug' => wee::env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Default timezone for the application
    |
    */
    'timezone' => wee::env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your application
    |
    */
    'url' => wee::env('APP_URL', 'http://localhost'),
];
