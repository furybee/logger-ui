<?php

namespace FuryBee\LoggerUi\Http\Controllers;

use FuryBee\LoggerUi\Http\Resources\LogResource;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController
{
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
        $params = collect($request->all())->filter();

        info($params);

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
            ->when(isset($params['channel']) === true, function (Builder $builder) use ($params) {
                $builder->where('channel', $params['channel']);
            })
            ->when(isset($params['level_name']) === true, function (Builder $builder) use ($params) {
                $builder->where('level_name', $params['level_name']);
            })
            ->when(isset($params['query']) === true, function (Builder $builder) use ($params) {
                $query = $params['query'];

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
                'channels' => $channels,
                'level_names' => $levelNames
            ]
        ];
    }
}
