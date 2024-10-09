<?php

namespace FuryBee\LoggerUi\Http\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LogRepository
{
    private string $connection;
    private string $table;

    public function __construct()
    {
        $dbConfig = config('logger-ui.db');

        $this->connection = $dbConfig['connection'];
        $this->table = $dbConfig['table'];
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function getBuilder(array $filters = []): Builder
    {
        return DB::connection($this->connection)
            ->table($this->table)
            ->when(isset($filters['date_from']) === true, function (Builder $builder) use ($filters) {
                $date = Carbon::createFromFormat('Y-m-d', $filters['date_from'])->startOfDay();

                $builder->where('logged_at', '>=', $date);
            })
            ->when(isset($filters['date_to']) === true, function (Builder $builder) use ($filters) {
                $date = Carbon::createFromFormat('Y-m-d', $filters['date_to'])->endOfDay();

                $builder->where('logged_at', '<=', $date);
            })
            ->when(isset($filters['app_name']) === true, function (Builder $builder) use ($filters) {
                $builder->where('app_name', $filters['app_name']);
            })
            ->when(isset($filters['environment']) === true, function (Builder $builder) use ($filters) {
                $builder->where('environment', $filters['environment']);
            })
            ->when(isset($filters['channel']) === true, function (Builder $builder) use ($filters) {
                $builder->where('channel', $filters['channel']);
            })
            ->when(isset($filters['level_name']) === true, function (Builder $builder) use ($filters) {
                $builder->where('level_name', $filters['level_name']);
            })
            ->when(isset($filters['query']) === true, function (Builder $builder) use ($filters) {
                $query = $filters['query'];

                $builder->where(function (Builder $builder) use ($query) {
                    $builder->where('message', 'LIKE', "%{$query}%");
                    $builder->orWhere('context', 'LIKE', "%{$query}%");
                    $builder->orWhere('extra', 'LIKE', "%{$query}%");
                });
            });
    }

    public function getAvailableFilters(): array
    {
        $results = $this->getBuilder()
            ->selectRaw('DISTINCT app_name as value, "app_name" as type')
            ->unionAll(
                $this->getBuilder()
                    ->selectRaw('DISTINCT environment as value, "environment" as type')
            )
            ->unionAll(
                $this->getBuilder()
                    ->selectRaw('DISTINCT channel as value, "channel" as type')
            )
            ->unionAll(
                $this->getBuilder()
                    ->selectRaw('DISTINCT level_name as value, "level_name" as type')
            )
            ->get();

        $distinctValues = [
            'app_names' => [],
            'environments' => [],
            'channels' => [],
            'level_names' => [],
        ];

        foreach ($results as $result) {
            $distinctValues[$result->type.'s'][] = $result->value;
        }

        return $distinctValues;
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
            ->selectRaw('DISTINCT(environment) as environments')
            ->pluck('environments')
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
