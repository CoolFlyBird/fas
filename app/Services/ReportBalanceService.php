<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use App\Models\ReportBalanceModel;
use App\Models\CurrentPeriodModel;
use App\Models\SubjectBalanceModel;
use App\Models\SubjectModel;
use App\Subject;

class ReportBalanceService
{
    /**
     * @var ReportBalanceModel $balanceModel
     */
    private $balanceModel;
    /**
     * @var CurrentPeriodModel $periodModel
     */
    private $periodModel;
    /**
     * 资产负债表 关键字
     */
    private $keys = [];

    /**
     * ReportBalanceService constructor.
     * @param ReportBalanceModel $balanceModel
     * @param CurrentPeriodModel $periodModel
     */
    public function __construct(ReportBalanceModel $balanceModel, CurrentPeriodModel $periodModel)
    {
        $this->balanceModel = $balanceModel;
        $this->periodModel = $periodModel;
        $this->keys = [];
        for ($i = 1; $i <= 63; $i++) {
            if ($i == 3 || $i == 45) continue;
            array_push($this->keys, 'bs' . $i);
        }
    }

    /**
     * 结算时候需要计算报表
     * 调用时间在科目余额计算之后
     * @return bool
     */
    public function calculateMonthBalance()
    {
        return $this->calculateBalance($this->periodModel->getCurrentYear(), $this->periodModel->getCurrentPeriod());
    }

    /**
     * 删除报表月份余额
     * @return bool
     */
    public function revokeMonthBalance()
    {
        $year = $this->periodModel->getCurrentYear();
        $period = $this->periodModel->getCurrentPeriod();
        return $this->balanceModel->delAll(['year' => $year, 'period' => $period]);
    }

    /**
     * 结算时候需要计算报表
     * 调用时间在12月科目余额计算之后
     * 设置下一年的年初值
     * @return bool
     */
    public function calculateYearBalance()
    {
        $year = $this->periodModel->getCurrentYear();
        $year = (int)$year + 1;
        return $this->calculateBalance($year, '00');
    }

    /**
     * 删除报表年初00周期
     * @return bool
     */
    public function revokeYearBalance()
    {
        $year = $this->periodModel->getCurrentYear();
        return $this->balanceModel->delAll(['year' => $year, 'period' => '00']);
    }

    /**
     * 设置科目余额
     * @param $year 年度
     * @param $period 周期
     * @return bool
     */
    private function calculateBalance($year, $period)
    {
        $begin = $this->getBeginBalanceArray($year, $period);
        $end = $this->getEndBalanceArray($year, $period);
        $result = [];
        foreach ($this->keys as $key) {
            array_push($result, ['year' => $year, 'period' => $period, 'id' => str_replace("bs", "", $key), 'beginValue' => $begin[$key], 'endValue' => $end[$key]]);
        }
        return $this->balanceModel->addAll($result);
    }

    /**
     * @param $year 年
     * @param $period 会计期间
     * @return array
     */
    private function getEndBalanceArray($year, $period)
    {
        //货币资金
        $bs1 = $this->calculateArray($year, $period, [Subject::库存现金, Subject::银行存款, Subject::其他货币资金]);
        //以公允价值计量且其变动计入当期损益的金融资产
        $bs2 = $this->calculateArray($year, $period, [Subject::交易性金融资产]);
        //应收票据
        $bs4 = $this->calculateArray($year, $period, [Subject::应收票据]);
        //应收账款 = 应收账款(借)（1122）+ 预收账款(借)（2203）- 坏账准备_应收账款坏账准备（123101）
        $bs5 = $this->calculateArrayInAndEnd($year, $period, [Subject::应收账款, Subject::预收账款], [-Subject::应收账款坏账准备]);
        //预付款项 = 预付账款(借)（1123）+ 应付账款(借)（2202）- 坏账准备_预付账款坏账准备（123102）
        $bs6 = $this->calculateArrayInAndEnd($year, $period, [Subject::预付账款, Subject::应付账款], [-Subject::预付账款坏账准备]);
        //应收利息
        $bs7 = $this->calculateArray($year, $period, [Subject::应收利息]);//
        //应收股利
        $bs8 = $this->calculateArray($year, $period, [Subject::应收股利]);//
        //其他应收款
        $bs9 = $this->calculateArray($year, $period, [Subject::其他应收款, -Subject::其他应收款坏账准备]);//
        //存货
        $bs10 = $this->calculateArray($year, $period, [Subject::材料采购, Subject::在途物资, Subject::原材料, Subject::材料成本差异,
            Subject::库存商品, Subject::委托加工物资, Subject::周转材料, Subject::消耗性生物资产,
            Subject::生产成本, Subject::制造费用, Subject::工程施工, -Subject::商品进销差价]);//
        //其他流动资产
        $bs11 = $this->calculateArray($year, $period, [Subject::待处理财产损溢, Subject::衍生工具]);
        //流动资产合计
        $bs12 = $bs1 + $bs2 + $bs4 + $bs5 + $bs6 + $bs7 + $bs8 + $bs9 + $bs10 + $bs11;

        //可供出售金融资产
        $bs13 = $this->calculateArray($year, $period, [Subject::可供出售金融资产]);
        //持有至到期投资
        $bs14 = $this->calculateArray($year, $period, [Subject::持有至到期投资, -Subject::持有至到期投资减值准备]);
        //长期应收款
        $bs15 = $this->calculateArray($year, $period, [Subject::长期应收款, -Subject::未实现融资收益]);
        //长期股权投资
        $bs16 = $this->calculateArray($year, $period, [Subject::长期股权投资, -Subject::长期股权投资减值准备]);
        //投资性房地产
        $bs17 = $this->calculateArray($year, $period, [Subject::投资性房地产]);
        //固定资产
        $bs18 = $this->calculateArray($year, $period, [Subject::固定资产, -Subject::累计折旧_50, -Subject::固定资产减值准备]);
        //在建工程
        $bs19 = $this->calculateArray($year, $period, [Subject::在建工程]);
        //工程物资
        $bs20 = $this->calculateArray($year, $period, [Subject::工程物资]);
        //固定资产清理
        $bs21 = $this->calculateArray($year, $period, [Subject::固定资产清理]);
        //生产性生物资产
        $bs22 = $this->calculateArray($year, $period, [Subject::生产性生物资产, -Subject::生产性生物资产累计折旧]);
        //油气资产
        $bs23 = $this->calculateArray($year, $period, []);
        //无形资产
        $bs24 = $this->calculateArray($year, $period, [Subject::无形资产, -Subject::累计摊销]);
        //开发支出
        $bs25 = $this->calculateArray($year, $period, [Subject::研发支出]);
        //商誉
        $bs26 = $this->calculateArray($year, $period, [Subject::商誉]);
        //长期待摊费用
        $bs27 = $this->calculateArray($year, $period, [Subject::长期待摊费用]);
        //递延所得税资产
        $bs28 = $this->calculateArray($year, $period, [Subject::递延所得税资产]);
        //其他非流动资产
        $bs29 = $this->calculateArray($year, $period, [Subject::待处理非流动资产损溢]);
        //非流动资产合计
        $bs30 = $bs13 + $bs14 + $bs15 + $bs16 + $bs17 + $bs18 + $bs19 + $bs20 + $bs21 + $bs22 + $bs23 + $bs24 + $bs25 + $bs26 + $bs27 + $bs28 + $bs29;
        //资产总计
        $bs31 = $bs12 + $bs30;

        //短期借款
        $bs32 = $this->calculateArray($year, $period, [Subject::短期借款]);
        //以公允价值计量且其变动计入当期损益的金融负债
        $bs33 = $this->calculateArray($year, $period, [Subject::交易性金融负债]);
        //应付票据
        $bs34 = $this->calculateArray($year, $period, [Subject::应付票据]);
        //应付账款 = 预付账款(贷)（1123）+ 应付账款(贷)（2202）
        $bs35 = $this->calculateArrayOut($year, $period, [Subject::预付账款, Subject::应付账款]);
        //预收款项 = 应收账款(贷)（1122）+ 预收账款(贷)（2203）
        $bs36 = $this->calculateArrayOut($year, $period, [Subject::应收账款, Subject::预收账款]);
        //应付职工薪酬
        $bs37 = $this->calculateArray($year, $period, [Subject::应付职工薪酬]);
        //应交税费
        $bs38 = $this->calculateArray($year, $period, [Subject::应交税费]);
        //应付利息
        $bs39 = $this->calculateArray($year, $period, [Subject::应付利息]);
        //应付股利
        $bs40 = $this->calculateArray($year, $period, [Subject::应付股利]);
        //其他应付款
        $bs41 = $this->calculateArray($year, $period, [Subject::其他应付款]);
        //一年内到期的非流动负债
        $bs42 = $this->calculateArray($year, $period, []);
        //其他流动负债
        $bs43 = $this->calculateArray($year, $period, []);
        //流动负债合计
        $bs44 = $bs32 + $bs33 + $bs34 + $bs35 + $bs36 + $bs37 + $bs38 + $bs39 + $bs40 + $bs41 + $bs42 + $bs43;

        //长期借款
        $bs46 = $this->calculateArray($year, $period, [Subject::长期借款]);
        //应付债券
        $bs47 = $this->calculateArray($year, $period, [Subject::应付债券]);
        //长期应付款
        $bs48 = $this->calculateArray($year, $period, [Subject::长期应付款, -Subject::未确认融资费用]);
        //专项应付款
        $bs49 = $this->calculateArray($year, $period, [Subject::专项应付款]);
        //预计负债
        $bs50 = $this->calculateArray($year, $period, [Subject::预计负债]);
        //递延收益
        $bs51 = $this->calculateArray($year, $period, [Subject::递延收益]);
        //递延所得税负债
        $bs52 = $this->calculateArray($year, $period, [Subject::递延所得税负债]);
        //其他非流动负债
        $bs53 = $this->calculateArray($year, $period, []);
        //非流动负债合计
        $bs54 = $bs46 + $bs47 + $bs48 + $bs49 + $bs50 + $bs51 + $bs52 + $bs53;
        //负债合计
        $bs55 = $bs44 + $bs54;

        //实收资本
        $bs56 = $this->calculateArray($year, $period, [Subject::实收资本]);
        //资本公积
        $bs57 = $this->calculateArray($year, $period, [Subject::资本公积]);
        //库存股
        $bs58 = $this->calculateArray($year, $period, [Subject::库存股]);
        //其他综合收益
        $bs59 = $this->calculateArray($year, $period, [Subject::其他权益工具]);
        //盈余公积
        $bs60 = $this->calculateArray($year, $period, [Subject::盈余公积]);
        //未分配利润
        $bs61 = $this->calculateArray($year, $period, [Subject::本年利润, Subject::利润分配]);
        //所有者权益
        $bs62 = $bs56 + $bs57 + $bs59 + $bs60 + $bs61 - $bs58;
        //负债和所有者权益
        $bs63 = $bs55 + $bs62;
        return array("bs1" => $bs1, "bs2" => $bs2, "bs4" => $bs4, "bs5" => $bs5, "bs6" => $bs6, "bs7" => $bs7, "bs8" => $bs8, "bs9" => $bs9,
            "bs10" => $bs10, "bs11" => $bs11, "bs12" => $bs12, "bs13" => $bs13, "bs14" => $bs14, "bs15" => $bs15, "bs16" => $bs16, "bs17" => $bs17, "bs18" => $bs18, "bs19" => $bs19,
            "bs20" => $bs20, "bs21" => $bs21, "bs22" => $bs22, "bs23" => $bs23, "bs24" => $bs24, "bs25" => $bs25, "bs26" => $bs26, "bs27" => $bs27, "bs28" => $bs28, "bs29" => $bs29,
            "bs30" => $bs30, "bs31" => $bs31, "bs32" => $bs32, "bs33" => $bs33, "bs34" => $bs34, "bs35" => $bs35, "bs36" => $bs36, "bs37" => $bs37, "bs38" => $bs38, "bs39" => $bs39,
            "bs40" => $bs40, "bs41" => $bs41, "bs42" => $bs42, "bs43" => $bs43, "bs44" => $bs44, "bs46" => $bs46, "bs47" => $bs47, "bs48" => $bs48, "bs49" => $bs49,
            "bs50" => $bs50, "bs51" => $bs51, "bs52" => $bs52, "bs53" => $bs53, "bs54" => $bs54, "bs55" => $bs55, "bs56" => $bs56, "bs57" => $bs57, "bs58" => $bs58, "bs59" => $bs59,
            "bs60" => $bs60, "bs61" => $bs61, "bs62" => $bs62, "bs63" => $bs63);
    }

    /**
     * @param $year 年份
     * @param $period 会计期间 未用到
     * @return array
     */
    private function getBeginBalanceArray($year, $period)
    {
        $result = [];
        foreach ($this->keys as $key) {
            $result = array_add($result, $key, $this->getBalanceRecordById($year, $period, str_replace("bs", "", $key)));
        }
        return $result;
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
     * @param $year
     * @param $period
     * @param $arrayIn 借方 数组
     * @param $arrayEnd 科目公式数组
     * @return float|int
     */
    private function calculateArrayInAndEnd($year, $period, $arrayIn, $arrayEnd)
    {
        $total = 0.0;
        //借方数组
        foreach ($arrayIn as $value) {
            $sign = $this->sign($value);
            $balance = $this->getInBalanceById($year, $period, abs($value));
            $total = $total + $sign * $balance;
        }
        //期末数组
        foreach ($arrayEnd as $value) {
            $sign = $this->sign($value);
            $balance = $this->getBalanceById($year, $period, abs($value));
            $total = $total + $sign * $balance;
        }
        return $total;
    }

    private function calculateArrayOut($year, $period, $arrayOut)
    {
        $total = 0.0;
        //贷方数组
        foreach ($arrayOut as $value) {
            $sign = $this->sign($value);
            $balance = $this->getOutBalanceById($year, $period, abs($value));
            $total = $total + $sign * $balance;
        }
        return $total;
    }

    private function sign($number)
    {
        return $number < 0 ? -1 : 1;
    }

    /**
     * 查询年初
     * @param $year 年份
     * @param $id
     * @return mixed
     */
    private function getBalanceRecordById($year, $period, $id)
    {
        //period 00表示年初
        $value = ReportBalanceModel::where(['year' => $year, 'period' => '00', 'id' => $id])->value("endValue");
        if (!$value) {
            $value = 0;
        }
        return $value;
    }

    /**
     * 查询科目借方变动额
     * @param $year
     * @param $period
     * @param $id
     * @return mixed
     */
    private function getInBalanceById($year, $period, $id)
    {
        $value = SubjectBalanceModel::where(['year' => $year, 'month' => $period, 'subjectId' => $id])->value("debitBalance");
        if (!$value) {
            $value = 0;
        }
        return $value;
    }

    /**
     * 查询科目贷方变动额
     * @param $year
     * @param $period
     * @param $id
     * @return mixed
     */
    private function getOutBalanceById($year, $period, $id)
    {
        $value = SubjectBalanceModel::where(['year' => $year, 'month' => $period, 'subjectId' => $id])->value("creditBalance");
        if (!$value) {
            $value = 0;
        }
        return $value;
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
        $value = SubjectBalanceModel::where(['year' => $year, 'period' => $period, 'subjectId' => $id])->value("endingBalance");
        if (!$value) {
            $value = 0;
        }
        return $value;
    }
}
