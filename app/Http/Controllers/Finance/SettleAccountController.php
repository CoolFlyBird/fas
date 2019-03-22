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

    public function settleAccount()
    {
        $res = $this->financeService->settleAccount();

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    public function checkout()
    {
        dd(99);
    }
}
