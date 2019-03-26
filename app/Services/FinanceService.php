<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */
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
                $yearData = $this->subjectBalanceModel->getYearBalance($year);
                $data[$k]['yearDebitBalance']  = $v['debitBalance'] + $yearData['yearDebitBalance'];
                $data[$k]['yearCreditBalance'] = $v['creditBalance'] + $yearData['yearCreditBalance'];

                //会计期间
                $data[$k]['year']          = $year;
                $data[$k]['month']         = $month;
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
}
