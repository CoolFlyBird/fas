<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ReportBalanceModel;
use App\Models\ReportCashFlowModel;
use App\Models\ReportIncomeModel;
use App\Models\SubjectBalanceModel;
use App\Services\ReportService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

class SheetController extends Controller
{

    private $reportBalanceModel;
    private $reportIncomeModel;
    private $reportCashFlowModel;
    private $service;

    public function __construct(ReportBalanceModel $reportBalanceModel, ReportIncomeModel $reportIncomeModel, ReportCashFlowModel $reportCashFlowModel, ReportService $service)
    {
        $this->reportBalanceModel = $reportBalanceModel;
        $this->reportIncomeModel = $reportIncomeModel;
        $this->reportCashFlowModel = $reportCashFlowModel;
        $this->service = $service;
    }

    public function test()
    {
        $result = $this->service->calculateMonth('2019', '04');
        if ($result) {
            return $this->success($result);
        } else {
            return $this->fail($result);
        }
    }

    public function test1()
    {
        $result = $this->service->revokeMonth('2019', '04');
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
        $params = $request->only(['year', 'period']);
        $validator = Validator::make($params, [
            'year' => 'required|integer|min:2000',
            'period' => 'required|integer|min:0',
        ], [
            'year.required' => '年份不能为空',
            'year.integer' => '年份只能是整数',
            'year.min' => '年份不能小于2000',
            'period.required' => '会计周期不能为空',
            'period.integer' => '会计周期只能是整数',
            'period.min' => '会计周期不能小于0',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }
        $year = $params['year'] ?? 2000;
        $period = $params['period'] ?? 1;

        $result = $this->reportBalanceModel->loadResult($year, $period);
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
        $params = $request->only(['year', 'period']);
        $validator = Validator::make($params, [
            'year' => 'required|integer|min:2000',
            'period' => 'required|integer|min:0',
        ], [
            'year.required' => '年份不能为空',
            'year.integer' => '年份只能是整数',
            'year.min' => '年份不能小于2000',
            'period.required' => '会计周期不能为空',
            'period.integer' => '会计周期只能是整数',
            'period.min' => '会计周期不能小于0',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }
        $year = $params['year'] ?? 2000;
        $period = $params['period'] ?? 1;
        $result = $this->reportIncomeModel->loadResult($year, $period);
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
        $params = $request->only(['year', 'period']);
        $validator = Validator::make($params, [
            'year' => 'required|integer|min:2000',
            'period' => 'required|integer|min:0',
        ], [
            'year.required' => '年份不能为空',
            'year.integer' => '年份只能是整数',
            'year.min' => '年份不能小于2000',
            'period.required' => '会计周期不能为空',
            'period.integer' => '会计周期只能是整数',
            'period.min' => '会计周期不能小于0',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }
        $year = $params['year'] ?? 2000;
        $period = $params['period'] ?? 1;
        $result = $this->reportCashFlowModel->loadResult($year, $period);
        if ($result) {
            return $this->success($result);
        } else {
            return $this->fail($result);
        }
    }

}
