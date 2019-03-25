<?php

namespace App\Http\Controllers\Finance;

use App\Services\FinanceService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettleAccountController extends Controller
{
    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * 结账
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function settleAccount()
    {
        $res = $this->financeService->settleAccount();

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 反结账
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout()
    {
        $res = $this->financeService->checkout();

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }
}
