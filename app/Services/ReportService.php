<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use App\Models\CurrentPeriodModel;
use App\Models\SubjectBalanceModel;
use App\Models\SubjectModel;
use App\Models\VoucherDetailModel;
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

    public function __construct(ReportBalanceService $balanceService, ReportIncomeService $incomeService,
                                ReportCashFlowService $cashFlowService, SubjectBalanceModel $subjectBalanceModel,
                                SubjectModel $subjectModel, VoucherDetailModel $voucherDetailModel, CurrentPeriodModel $currentPeriodModel)
    {
        $this->balanceService      = $balanceService;
        $this->incomeService       = $incomeService;
        $this->cashFlowService     = $cashFlowService;
        $this->subjectBalanceModel = $subjectBalanceModel;
        $this->subjectModel        = $subjectModel;
        $this->voucherDetailModel  = $voucherDetailModel;
        $this->currentPeriodModel  = $currentPeriodModel;
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
                $data[$k]['debitBeginBalance']  = $v['beginBalance'];
                $data[$k]['debitEndingBalance'] = $v['endingBalance'];
                $data[$k]['creditBeginBalance'] = $data[$k]['creditEndingBalance'] = 0.00;
            } else {
                $data[$k]['debitBeginBalance']   = $data[$k]['debitEndingBalance'] = 0.00;
                $data[$k]['creditBeginBalance']  = $v['beginBalance'];
                $data[$k]['creditEndingBalance'] = $v['endingBalance'];
            }

            //本期发生额
            $data[$k]['accrualDebitBalance']  = $v['debitBalance'];
            $data[$k]['accrualCreditBalance'] = $v['creditBalance'];

            //本年累计发生额
            $data[$k]['yearDebitBalance']  = $v['yearDebitBalance'];
            $data[$k]['yearCreditBalance'] = $v['yearCreditBalance'];
        }

        return $data;
    }

    /**
     * 明细账
     * @author huxinlu
     * @param $params
     * @return array
     */
    public function getBalanceDetailList($params)
    {
        if ($params['startPeriod'] == 0) {
            $params['startPeriod'] = $this->currentPeriodModel->getCurrentPeriod();
        }
        if ($params['endPeriod'] == 0) {
            $params['endPeriod'] = $this->currentPeriodModel->getCurrentPeriod();
        }

        //判断科目借贷方向
        $direction = $this->subjectModel->getDirectionById((int)$params['subjectId']);
        if ($direction == $this->subjectModel::DIRECTION_DEBIT) {
            $directionCn = '借';
        } else {
            $directionCn = '贷';
        }

        $list = $this->voucherDetailModel->getDetailList($params);
        $data = [];
        foreach ($list['data'] as $k => $v) {
            $year = date('Y', strtotime($v['date']));
            $month = date('m', strtotime($v['date']));
            $beginBalance = $this->subjectBalanceModel->getSubjectBeginBalance($year, $month, $v['subjectId']);
            if (empty($beginBalance)) {
                $beginBalance = $v['beginBalance'];
            }

            if (!isset($data[$year . '-' . $month]['balance'])) {
                $data[$year . '-' . $month]['initialBalance'] = $beginBalance;
            }
            if (!isset($data[$year . '-' . $month]['debit'])) {
                $data[$year . '-' . $month]['debit'] = '0.00';
            }
            if (!isset($data[$year . '-' . $month]['credit'])) {
                $data[$year . '-' . $month]['credit'] = '0.00';
            }
            $data[$year . '-' . $month]['debit'] += $v['debit'];
            $data[$year . '-' . $month]['credit'] += $v['credit'];

            $data[$year . '-' . $month]['month'] = $year . '-' . $month;
            $data[$year . '-' . $month]['data'][] = [
                'date' => $v['date'],
                'subject' => $v['name'],
                'voucherNo' => $v['voucherNo'],
                'summary' => $v['summary'],
                'debit' => $v['debit'],
                'credit' => $v['credit'],
                'direction' => $directionCn,
                'balance' => $direction == $this->subjectModel::DIRECTION_DEBIT ? $data[$year . '-' . $month]['initialBalance'] + ($data[$year . '-' . $month]['debit'] - $data[$year . '-' . $month]['credit']) : $data[$year . '-' . $month]['initialBalance'] - ($data[$year . '-' . $month]['debit'] - $data[$year . '-' . $month]['credit'])
            ];
        }

        $res = [];
        $i = 0;
        foreach ($data as $k => $v) {
            if ($i == 0) {
                $res['initialBalance'] = $v['initialBalance'];
            }
            $res['data'][] = [
                'month' => $k,
                'data' => $v['data'],
            ];
            $i++;
        }

        return $res;
    }
}
