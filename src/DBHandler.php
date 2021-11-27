<?php

namespace FuryBee\LoggerUi;

use DateTimeImmutable;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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
            'app_name'      => config('logger-ui.app.name'),
            'channel'       => $record['channel'],
            'level_name'    => $record['level_name'],
            'level'         => $record['level'],
            'message'       => $this->formatMessage($record['message']),
            'context'       => $this->formatContext($record['context']),
            'extra'         => json_encode($record['extra']),
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
