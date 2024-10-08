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
use Illuminate\Support\Facades\Log;

class DBLoggerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public int $timeout = 300;

    /**
     * @var int
     */
    public int $tries = 3;

    /**
     * @var string
     */
    private string $dbConnection;

    /**
     * @var string
     */
    private string $dbTable;

    /**
     * @var array
     */
    private array $record;

    /**
     * ExportJob constructor.
     *
     * @param array $dbConfig
     * @param array $record
     */
    public function __construct(array $dbConfig, array $record)
    {
        $this->dbConnection = $dbConfig['connection'];
        $this->dbTable = $dbConfig['table'];

        $this->record = $record;
        $this->record['message'] = $this->formatMessage($record['message']);
        $this->record['context'] = $this->formatContext($record['context']);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \Throwable
     */
    public function handle(): void
    {
        try {
            retry(5, function () {
                $createdAt = new DateTimeImmutable("now", new \DateTimeZone('UTC'));

                $this->record['created_at'] = $createdAt->format('Y-m-d H:i:s.u');

                DB::transaction(function () {
                    DB::connection($this->dbConnection)->table($this->dbTable)->insert($this->record);
                });
            }, 100);
        } catch (Exception $exception) {
            // Write exception in emergency log :
            logger()->channel('single')
                ->emergency($exception->getMessage(), ['exception' => $exception]);
        }

    }

    /**
     *
     * @param $message
     * @return string
     */
    protected function formatMessage($message): string
    {
        if (method_exists($message, 'toArray')) {
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
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'name' => $exception::class,
                'message' => $exception->getMessage(),
                'stacktrace' => $exception->getTraceAsString(),
                'code' => $exception->getCode(),
            ];
        }

        return json_encode($context);
    }
}
