<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use App\Models\CurrentPeriodModel;
use App\Models\ReportIncomeModel;
use App\Models\SubjectBalanceModel;
use App\Subject;

class ReportIncomeService
{
    /**
     * @var SubjectBalanceModel $subjectBalanceModel
     */
    private $subjectBalanceModel;
    /**
     * @var ReportIncomeModel $periodModel
     */
    private $incomeModel;

    /**
     * @var CurrentPeriodModel $periodModel
     */
    private $periodModel;

    /**
     * 利润表 关键字
     */
    private $keys = [];

    /**
     * 科目余额查询缓存
     */
    private $temp = [];

    public function __construct(ReportIncomeModel $incomeModel, CurrentPeriodModel $periodModel, SubjectBalanceModel $subjectBalanceModel)
    {
        $this->subjectBalanceModel = $subjectBalanceModel;
        $this->incomeModel = $incomeModel;
        $this->periodModel = $periodModel;
        for ($i = 1; $i <= 35; $i++) {
            array_push($this->keys, 'is' . $i);
        }
    }

    /**
     * 结算时候需要计算报表
     * 调用时间在科目余额计算之后
     * @return bool
     */
    public function calculateMonthIncome()
    {
        $year = $this->periodModel->getCurrentYear();
        $period = $this->periodModel->getCurrentPeriod();
        return $this->calculateIncome($year, $period);
    }

    /**
     * 删除报表月份余额
     * @return bool
     */
    public function revokeMonthIncome()
    {
        $year = $this->periodModel->getCurrentYear();
        $period = $this->periodModel->getCurrentPeriod();
        return $this->incomeModel->delAll(['year' => $year, 'period' => $period]);
    }


    /**
     * 结算时候需要计算报表
     * 调用时间在12月科目余额计算之后
     * 设置下一年的年初值
     * @return bool
     */
    public function calculateYearIncome()
    {
        $year = $this->periodModel->getCurrentYear();
        $year = (int)$year + 1;
        return $this->calculateIncome($year, '00');
    }


    /**
     *
     * 删除报表年初00周期
     * @return bool
     */
    public function revokeYearIncome()
    {
        $year = $this->periodModel->getCurrentYear();
        return $this->incomeModel->delAll(['year' => $year, 'period' => '00']);
    }


    /**
     * @param $year
     * @param $period
     * @return bool
     */
    private function calculateIncome($year, $period)
    {
        $total = $this->getIncomeArray($year, $period);
        $result = [];

        //一次批量查询
        $temp1 = $this->getPrePeriodTotalArray($year, 1);
        $temp2 = $this->getPrePeriodTotalArray($year, $period);

        foreach ($this->keys as $key) {
            $id = str_replace("is", "", $key);
            $all = $total[$key];//所有数额

            $yearAmount = 0.0;
            $lastAmount = 0.0;
            try {
                $yearAmount = $temp1[$id];//截止去年所有数额
                $lastAmount = $temp2[$id];//截止上月所有数额
            } catch (\Exception $e) {

            } finally {
                if (!$yearAmount) {
                    $yearAmount = 0.0;
                }
                if (!$lastAmount) {
                    $lastAmount = 0.0;
                }
            }
            array_push($result, ['year' => $year, 'period' => $period, 'id' => $id, 'totalAmount' => $all, 'yearAmount' => ($all - $yearAmount), 'amount' => ($all - $lastAmount)]);
        }
        return $this->incomeModel->addAll($result);
    }


    private function getIncomeArray($year, $period)
    {
        //营业收入
        $is1 = $this->calculateArray($year, $period, [Subject::mainBusinessIncome, Subject::otherBusinessIncome]);
        //营业成本
        $is2 = $this->calculateArray($year, $period, [Subject::mainBusinessCost, Subject::otherBusinessCosts]);
        //税金及附加
        $is3 = $this->calculateArray($year, $period, [Subject::taxesAndSurcharges]);
        //销售费用
        $is4 = $this->calculateArray($year, $period, [Subject::sellingExpenses]);
        //管理费用
        $is5 = $this->calculateArray($year, $period, [Subject::managementCost]);
        //财务费用
        $is6 = $this->calculateArray($year, $period, [Subject::financialCost]);
        //资产减值损失
        $is7 = $this->calculateArray($year, $period, [Subject::assetsImpairmentLoss]);
        //公允价值变动收益（损失以“-”号填列）
        $is8 = $this->calculateArray($year, $period, [Subject::fairValueChangeGainsAndLosses]);
        //投资收益（损失以“-”号填列）
        $is9 = $this->calculateArray($year, $period, [Subject::incomeFromInvestment]);
        //其中：对联营企业和合营企业的投资收益
        $is10 = $this->calculateArray($year, $period, []);

        //营业利润（亏损以“-”号填列）
        $is11 = $is1 - $is2 - $is3 - $is4 - $is5 - $is6 - $is7 + $is8 + $is9;
        //加：营业外收入
        $is12 = $this->calculateArray($year, $period, [Subject::outOfBusinessIncome]);
        //其中：非流动资产处置利得
        $is13 = $this->calculateArray($year, $period, []);
        //减：营业外支出
        $is14 = $this->calculateArray($year, $period, [Subject::outOfBusinessExpenses]);
        //其中：非流动资产处置损失
        $is15 = $this->calculateArray($year, $period, []);

        //利润总额（亏损总额以“-”号填列）
        $is16 = $is11 + $is12 - $is14;
        //加：以前年度损益
        $is17 = $this->calculateArray($year, $period, [Subject::earningsAndLossesOfPreviousYears]);
        //减：所得税费用
        $is18 = $this->calculateArray($year, $period, [Subject::incomeTaxExpenses]);

        //净利润（净亏损以“-”号填列）
        $is19 = $is16 - $is18;

        //以后不能重分类进损益的其他综合收益
        $is21 = $this->calculateArray($year, $period, []);
        //其中:1.重新计量设定受益计划净负债或净资产的变动
        $is22 = $this->calculateArray($year, $period, []);
        //2.权益法下在被投资单位不能重分类进损益的其他综合收益中享有的份额
        $is23 = $this->calculateArray($year, $period, []);
        //3.其他项目
        $is24 = $this->calculateArray($year, $period, []);
        //（二）以后将重分类进损益的其他综合收益

        //其他综合收益的税后净额
        $is20 = $is21 + $is24;

        $is25 = $this->calculateArray($year, $period, []);
        //其中:1.权益法下在被投资单位以后将重分类进损益的其他综合收益中享有的份额
        $is26 = $this->calculateArray($year, $period, []);
        //2.可供出售金融资产公允价值变动损益
        $is27 = $this->calculateArray($year, $period, []);
        //3.持有至到期投资重分类为可供出售金融资产损益
        $is28 = $this->calculateArray($year, $period, []);
        //4.现金流量套期损益的有效部分
        $is29 = $this->calculateArray($year, $period, []);
        //5.外币财务报表折算差额
        $is30 = $this->calculateArray($year, $period, []);
        //6.其他项目
        $is31 = $this->calculateArray($year, $period, []);

        //六、综合收益总额
        $is32 = $is19 + $is20;
        //七、每股收益
        $is33 = $this->calculateArray($year, $period, []);
        //基本每股收益
        $is34 = $this->calculateArray($year, $period, []);
        //稀释每股收益
        $is35 = $this->calculateArray($year, $period, []);

        return array("is1" => $is1, "is2" => $is2, "is3" => $is3, "is4" => $is4, "is5" => $is5, "is6" => $is6, "is7" => $is7, "is8" => $is8, "is9" => $is9,
            "is10" => $is10, "is11" => $is11, "is12" => $is12, "is13" => $is13, "is14" => $is14, "is15" => $is15, "is16" => $is16, "is17" => $is17, "is18" => $is18, "is19" => $is19,
            "is20" => $is20, "is21" => $is21, "is22" => $is22, "is23" => $is23, "is24" => $is24, "is25" => $is25, "is26" => $is26, "is27" => $is27, "is28" => $is28, "is29" => $is29,
            "is30" => $is30, "is31" => $is31, "is32" => $is32, "is33" => $is33, "is34" => $is34, "is35" => $is35);
    }


    /**
     * @param $year
     * @param $period
     * @param $array 科目公式数组
     * @return float|int
     */
    private function calculateArray($year, $period, $array)
    {
        $total = 0.0;
        foreach ($array as $value) {
            $sign = $this->sign($value);
            $balance = $this->getBalanceById($year, $period, abs($value));
            $total = $total + $sign * $balance;
        }
        return $total;
    }


    /**
     * 累计到上月总金额
     * @param $year
     * @param $period 当前会计期间
     * @return mixed 返回上个会计期间数组
     */
    private function getPrePeriodTotalArray($year, $period)
    {
        $p = (int)$period - 1;
        return $this->incomeModel->getTotalAmountArray($year, $p);
    }

    /**
     * 查询正负号
     * @param $number 数字
     * @return mixed -1 或者 1
     */
    private function sign($number)
    {
        return $number < 0 ? -1 : 1;
    }


    /**
     * 查询科目余额
     * @param $year
     * @param $period
     * @param $id
     * @return mixed
     */
    private function getBalanceById($year, $period, $id)
    {
        if (!$this->temp) {
            $this->temp = $this->subjectBalanceModel->getEndingBalanceArray($year, $period);
        }
        $value = 0.0;
        try {
            $value = $this->temp[$id];
            //        $this->subjectBalanceModel->getEndingBalanceArray();
//        $value = SubjectBalanceModel::where(['year' => $year, 'month' => $period, 'subjectId' => $id])->value("endingBalance");
        } catch (Exception $e) {
        } finally {
            if (!$value) {
                $value = 0;
            }
            return $value;
        }

//        $value = $this->subjectBalanceModel->getLastPeriodEndingBalance($year, $period, $id);
//        if (!$value) {
//            $value = 0;
//        }
//        return $value;
    }

}
