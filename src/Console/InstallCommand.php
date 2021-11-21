<?php

namespace FuryBee\LoggerUi\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logger-ui:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Logger UI resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Logger UI Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'logger-ui-provider']);

        $this->comment('Publishing Logger UI Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'logger-ui-assets']);

        $this->registerLoggerUiServiceProvider();

        $this->info('Logger UI scaffolding installed successfully.');
    }

    /**
     * Register the Logger UI service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerLoggerUiServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace . '\\Providers\\LoggerUiServiceProvider::class')) {
            return;
        }

        $lineEndingCount = [
            "\r\n" => substr_count($appConfig, "\r\n"),
            "\r" => substr_count($appConfig, "\r"),
            "\n" => substr_count($appConfig, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\RouteServiceProvider::class," . $eol,
            "{$namespace}\\Providers\RouteServiceProvider::class," . $eol . "        {$namespace}\Providers\LoggerUiServiceProvider::class," . $eol,
            $appConfig
        ));

        file_put_contents(app_path('Providers/LoggerUiServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/LoggerUiServiceProvider.php'))
        ));
    }
}
