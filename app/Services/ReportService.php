<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @var ReportBalanceService $balanceService
     */
    private $balanceService;

    /**
     * @var ReportCashFlowService $cashFlowService
     */
    private $cashFlowService;

    /**
     * @var ReportIncomeService $incomeService
     */
    private $incomeService;

    public function __construct(ReportBalanceService $balanceService, ReportIncomeService $incomeService, ReportCashFlowService $cashFlowService)
    {
        $this->balanceService = $balanceService;
        $this->incomeService = $incomeService;
        $this->cashFlowService = $cashFlowService;
    }

    /**
     * 每月调用一次
     * @return bool
     */
    public function calculateMonth()
    {
        DB::beginTransaction();
        $result1 = $this->balanceService->calculateMonthBalance();
        $result2 = $this->incomeService->calculateMonthIncome();
        $result3 = $this->cashFlowService->calculateMonthCashFlow();
        if ($result1 && $result2 && $result3) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            return false;
        }
    }

    /**
     *
     * 每年调用一次
     * @return bool
     */
    public function calculateYear()
    {
        DB::beginTransaction();
        $result1 = $this->balanceService->calculateYearBalance();
        $result2 = $this->incomeService->calculateYearIncome();
        $result3 = $this->cashFlowService->calculateYearCashFlow();
        if ($result1 && $result2 && $result3) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            return false;
        }
    }

}
