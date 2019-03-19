<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use App\Models\CurrentPeriodModel;
use App\Models\ReportIncomeModel;
use App\Models\SubjectModel;
use App\Subject;

class ReportIncomeService
{
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

    public function __construct(ReportIncomeModel $incomeModel, CurrentPeriodModel $periodModel)
    {
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
     * @param $year
     * @param $period
     * @return bool
     */
    private function calculateIncome($year, $period)
    {
        $total = $this->getIncomeArray();
        $result = [];
        foreach ($this->keys as $key) {
            $id = str_replace("is", "", $key);
            $all = $total[$key];//所有数额
            $yearAmount = $this->getPrePeriodTotalAmount($year, '01', $id);//截止去年所有数额
            $lastAmount = $this->getPrePeriodTotalAmount($year, $period, $id);//截止上月所有数额
            array_push($result, ['year' => $year, 'period' => $period, 'id' => $id, 'totalAmount' => $all, 'yearAmount' => ($all - $yearAmount), 'amount' => ($all - $lastAmount)]);
        }
        return $this->incomeModel->addAll($result);
    }


    private function getIncomeArray()
    {
        //营业收入
        $is1 = $this->calculateArray([Subject::主营业务收入, Subject::其他业务收入]);
        //营业成本
        $is2 = $this->calculateArray([Subject::主营业务成本, Subject::其他业务成本]);
        //税金及附加
        $is3 = $this->calculateArray([Subject::税金及附加]);
        //销售费用
        $is4 = $this->calculateArray([Subject::销售费用]);
        //管理费用
        $is5 = $this->calculateArray([Subject::管理费用]);
        //财务费用
        $is6 = $this->calculateArray([Subject::财务费用]);
        //资产减值损失
        $is7 = $this->calculateArray([Subject::资产减值损失]);
        //公允价值变动收益（损失以“-”号填列）
        $is8 = $this->calculateArray([Subject::公允价值变动损益]);
        //投资收益（损失以“-”号填列）
        $is9 = $this->calculateArray([Subject::投资收益]);
        //其中：对联营企业和合营企业的投资收益
        $is10 = $this->calculateArray([]);

        //营业利润（亏损以“-”号填列）
        $is11 = $is1 - $is2 - $is3 - $is4 - $is5 - $is6 - $is7 + $is8 + $is9;
        //加：营业外收入
        $is12 = $this->calculateArray([Subject::营业外收入]);
        //其中：非流动资产处置利得
        $is13 = $this->calculateArray([]);
        //减：营业外支出
        $is14 = $this->calculateArray([Subject::营业外支出]);
        //其中：非流动资产处置损失
        $is15 = $this->calculateArray([]);

        //利润总额（亏损总额以“-”号填列）
        $is16 = $is11 + $is12 - $is14;
        //加：以前年度损益
        $is17 = $this->calculateArray([Subject::以前年度损益]);
        //减：所得税费用
        $is18 = $this->calculateArray([Subject::所得税费用]);

        //净利润（净亏损以“-”号填列）
        $is19 = $is16 - $is18;

        //以后不能重分类进损益的其他综合收益
        $is21 = $this->calculateArray([]);
        //其中:1.重新计量设定受益计划净负债或净资产的变动
        $is22 = $this->calculateArray([]);
        //2.权益法下在被投资单位不能重分类进损益的其他综合收益中享有的份额
        $is23 = $this->calculateArray([]);
        //3.其他项目
        $is24 = $this->calculateArray([]);
        //（二）以后将重分类进损益的其他综合收益

        //其他综合收益的税后净额
        $is20 = $is21 + $is24;

        $is25 = $this->calculateArray([]);
        //其中:1.权益法下在被投资单位以后将重分类进损益的其他综合收益中享有的份额
        $is26 = $this->calculateArray([]);
        //2.可供出售金融资产公允价值变动损益
        $is27 = $this->calculateArray([]);
        //3.持有至到期投资重分类为可供出售金融资产损益
        $is28 = $this->calculateArray([]);
        //4.现金流量套期损益的有效部分
        $is29 = $this->calculateArray([]);
        //5.外币财务报表折算差额
        $is30 = $this->calculateArray([]);
        //6.其他项目
        $is31 = $this->calculateArray([]);

        //六、综合收益总额
        $is32 = $is19 + $is20;
        //七、每股收益
        $is33 = $this->calculateArray([]);
        //基本每股收益
        $is34 = $this->calculateArray([]);
        //稀释每股收益
        $is35 = $this->calculateArray([]);

        return array("is1" => $is1, "is2" => $is2, "is3" => $is3, "is4" => $is4, "is5" => $is5, "is6" => $is6, "is7" => $is7, "is8" => $is8, "is9" => $is9,
            "is10" => $is10, "is11" => $is11, "is12" => $is12, "is13" => $is13, "is14" => $is14, "is15" => $is15, "is16" => $is16, "is17" => $is17, "is18" => $is18, "is19" => $is19,
            "is20" => $is20, "is21" => $is21, "is22" => $is22, "is23" => $is23, "is24" => $is24, "is25" => $is25, "is26" => $is26, "is27" => $is27, "is28" => $is28, "is29" => $is29,
            "is30" => $is30, "is31" => $is31, "is32" => $is32, "is33" => $is33, "is34" => $is34, "is35" => $is35);
    }


    /**
     * @param $array 科目公式数组
     * @return float|int
     */
    private function calculateArray($array)
    {
        $total = 0.0;
        foreach ($array as $value) {
            $sign = $this->sign($value);
            $balance = $this->getBalanceById(abs($value));
            $total = $total + $sign * $balance;
        }
        return $total;
    }

    /**
     * 累计到上月总金额
     * @param $year
     * @param $period 当前会计期间
     * @param $id
     * @return mixed 返回上个会计期间数额
     */
    private function getPrePeriodTotalAmount($year, $period, $id)
    {
        $p = "" . ((int)$period - 1);
        if (strlen($p) == 1) {
            $p = '0' . $p;
        }
        $value = ReportIncomeModel::where(['year' => $year, 'period' => $p, 'id' => $id])->value("totalAmount");
        if (!$value) {
            $value = 0;
        }
        return $value;
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
     * @param $id
     * @return mixed
     */
    private function getBalanceById($id)
    {
        return SubjectModel::where(["id" => $id])->value("balance");
    }

}
