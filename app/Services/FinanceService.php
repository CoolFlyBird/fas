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
                                VoucherDetailModel $voucherDetailModel, SubjectBalanceModel $subjectBalanceModel)
    {
        $this->voucherModel        = $voucherModel;
        $this->currentPeriodModel  = $currentPeriodModel;
        $this->voucherDetailModel  = $voucherDetailModel;
        $this->subjectBalanceModel = $subjectBalanceModel;
    }

    public function settleAccount()
    {
        //是否存在未审核凭证
        $isExist = $this->voucherModel->isExistCurrentUnchecked($this->currentPeriodModel->getCurrentPeriod());
        if ($isExist) {
            return ['res' => false, 'msg' => '本期存在未审核凭证，请审核后再进行结账操作'];
        }

        DB::beginTransaction();
        try {
            //年份
            $year = $this->currentPeriodModel->getCurrentYear();
            //期数
            $month = $this->currentPeriodModel->getCurrentPeriod();

            //获取本期凭证信息
            $currentList = $this->voucherDetailModel->getCurrentVoucherList($year, $month);
            foreach ($currentList as $k => $v) {
                //获取上月的期初余额
                $last = $this->subjectBalanceModel->getLastPeriodBeginBalance($year, $month, $v['subjectId']);
                if ($last) {
                    $beginBalance = $last['debitEndingBalance'] - $last['creditEndingBalance'];
                } else {
                    $beginBalance = $v['balance'];
                }
                //判断科目方向并设置期初余额
                if ($v['direction'] == $this->voucherModel::DIRECTION_DEBIT) {
                    $currentList[$k]['debitBeginBalance'] = $beginBalance;
                    $currentList[$k]['creditBeginBalance'] = '0.00';
                } else {
                    $currentList[$k]['debitBeginBalance'] = '0.00';
                    $currentList[$k]['creditBeginBalance'] = -$beginBalance;
                }
            }

            //本期科目结账数据
            $this->subjectBalanceModel->addAll($currentList);

            DB::commit();
            return ['res' => true, 'msg' => '成功'];
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();

            return ['res' => false , 'msg' => '结账失败，系统内部错误'];
        }
    }
}
