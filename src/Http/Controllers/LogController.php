<?php

namespace FuryBee\LoggerUi\Http\Controllers;

use Carbon\Carbon;
use FuryBee\LoggerUi\Http\Repositories\LogRepository;
use FuryBee\LoggerUi\Http\Resources\LogResource;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogController
{
    const ALLOWED_FILTERS = [
        'date',
        'app_name',
        'environnement',
        'channel',
        'level_name',
        'query',
        'per_page',
    ];

    const DEFAULT_FILTERS = [
        'date' => '',
        'app_name' => '',
        'environnement' => '',
        'channel' => '',
        'level_name' => '',
        'query' => '',
        'per_page' => 300,
    ];

    protected $connection;
    protected $table;
    protected $logRepository;

    /**
     *
     * @param LogRepository $logRepository
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
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
            'environnement' => 'nullable|string',
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

        $paginator = $this->logRepository->getBuilder()
            ->when(isset($filters['date']) === true, function (Builder $builder) use ($filters) {
                $date = Carbon::createFromFormat('Y-m-d', $filters['date'])->endOfDay();

                $builder->where('logged_at', '<=', $date);
            })
            ->when(isset($filters['app_name']) === true, function (Builder $builder) use ($filters) {
                $builder->where('app_name', $filters['app_name']);
            })
            ->when(isset($filters['environnement']) === true, function (Builder $builder) use ($filters) {
                $builder->where('environnement', $filters['environnement']);
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
            ->setPageName('page')
            ->toArray();

        $data = $paginator['data'];

        unset($paginator['data']);

        return [
            'pagination' => $paginator,
            'lines' => LogResource::collection($data),
            'available_filters' => [
                'app_names' => $this->logRepository->getAppList(),
                'environnements' => $this->logRepository->getEnvList(),
                'channels' => $this->logRepository->getChannelList(),
                'level_names' => $this->logRepository->getLevelNameList()
            ],
        ];
    }
}
