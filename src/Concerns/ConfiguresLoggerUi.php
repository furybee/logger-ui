<?php

namespace FuryBee\LoggerUi\Concerns;

use Illuminate\Support\Facades\Config;

trait ConfiguresLoggerUi
{
    /**
     * Ensure Logger UI is properly configured.
     *
     * @return void
     */
    protected function ensureLoggerUiIsConfigured()
    {
        Config::set('logger-ui', array_merge([
            'db' => [
                'connection' => env('DB_CONNECTION', null),
                'table' => env('DB_LOGGER_UI_TABLE', null),
            ],
        ], Config::get('logger-ui') ?? []));
    }
}
