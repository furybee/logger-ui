<?php

namespace FuryBee\LoggerUi\Jobs;

use DateTimeImmutable;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class DBLoggerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 300;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var string
     */
    private $dbConnection;

    /**
     * @var string
     */
    private $dbTable;

    /**
     * @var array
     */
    private $record;

    /**
     * ExportJob constructor.
     *
     * @param string $dsn
     * @param array $data
     */
    public function __construct(array $dbConfig, array $record)
    {
        $this->dbConnection = $dbConfig['connection'];
        $this->dbTable = $dbConfig['table'];

        $this->record = $record;
        $this->record['message'] = $this->formatMessage($record['message']);
        $this->record['context'] = $this->formatContext($record['context']);
    }

    public function handle(): void
    {
        retry(5, function () {
            $createdAt = new DateTimeImmutable();
            $this->record['created_at'] = $createdAt->format('Y-m-d H:i:s.u');

            DB::connection($this->dbConnection)->table($this->dbTable)->insert($this->record);
        }, 500);
    }

    /**
     *
     * @param array $message
     * @return string
     */
    protected function formatMessage($message): string
    {
        if ($message instanceof Collection || $message instanceof EloquentCollection || $message instanceof Arrayable) {
            $message = $message->toArray();
        }

        if (is_array($message) === true) {
            $message = json_encode($message);
        }

        return $message;
    }

    /**
     *
     * @param array $context
     * @return string
     */
    protected function formatContext(array $context): string
    {
        if (isset($context['exception']) === false) {
            return json_encode($context);
        }

        $exception = $context['exception'];

        if ($exception instanceof Exception) {
            $context['exception'] = [
                'name' => $exception::class,
                'message' => $exception->getMessage(),
                'stacktrace' => $exception->getTraceAsString(),
            ];
        }

        return json_encode($context);
    }
}
