<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Requests\FundPostRequest;
use App\Services\FundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiFundController
{
    protected FundService $fundService;

    /**
     * ApiFundController constructor.
     *
     * @param FundService $fundService
     */
    public function __construct(FundService $fundService)
    {
        $this->fundService = $fundService;
    }

    /**
     * Create a new fund.
     *
     * @param FundPostRequest $request
     * @return JsonResponse
     */
    public function createFund(FundPostRequest $request): JsonResponse
    {
        $data = $request->validated();
        return $this->fundService->createFund($data);
    }

    /**
     * Read a fund.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function readFund(Request $request): JsonResponse
    {
        // Note: Depending on your logic, you may need some kind of validation here too.
        $data = $request->all();
        return $this->fundService->readFund($data);
    }

    /**
     * Update a fund.
     *
     * @param FundPostRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateFund(FundPostRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        return $this->fundService->updateFund($data, $id);
    }
}
