<?php

namespace FuryBee\LoggerUi;

use FuryBee\LoggerUi\Jobs\DBLoggerJob;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DBHandler extends AbstractProcessingHandler
{
    /**
     *
     * @param [type] $level
     * @param boolean $bubble
     */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * @param \Monolog\LogRecord $record
     * @return void
     */
    protected function write(\Monolog\LogRecord $record): void
    {
        $config = config('logger-ui');

        $record = $record->toArray();

        $data = array(
            'app_name' => $config['app']['name'],
            'environment' => config('app.env'),
            'channel' => $record['channel'],
            'level_name' => $record['level_name'],
            'level' => $record['level'],
            'message' => $record['message'],
            'context' => $record['context'],
            'extra' => json_encode($record['extra']),
            'user_id' => optional(auth())->id(),
            'logged_at' => $record['datetime']->format('Y-m-d H:i:s.u'),
            'created_at' => null,
        );

        $job = new DBLoggerJob($config['db'], $data);

        if (isset($config['queue']['active']) === true && $config['queue']['active'] === true) {
            if (isset($config['queue']['name']) === true) {
                $job->onQueue($config['queue']['name']);
            }

            dispatch($job);

            return;
        }

        dispatch_sync($job);
    }
}
