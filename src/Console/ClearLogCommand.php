<?php

namespace FuryBee\LoggerUi\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logger-ui:clear {--from=} {--force=off : on/off}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear logs from a date';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $from = $this->option('from');

        if ($from === null) {
            $from = 'beginning';
        } else {
            $from = Carbon::parse($from)->toDateTimeString();
        }

        $this->comment('You are about to clear logs from ' . $from . '.');

        if ($this->option('from') === 'off' && $this->confirm('Are you sure?') === false) {
            return;
        }

        $dbConfig = config('logger-ui.db');

        if ($this->option('from') === null) {
            $this->comment('Truncating...');

            DB::connection($dbConfig['connection'])->table($dbConfig['table'])->truncate();

            $this->newLine();

            $this->info('Done.');

            return;
        }

        $this->comment('Deleting...');

        DB::connection($dbConfig['connection'])
            ->table($dbConfig['table'])
            ->where('logged_at', '>=', $from)
            ->delete();

        $this->newLine();

        $this->info('Done.');
    }
}
