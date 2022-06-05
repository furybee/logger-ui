<?php

namespace FuryBee\LoggerUi\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logger-ui:migrate {--singlestore=off : on/off}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create table for Logger UI';


    protected $config;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->config = config('logger-ui.db');

            $this->option('singlestore') === 'on' ? $this->handleSingleStore() : $this->handleNormal();
        } catch (Exception $exception) {
            $this->warn($exception->getMessage());

            return;
        }

        $this->info('Migration has been executed successfully!');
    }

    private function handleSingleStore()
    {
        $statement = file_get_contents(dirname(__FILE__) . '/sql/2021_11_11_000001_create_logger_ui_table_singlestore.sql');
        $statement = str_replace(':logger_ui_table_name:', $this->config['table'], $statement);


        DB::connection($this->config['connection'])->statement($statement);
    }

    private function handleNormal()
    {
        $schema = Schema::connection($this->config['connection']);

        $schema->create($this->config['table'], function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('app_name')->index();
            $table->string('environment')->index();
            $table->string('channel')->index();
            $table->string('level_name')->index();
            $table->string('level')->index();
            $table->longText('message')->nullable();
            $table->longText('context')->nullable();
            $table->longText('extra')->nullable();
            $table->string('user_id')->nullable();

            $table->dateTime('logged_at', 6)->index();
            $table->dateTime('created_at', 6)->index();
        });
    }
}
