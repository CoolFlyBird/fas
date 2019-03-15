<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\ReportBalanceService;
use App\Models\BalanceModel;

class BalanceController extends Controller
{

    public function balanceSheet(ReportBalanceService $service)
    {
        $result = $service->settleAccounts("2019", "01");
        if ($result) {
            return "success";
        } else {
            return "false";
        }
    }

}
