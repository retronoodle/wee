<?php

/**
 * Database Configuration
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Database Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "mysql", "pgsql", "sqlite"
    |
    */
    'driver' => wee::env('DB_DRIVER', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connection Settings
    |--------------------------------------------------------------------------
    */
    'host' => wee::env('DB_HOST', 'localhost'),
    'port' => wee::env('DB_PORT', '3306'),
    'database' => wee::env('DB_DATABASE', 'wee'),
    'username' => wee::env('DB_USERNAME', 'root'),
    'password' => wee::env('DB_PASSWORD', ''),
    'charset' => wee::env('DB_CHARSET', 'utf8mb4'),
    'collation' => wee::env('DB_COLLATION', 'utf8mb4_unicode_ci'),

    /*
    |--------------------------------------------------------------------------
    | SQLite Path (if using sqlite)
    |--------------------------------------------------------------------------
    */
    'sqlite_path' => wee::env('DB_SQLITE_PATH', __DIR__ . '/../database.sqlite'),
];
