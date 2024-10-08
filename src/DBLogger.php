<?php

namespace FuryBee\LoggerUi;

use Monolog\Logger;

class DBLogger
{
    /**
     * Create a custom Monolog instance.
     *
     *
     * @param array $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('logger-ui');


        return $logger->pushHandler(new DBHandler());
    }
}
