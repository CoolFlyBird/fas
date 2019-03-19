<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ReportBalanceModel;
use App\Models\ReportCashFlowModel;
use App\Models\ReportIncomeModel;
use App\Services\ReportBalanceService;
use App\Services\ReportCashFlowService;
use App\Services\ReportIncomeService;
use App\Services\ReportService;
use Illuminate\Http\Request;

class SheetController extends Controller
{
    /**
     * 测试服务 正常运行
     * @param ReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportTest(ReportService $service)
    {
        $result = $service->calculateMonth();
//        $result = $service->calculateYear();
        if ($result) {
            return $this->success($result);
        } else {
            return $this->fail($result);
        }
    }

    /**
     * 资产负债表 查询
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balanceSheet(Request $request)
    {
        $year = $request->only('year');
        $period = $request->only('period');
        $result = ReportBalanceModel::query()
            ->leftJoin('report_balance_name', 'report_balance_name.id', '=', 'report_balance.id')
            ->where(["year" => $year, "period" => $period])
            ->get();
        if ($result) {
            return $this->success($result);
        } else {
            return $this->fail($result);
        }
    }

    /**
     * 利润表 查询
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function incomeSheet(Request $request)
    {
        $year = $request->only('year');
        $period = $request->only('period');
        $result = ReportIncomeModel::query()
            ->leftJoin('report_income_name', 'report_income_name.id', '=', 'report_income.id')
            ->where(["year" => $year, "period" => $period])
            ->get();
        if ($result) {
            return $this->success($result);
        } else {
            return $this->fail($result);
        }
    }

    /**
     * 现金流量表 查询
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cashFlowSheet(Request $request)
    {
        $year = $request->only('year');
        $period = $request->only('period');
        $result = ReportCashFlowModel::query()
            ->leftJoin('report_cash_flow_name', 'report_cash_flow_name.id', '=', 'report_cash_flow.id')
            ->where(["year" => $year, "period" => $period])
            ->get();
        if ($result) {
            return $this->success($result);
        } else {
            return $this->fail($result);
        }
    }

}
