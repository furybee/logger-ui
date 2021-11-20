<?php

namespace FuryBee\LoggerUi\Http\Controllers;

use FuryBee\LoggerUi\Http\Resources\LogResource;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController
{
    const ALLOWED_FILTERS = [
        'app_name',
        'channel',
        'level_name',
        'query',
    ];

    const DEFAULT_FILTERS = [
        'app_name' => '',
        'channel' => '',
        'level_name' => '',
        'query' => '',
    ];

    protected $connection;
    protected $table;

    public function __construct()
    {
        $dbConfig = config('logger-ui.db');

        $this->connection = $dbConfig['connection'];
        $this->table = $dbConfig['table'];
    }

    private function getBuilder(): Builder
    {
        return DB::connection($this->connection)->table($this->table);
    }

    public function index(Request $request)
    {
        $filters = collect($request->only(self::ALLOWED_FILTERS))->filter()->toArray();

        $apps = $this
            ->getBuilder()
            ->selectRaw('DISTINCT(app_name) as apps')
            ->pluck('apps')
            ->toArray();

        $channels = $this
            ->getBuilder()
            ->selectRaw('DISTINCT(channel) as channels')
            ->pluck('channels')
            ->toArray();

        $levelNames = $this
            ->getBuilder()
            ->selectRaw('DISTINCT(level_name) as level_names')
            ->pluck('level_names')
            ->toArray();

        $lines = $this
            ->getBuilder()
            ->when(isset($filters['app_name']) === true, function (Builder $builder) use ($filters) {
                $builder->where('app_name', $filters['app_name']);
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
                });
            })
            ->orderByDesc('logged_at')
            ->limit(100)
            ->get()
            ->sortBy('logged_at');

        return [
            'lines' => LogResource::collection($lines),
            'available_filters' => [
                'app_names' => $apps,
                'channels' => $channels,
                'level_names' => $levelNames
            ],
            'default_filters' => array_merge(self::DEFAULT_FILTERS, $filters)
        ];
    }
}
