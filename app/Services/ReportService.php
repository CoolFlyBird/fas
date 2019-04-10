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
     * 结账调用
     * @param $year
     * @param $period
     * @return bool 是否计算成功
     */
    public function calculateMonth($year, $period)
    {
        $currentYear   = (int)$this->currentPeriodModel->getCurrentYear();
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
                    && $result4 && $result5 && $result6
                ) {
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
        $currentYear   = (int)$this->currentPeriodModel->getCurrentYear();
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
                    && $result4 && $result5 && $result6
                ) {
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

            //该科目期初余额
            $beginBalance = $this->subjectBalanceModel->getSubjectMinMonthBeginBalance((int)$params['startPeriod'], (int)$params['endPeriod'], $v['subjectId']);
            $beginBalance = $beginBalance ?? 0.00;

            //该科目期末余额
            $endingBalance = $this->subjectBalanceModel->getSubjectMaxMonthEndingBalance((int)$params['startPeriod'], (int)$params['endPeriod'], $v['subjectId']);
            $endingBalance = $endingBalance ?? 0.00;

            //判断科目属于借还是贷
            $direction = $this->subjectModel->getDirectionByCode($v['code']);
            if ($direction == $this->subjectModel::DIRECTION_DEBIT) {
                $data[$k]['debitBeginBalance']  = $beginBalance;
                $data[$k]['debitEndingBalance'] = $endingBalance;
                $data[$k]['creditBeginBalance'] = $data[$k]['creditEndingBalance'] = 0.00;
            } else {
                $data[$k]['debitBeginBalance']   = $data[$k]['debitEndingBalance'] = 0.00;
                $data[$k]['creditBeginBalance']  = $beginBalance;
                $data[$k]['creditEndingBalance'] = $endingBalance;
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

        //科目余额
        $subjectBalance = $this->subjectBalanceModel->getSubjectBalance(date('Y'), (int)$params['startPeriod'], (int)$params['endPeriod'], (int)$params['subjectId']);
        if (empty($subjectBalance)) {
            $balance = $this->subjectModel->getInitialBalance((int)$params['subjectId']);
            $balance = $balance ?? 0.00;
        } else {
            $balance = $subjectBalance['beginBalance'];
        }

        //期初余额
        $initialBalance = $balance;

        //列表
        $list = $this->voucherDetailModel->getDetailList($params);
        $data = [];
        foreach ($list['data'] as $k => $v) {
            $year  = date('Y');
            $month = date('m', strtotime($v['date']));
            if ($direction == $this->subjectModel::DIRECTION_DEBIT) {
                $balance = $balance + $v['debit'] - $v['credit'];
            } else {
                $balance = $balance + $v['credit'] - $v['debit'];
            }

            $data[$year . '-' . $month]['month']  = $year . '-' . $month;
            $data[$year . '-' . $month]['data'][] = [
                'date'      => $v['date'],
                'subject'   => $v['name'],
                'voucherNo' => $v['voucherNo'],
                'summary'   => $v['summary'],
                'debit'     => $v['debit'],
                'credit'    => $v['credit'],
                'direction' => $balance == 0.00 ? '平' : $directionCn,
                'balance'   => $balance
            ];
        }

        //最小月份
        $minMonth = $this->subjectBalanceModel->getSubjectMinMonth(date('Y'), (int)$params['startPeriod'], (int)$params['endPeriod'], (int)$params['subjectId']);
        $date     = date('Y-' . $minMonth . '-01');

        $res = [];
        $i   = 0;
        foreach ($data as $k => $v) {
            if ($i == 0) {
                $res['initialBalance']    = $initialBalance;
                $res['yearDebitBalance']  = $subjectBalance['yearDebitBalance'];
                $res['yearCreditBalance'] = $subjectBalance['yearCreditBalance'];
                $res['date']              = $date;
                $res['direction']         = $balance == 0.00 ? '平' : $directionCn;
            }
            $res['data'][] = [
                'month' => $k,
                'data'  => $v['data'],
            ];
            $i++;
        }

        return $res;
    }

    /**
     * 核算项目明细账
     * @author huxinlu
     * @param $params
     * @return array
     */
    public function getAssistSubjectList($params)
    {
        if ($params['startPeriod'] == 0) {
            $params['startPeriod'] = $this->currentPeriodModel->getCurrentPeriod();
        }
        if ($params['endPeriod'] == 0) {
            $params['endPeriod'] = $this->currentPeriodModel->getCurrentPeriod();
        }

        if (!empty($params['code'])) {
            if (strpos($params['code'], ',') !== false) {
                $params['code'] = explode(',', $params['code']);
            } elseif (strpos($params['code'], '-') !== false) {
                $codeArr = explode('-', $params['code']);
                $code    = [];
                for ($i = (int)$codeArr[0]; $i <= $codeArr[1]; $i++) {
                    $code[] = $i;
                }

                $params['code'] = $code;
            }
        }

        //含有凭证明细的科目列表
        $list = $this->subjectModel->getSubjectList($params);
        $data = [];
        foreach ($list['data'] as $k => $v) {
            //该科目所有的月份
            $monthDateArr = $this->voucherDetailModel->getMonthlySubjectDateArr((int)$params['auxiliaryTypeId'], (int)$params['startPeriod'], (int)$params['endPeriod'], $v['id']);
            if ($monthDateArr) {
                $data[$k]['code'] = $v['code'];
                $data[$k]['name'] = $v['name'];

                //判断科目借贷方向
                if ($v['direction'] == $this->subjectModel::DIRECTION_DEBIT) {
                    $directionCn = '借';
                } else {
                    $directionCn = '贷';
                }

                $arr = [];
                foreach ($monthDateArr as $monthDate) {
                    //科目每月期初余额
                    $beginBalance = $this->subjectBalanceModel->getSubjectBeginBalance(date('Y', strtotime($monthDate)), date('m', strtotime($monthDate)), $v['id']);
                    $beginBalance = $beginBalance ?? 0.00;

                    //每月列表
                    $record = $this->voucherDetailModel->getAssistSubjectMonthlyList((int)$params['auxiliaryTypeId'], $monthDate, $v['id']);

                    $monthData = [];
                    $balance   = $beginBalance;
                    $debit     = $credit = 0.00;
                    foreach ($record as $key => $value) {
                        if ($v['direction'] == $this->subjectModel::DIRECTION_DEBIT) {
                            $balance = $balance + $value['debit'] - $value['credit'];
                        } else {
                            $balance = $balance + $value['credit'] - $value['debit'];
                        }

                        $monthData[$key]['date']      = $value['date'];
                        $monthData[$key]['voucherNo'] = $value['voucherNo'];
                        $monthData[$key]['summary']   = $value['summary'];
                        $monthData[$key]['debit']     = $value['debit'];
                        $monthData[$key]['credit']    = $value['credit'];
                        $monthData[$key]['direction'] = $balance == 0.00 ? '平' : $directionCn;
                        $monthData[$key]['balance']   = $balance;

                        $debit += $value['debit'];
                        $credit += $value['credit'];
                    }

                    //当月的第一天
                    $firstDay['date']      = $monthDate . '-01';
                    $firstDay['voucherNo'] = '';
                    $firstDay['balance']   = $beginBalance;
                    $firstDay['direction'] = $beginBalance == 0.00 ? '平' : $directionCn;
                    $firstDay['summary']   = '期初余额';
                    if ($v['direction'] == $this->subjectModel::DIRECTION_DEBIT) {
                        $firstDay['debit']  = $beginBalance;
                        $firstDay['credit'] = 0.00;
                    } else {
                        $firstDay['credit'] = $beginBalance;
                        $firstDay['debit']  = 0.00;
                    }

                    array_unshift($monthData, $firstDay);

                    //当月的最后一天
                    $lastDay['date']      = date('Y-m-d', strtotime("$monthDate +1 month -1 day"));
                    $lastDay['voucherNo'] = '';
                    $lastDay['summary']   = '本期合计';
                    $lastDay['debit']     = $debit;
                    $lastDay['credit']    = $credit;
                    $lastDay['direction'] = $debit - $credit == 0.00 ? '平' : $directionCn;
                    $lastDay['balance']   = $v['direction'] == $this->subjectModel::DIRECTION_DEBIT ? $debit - $credit : $credit - $debit;

                    array_push($monthData, $lastDay);

                    $arr[] = $monthData;
                }

                //把每个月的数据组合成一个数组
                $res = array_reduce($arr, 'array_merge', []);

                //该科目最后一个月
                $lastMonth     = $monthDateArr[count($monthDateArr) - 1];
                $balanceDetail = $this->subjectBalanceModel->getSubjectMonthYearBalanceDetail(date('Y', strtotime($lastMonth)), date('m', strtotime($lastMonth)), $v['id']);

                //本年累计
                $year['date']      = date('Y-m-d', strtotime("$lastMonth +1 month -1 day"));
                $year['voucherNo'] = '';
                $year['summary']   = '本年累计';
                $year['debit']     = $balanceDetail ? $balanceDetail['yearDebitBalance'] : 0.00;
                $year['credit']    = $balanceDetail ? $balanceDetail['yearCreditBalance'] : 0.00;
                $year['direction'] = $year['debit'] - $year['credit'] == 0.00 ? '平' : $directionCn;
                $year['balance']   = $v['direction'] == $this->subjectModel::DIRECTION_DEBIT ? $year['debit'] - $year['credit'] : $year['debit'] - $year['credit'];

                array_push($res, $year);

                $data[$k]['data'] = $res;
            }

            $data = array_merge($data);
        }

        return $data;
    }
}
