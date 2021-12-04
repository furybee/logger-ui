<?php

namespace FuryBee\LoggerUi\Http\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LogRepository
{
    public function __construct()
    {
        $dbConfig = config('logger-ui.db');

        $this->connection = $dbConfig['connection'];
        $this->table = $dbConfig['table'];
    }

    /**
     *
     * @return Builder
     */
    public function getBuilder(): Builder
    {
        return DB::connection($this->connection)->table($this->table);
    }

    public function getAppList(): array
    {
        return $this
            ->getBuilder()
            ->selectRaw('DISTINCT(app_name) as apps')
            ->pluck('apps')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getEnvList(): array
    {
        return $this
            ->getBuilder()
            ->selectRaw('DISTINCT(environnement) as environnements')
            ->pluck('environnements')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getChannelList(): array
    {
        return $this
            ->getBuilder()
            ->selectRaw('DISTINCT(channel) as channels')
            ->pluck('channels')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getLevelNameList(): array
    {
        return $this
            ->getBuilder()
            ->selectRaw('DISTINCT(level_name) as level_names')
            ->pluck('level_names')
            ->sort()
            ->values()
            ->toArray();
    }
}
