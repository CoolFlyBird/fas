<?php
namespace App\Services;

use App\Models\CurrentPeriodModel;
use App\Models\SubjectBalanceModel;
use App\Models\VoucherDetailModel;
use App\Models\VoucherModel;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public function __construct(VoucherModel $voucherModel, CurrentPeriodModel $currentPeriodModel,
                                VoucherDetailModel $voucherDetailModel, SubjectBalanceModel $subjectBalanceModel,
                                ReportService $reportService)
    {
        $this->voucherModel        = $voucherModel;
        $this->currentPeriodModel  = $currentPeriodModel;
        $this->voucherDetailModel  = $voucherDetailModel;
        $this->subjectBalanceModel = $subjectBalanceModel;
        $this->reportService       = $reportService;
    }

    /**
     * 结账
     * @author huxinlu
     * @return array
     */
    public function settleAccount()
    {
        //是否存在未审核凭证
        $isExist = $this->voucherModel->isExistCurrentUnchecked($this->currentPeriodModel->getCurrentPeriod());
        if ($isExist) {
            return ['res' => false, 'msg' => '本期存在未审核凭证，请审核后再进行结账操作'];
        }

        //年份
        $year = $this->currentPeriodModel->getCurrentYear();
        //期数
        $month = $this->currentPeriodModel->getCurrentPeriod();

        //获取本期凭证信息
        $currentList = $this->voucherDetailModel->getCurrentVoucherList($year, $month);
        if (!$currentList) {
            return ['res' => false, 'msg' => '无凭证信息，无法结账'];
        }
        DB::beginTransaction();
        try {
            $data = [];
            foreach ($currentList as $k => $v) {
                $data[$k]['subjectId'] = $v['subjectId'];
                //获取上月的期末余额=下月的期初
                $beginBalance = $this->subjectBalanceModel->getLastPeriodEndingBalance($year, $month, $v['subjectId']);
                if (empty($data[$k]['beginBalance'])) {
                }
                $data[$k]['beginBalance'] = empty($beginBalance) ? $v['balance'] : $beginBalance;

                //期末余额=期初余额+借贷相减（方向是借：借-贷；方向是贷：贷-借）
                if ($v['direction'] == $this->voucherModel::DIRECTION_DEBIT) {
                    $data[$k]['endingBalance'] = $beginBalance + ($v['debitBalance'] - $v['creditBalance']);
                } else {
                    $data[$k]['endingBalance'] = $beginBalance + ($v['creditBalance'] - $v['debitBalance']);
                }
                //本期发生额
                $data[$k]['debitBalance']  = $v['debitBalance'];
                $data[$k]['creditBalance'] = $v['creditBalance'];


                //年度发生额
                $yearData                      = $this->subjectBalanceModel->getYearBalance($year);
                $data[$k]['yearDebitBalance']  = $v['debitBalance'] + $yearData['yearDebitBalance'];
                $data[$k]['yearCreditBalance'] = $v['creditBalance'] + $yearData['yearCreditBalance'];

                //会计期间
                $data[$k]['year']  = $year;
                $data[$k]['month'] = $month;
            }

            //本期科目结账数据
            $this->subjectBalanceModel->addAll($data);

            //当前所在期间修改
            $next = date("Y-m", strtotime("+1 month", strtotime($year . '-' . $month)));
            $this->currentPeriodModel->editCurrent($year, $month, date('Y', strtotime($next)), date('m', strtotime($next)));

            //计算报表
            $calculate = $this->reportService->calculateMonth();
            if (!$calculate) {
                DB::rollBack();
                return ['res' => false, 'msg' => '结账失败，报表计算有误'];
            }

            DB::commit();
            return ['res' => true, 'msg' => '成功'];
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();

            return ['res' => false, 'msg' => '结账失败，系统内部错误'];
        }
    }

    /**
     * 反结账
     * @author huxinlu
     * @return array
     */
    public function checkout()
    {
        DB::beginTransaction();
        try {
            //年份
            $year = $this->currentPeriodModel->getCurrentYear();
            //期数
            $month = $this->currentPeriodModel->getCurrentPeriod();

            //当前所在期间修改
            $next          = date("Y-m", strtotime("-1 month", strtotime($year . '-' . $month)));
            $lastMonthYear = date('Y', strtotime($next));
            $lastMonth     = date('m', strtotime($next));

            //删除已经结完账的科目
            $this->subjectBalanceModel->delSubject($lastMonthYear, $lastMonth);

            //修改当前所在期间
            $this->currentPeriodModel->editCurrent($year, $month, $lastMonthYear, $lastMonth);

            //计算报表
            $calculate = $this->reportService->revokeMonth();
            if (!$calculate) {
                DB::rollBack();
                return ['res' => false, 'msg' => '反结账失败，报表计算有误'];
            }

            DB::commit();
            return ['res' => true, 'msg' => '成功'];
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();

            return ['res' => false, 'msg' => '反结账失败，系统内部错误'];
        }
    }

    /**
     * 结转损益
     * @author huxinlu
     * @param $params
     * @return array
     */
    public function calculateProfit($params)
    {
        //年
        $year = $this->currentPeriodModel->getCurrentYear();

        //月
        $month = $this->currentPeriodModel->getCurrentPeriod();

        //凭证日期默认是当前期间的最后一天
        $date = date('Y-m-d', strtotime($year . '-' . $month . " +1 month -1 day"));
        if ($date != $params['date']) {
            return ['res' => false, 'msg' => '凭证日期不正确'];
        }

        //判断是否已经结转过
        $isExistData = $this->voucherDetailModel->isExistCurrentProfit($date);
        if ($isExistData) {
            return ['res' => false, 'msg' => '不能重复结转'];
        }
        /**
         * 1、成本(5开头)，损益(6开头)借贷余额不等于0的数据转到本年利润
         * 2、就是说voucher表添加了一条数据，voucher_detail表会把1中的数据都放在刚添加的voucher下，然后再计算本年利润加入到voucher_detail表
         */
        $list = $this->voucherDetailModel->getProfitList($year, $month);
        if (empty($list)) {
            return ['res' => false, 'msg' => '没有需要结转的科目'];
        }
        DB::beginTransaction();
        try {
            //获取当前期间最大编码
            $maxVoucherNo = $this->voucherModel->getMaxVoucherNo($params['date'], (int)$params['proofWordId']);

            //该期间内总金额
            $money = $this->voucherDetailModel->getCurrentAllMoney($year, $month);

            //凭证数据
            $voucherData = [
                'proofWordId' => $params['proofWordId'],
                'voucherNo'   => $maxVoucherNo ? $maxVoucherNo + 1 : 1,
                'date'        => $date,
                'allDebit'    => $money['allDebit'],
                'allCredit'   => $money['allCredit']
            ];

            //凭证ID
            $voucherId = $this->voucherModel->insertGetId($voucherData);

            //凭证详情数据
            $voucherDetailData = [];
            foreach ($list as $v) {
                $voucherDetailData[] = [
                    'voucherId'       => $voucherId,
                    'summary'         => $v['summary'],
                    'subjectId'       => $v['subjectId'],
                    'auxiliaryTypeId' => $v['auxiliaryTypeId'],
                    'auxiliaryId'     => $v['auxiliaryId'],
                    'subject'         => $v['subject'],
                    'debit'           => $v['debit'],
                    'credit'          => $v['credit'],
                    'date'            => $date
                ];
            }

            $this->voucherDetailModel->addAll($voucherDetailData);

            //本年利润数据
            $profitData = [
                'voucherId' => $voucherId,
                'summary'   => $params['summary'],
                'subjectId' => 157,
                'subject'   => '4103 本年利润',
                'debit'     => $money['allDebit'],
                'credit'    => $money['allCredit'],
                'date'      => $date
            ];

            $this->voucherDetailModel->add($profitData);

            DB::commit();
            return ['res' => true, 'mag' => '成功'];
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();

            return ['res' => false, 'msg' => '结转失败'];
        }
    }
}
