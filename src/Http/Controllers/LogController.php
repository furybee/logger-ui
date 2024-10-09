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
        'environment',
        'channel',
        'level_name',
        'query',
        'per_page',
    ];

    const DEFAULT_FILTERS = [
        'date' => '',
        'app_name' => '',
        'environment' => '',
        'channel' => '',
        'level_name' => '',
        'query' => '',
        'per_page' => 300,
    ];

    protected LogRepository $logRepository;

    /**
     *
     * @param LogRepository $logRepository
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date',
            'app_name' => 'nullable|string',
            'environment' => 'nullable|string',
            'channel' => 'nullable|string',
            'level_name' => 'nullable|string',
            'query' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = collect($request->only(self::ALLOWED_FILTERS))
            ->filter()
            ->toArray();

        $paginator = $this->logRepository->getBuilder($filters)
            ->orderByDesc('logged_at')
            ->paginate(self::DEFAULT_FILTERS['per_page'])
            ->setPageName('page')
            ->toArray();

        $data = $paginator['data'];

        unset($paginator['data']);

        return response()->json([
            'pagination' => $paginator,
            'lines' => LogResource::collection($data),
            'available_filters' => [
                'app_names' => $this->logRepository->getAppList(),
                'environments' => $this->logRepository->getEnvList(),
                'channels' => $this->logRepository->getChannelList(),
                'level_names' => $this->logRepository->getLevelNameList()
            ],
        ]);
    }
}
