<?php

namespace FuryBee\LoggerUi;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use FuryBee\LoggerUi\Concerns\ConfiguresLoggerUi;
use FuryBee\LoggerUi\Exceptions\MissingLoggerUiEnvException;

class LoggerUiServiceProvider extends ServiceProvider
{
    use ConfiguresLoggerUi;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->ensureEnvExists() === false) {
            throw new MissingLoggerUiEnvException('Please ensure env is correctly defined.');

            return;
        }

        Route::middlewareGroup('logger-ui', config('logger-ui.middleware', []));

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'logger-ui');

        $this->registerMigrations();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/logger-ui.php' => config_path('logger-ui.php'),
            ], 'logger-ui-config');

            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/logger-ui'),
            ], 'logger-ui-assets');

            $this->publishes([
                __DIR__ . '/../stubs/LoggerUiServiceProvider.stub' => app_path('Providers/LoggerUiServiceProvider.php'),
            ], 'logger-ui-provider');

            $this->commands([
                Console\InstallCommand::class,
                Console\PublishCommand::class,
                Console\ClearLogCommand::class
            ]);
        }
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * @throws Throwable
     */
    private function ensureEnvExists()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $dbConfig = config('logger-ui.db');

        return isset($dbConfig)
            && isset($dbConfig['connection'])
            && isset($dbConfig['table']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/logger-ui.php', 'logger-ui');

        $this->ensureLoggerUiIsConfigured();
    }
}
