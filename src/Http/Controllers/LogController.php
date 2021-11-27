<?php

namespace FuryBee\LoggerUi\Http\Controllers;

use Carbon\Carbon;
use FuryBee\LoggerUi\Http\Requests\LogIndexRequest;
use FuryBee\LoggerUi\Http\Resources\LogResource;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LogController
{
    const ALLOWED_FILTERS = [
        'date',
        'app_name',
        'channel',
        'level_name',
        'query',
        'per_page',
    ];

    const DEFAULT_FILTERS = [
        'date' => '',
        'app_name' => '',
        'channel' => '',
        'level_name' => '',
        'query' => '',
        'per_page' => 300,
    ];

    protected $connection;
    protected $table;

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
    private function getBuilder(): Builder
    {
        return DB::connection($this->connection)->table($this->table);
    }

    /**
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date',
            'app_name' => 'nullable|string',
            'channel' => 'nullable|string',
            'level_name' => 'nullable|string',
            'query' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response('Validation Error.', 422);
        }

        $filters = collect($request->only(self::ALLOWED_FILTERS))
            ->filter()
            ->toArray();

        $filters = collect($request->only(self::ALLOWED_FILTERS))
            ->filter()
            ->toArray();

        $apps = $this
            ->getBuilder()
            ->selectRaw('DISTINCT(app_name) as apps')
            ->pluck('apps')
            ->sort()
            ->values()
            ->toArray();

        $channels = $this
            ->getBuilder()
            ->selectRaw('DISTINCT(channel) as channels')
            ->pluck('channels')
            ->sort()
            ->values()
            ->toArray();

        $levelNames = $this
            ->getBuilder()
            ->selectRaw('DISTINCT(level_name) as level_names')
            ->pluck('level_names')
            ->sort()
            ->values()
            ->toArray();

        $paginator = $this
            ->getBuilder()
            ->when(isset($filters['date']) === true, function (Builder $builder) use ($filters) {
                $date = Carbon::createFromFormat('Y-m-d', $filters['date'])->endOfDay();

                $builder->where('logged_at', '<=', $date);
            })
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
            ->simplePaginate(self::DEFAULT_FILTERS['per_page'])
            ->setPageName('page');

        $paginator = $paginator->toArray();

        $data = $paginator['data'];

        unset($paginator['data']);

        return [
            'pagination' => $paginator,
            'lines' => LogResource::collection($data),
            'available_filters' => [
                'app_names' => $apps,
                'channels' => $channels,
                'level_names' => $levelNames
            ],
        ];
    }
}
