<?php

namespace FuryBee\LoggerUi;

use DateTimeImmutable;
use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Illuminate\Support\Facades\DB;

class DBHandler extends AbstractProcessingHandler
{
    protected string $table;
    protected string $connection;

    /**
     *
     * @param [type] $level
     * @param boolean $bubble
     */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        $dbConfig = config('logger-ui.db');

        $this->table = $dbConfig['table'];
        $this->connection = $dbConfig['connection'];

        parent::__construct($level, $bubble);
    }

    /**
     *
     * @param array $record
     * @return void
     */
    protected function write(array $record): void
    {
        $data = array(
            'channel'       => $record['channel'],
            'level_name'    => $record['level_name'],
            'level'         => $record['level'],
            'message'       => $record['message'],
            'context'       => $this->formatContext($record['context']),
            'extra'         => json_encode($record['extra']),
            'formatted'     => $record['formatted'],
            'user_id'       => auth()->id(),
            'logged_at'     => $record['datetime']->format('Y-m-d H:i:s.u'),
            'created_at'    => null,
        );

        retry(3, function () use ($data) {
            $createdAt = new DateTimeImmutable();
            $data['created_at'] = $createdAt->format('Y-m-d H:i:s.u');

            DB::connection($this->connection)->table($this->table)->insert($data);
        }, 20);
    }

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
