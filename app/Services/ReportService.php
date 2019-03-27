<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use App\Models\CurrentPeriodModel;
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

    /**
     * @var CurrentPeriodModel $currentPeriodModel
     */
    private $currentPeriodModel;

    public function __construct(CurrentPeriodModel $currentPeriodModel, ReportBalanceService $balanceService, ReportIncomeService $incomeService, ReportCashFlowService $cashFlowService)
    {
        $this->currentPeriodModel = $currentPeriodModel;
        $this->balanceService = $balanceService;
        $this->incomeService = $incomeService;
        $this->cashFlowService = $cashFlowService;
    }

    /**
     * 结账调用
     * @param $year
     * @param $period
     * @return bool 是否计算成功
     */
    public function calculateMonth($year, $period)
    {
        $currentYear = (int)$this->currentPeriodModel->getCurrentYear();
        $currentPeriod = (int)$this->currentPeriodModel->getCurrentPeriod();
        if ($currentYear === (int)$year && $currentPeriod === (int)$period) {//如果不是计算本期，则不计算
            if (((int)$period) === 12) {//12月份 计算年度和月度
                DB::beginTransaction();
                $result1 = $this->balanceService->calculateMonthBalance();
                $result2 = $this->incomeService->calculateMonthIncome();
                $result3 = $this->cashFlowService->calculateMonthCashFlow();

                $result4 = $this->balanceService->calculateYearBalance();
                $result5 = $this->incomeService->calculateYearIncome();
                $result6 = $this->cashFlowService->calculateYearCashFlow();
                if ($result1 && $result2 && $result3
                    && $result4 && $result5 && $result6) {
                    DB::commit();
                    return true;
                } else {
                    DB::rollBack();
                    return false;
                }
            } else {//计算月度
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
        }
        return false;
    }

    /**
     * 反结账使用
     * @param $year
     * @param $period
     * @return bool
     */
    public function revokeMonth($year, $period)
    {
        $currentYear = (int)$this->currentPeriodModel->getCurrentYear();
        $currentPeriod = (int)$this->currentPeriodModel->getCurrentPeriod();
        if ($currentYear === (int)$year && $currentPeriod === (int)$period) {//如果不是计算本期，则不反结账
            if (((int)$period) === 1) {//1月份 撤销年度和月度
                DB::beginTransaction();
                $result1 = $this->balanceService->revokeMonthBalance();
                $result2 = $this->incomeService->revokeMonthIncome();
                $result3 = $this->cashFlowService->revokeMonthCashFlow();

                $result4 = $this->balanceService->revokeYearBalance();
                $result5 = $this->incomeService->revokeYearIncome();
                $result6 = $this->cashFlowService->revokeYearCashFlow();
                if ($result1 && $result2 && $result3
                    && $result4 && $result5 && $result6) {
                    DB::commit();
                    return true;
                } else {
                    DB::rollBack();
                    return false;
                }
            } else {//撤销月度
                DB::beginTransaction();
                $result1 = $this->balanceService->revokeMonthBalance();
                $result2 = $this->incomeService->revokeMonthIncome();
                $result3 = $this->cashFlowService->revokeMonthCashFlow();

                if ($result1 && $result2 && $result3) {
                    DB::commit();
                    return true;
                } else {
                    DB::rollBack();
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * 科目余额表
     * @author huxinlu
     * @param $params
     * @return array
     */
    public function getSubjectBalanceList($params)
    {
        //科目等级
        switch ($params['grade']) {
            case 2:
                $params['length'] = 6;
                break;
            case 3:
                $params['length'] = 8;
                break;
            case 4:
                $params['length'] = 10;
                break;
            default:
                $params['length'] = 4;
                break;
        }

        if ($params['endPeriod'] == 0) {
            $params['endPeriod'] = $this->subjectBalanceModel->getYearMaxMonth();
        }

        $list = $this->subjectBalanceModel->getSubjectBalanceList($params);
        $data = [];
        foreach ($list['data'] as $k => $v) {
            $data[$k]['code'] = $v['code'];
            $data[$k]['name'] = $v['name'];

            //判断科目属于借还是贷
            $direction = $this->subjectModel->getDirectionByCode($v['code']);
            if ($direction == $this->subjectModel::DIRECTION_DEBIT) {
                $data[$k]['debitBeginBalance'] = $v['beginBalance'];
                $data[$k]['debitEndingBalance'] = $v['endingBalance'];
                $data[$k]['creditBeginBalance'] = $data[$k]['creditEndingBalance'] = 0.00;
            } else {
                $data[$k]['debitBeginBalance'] = $data[$k]['debitEndingBalance'] = 0.00;
                $data[$k]['creditBeginBalance'] = $v['beginBalance'];
                $data[$k]['creditEndingBalance'] = $v['endingBalance'];
            }

            //本期发生额
            $data[$k]['accrualDebitBalance'] = $v['debitBalance'];
            $data[$k]['accrualCreditBalance'] = $v['creditBalance'];

            //本年累计发生额
            $data[$k]['yearDebitBalance'] = $v['yearDebitBalance'];
            $data[$k]['yearCreditBalance'] = $v['yearCreditBalance'];
        }

        return $data;
    }
}
