<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\ReportBalanceService;
use App\Models\ReportBalanceModel;
use App\Services\ReportIncomeService;

class SheetController extends Controller
{

    public function balanceSheet(ReportBalanceService $service)
    {
        $result = $service->calculateMonthBalance();
        if ($result) {
            return "success";
        } else {
            return "false";
        }
    }

    public function incomeSheet(ReportIncomeService $service)
    {
        $result = $service->calculateMonthIncome();
        if ($result) {
            return "success";
        } else {
            return "false";
        }
    }

}
