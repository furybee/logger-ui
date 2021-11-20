<?php

use FuryBee\LoggerUi\Http\Middleware\EnsureUserIsAuthorized;

return [

    /*
    |--------------------------------------------------------------------------
    | Logger UI Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Logger UI route - giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => [
        'web',
        EnsureUserIsAuthorized::class,
    ],

    'app' => [
        'name' => env('APP_NAME', 'Laravel'),
    ],

    'db' => [
        'connection' => env('DB_CONNECTION', null),
        'table' => env('DB_LOGGER_UI_TABLE', 'logger_ui_entries'),
    ],

];
