<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use App\Models\BalanceModel;
use App\Models\SubjectModel;
use App\Subject;

class ReportBalanceService
{

    /**
     * @var BalanceModel $balanceModel
     */
    private $balanceModel;

    /**
     * ReportBalanceService constructor.
     * @param BalanceModel $balanceModel
     */
    public function __construct(BalanceModel $balanceModel)
    {
        $this->balanceModel = $balanceModel;

    }

    /**
     * 结算时候需要计算报表
     */
    public function settleAccounts($year, $period)
    {
        $begin = $this->getBalanceArray($year, $period, 2);
        $end = $this->getBalanceArray($year, $period, 1);
        $result = [];
        for ($i = 1; $i <= 63; $i++) {
            if ($i == 3 || $i == 45) continue;
            array_push($result, ['year' => $year, 'period' => $period, 'id' => $i, 'beginValue' => $begin['bs' . $i], 'endValue' => $end['bs' . $i]]);
        }
        return $this->balanceModel->addAll($result);
    }

    /**
     * @param $year 年
     * @param $period 会计期间
     * @param int $type 1:期末,2:年初
     * @return array
     */
    private function getBalanceArray($year, $period, $type = 1)
    {
        //货币资金
        $bs1 = $this->calculateArray($year, $period, $type, [Subject::库存现金, Subject::银行存款, Subject::其他货币资金]);
        //以公允价值计量且其变动计入当期损益的金融资产
        $bs2 = $this->calculateArray($year, $period, $type, [Subject::交易性金融资产]);
        //应收票据
        $bs4 = $this->calculateArray($year, $period, $type, [Subject::应收票据]);
        //应收账款 = 应收账款(借)（1122）+ 预收账款(借)（2203）- 坏账准备_应收账款坏账准备（123101）
        $bs5 = $this->calculateArray($year, $period, $type, []);
        //预付款项 = 预付账款(借)（1123）+ 应付账款(借)（2202）- 坏账准备_预付账款坏账准备（123102）
        $bs6 = $this->calculateArray($year, $period, $type, []);
        //应收利息
        $bs7 = $this->calculateArray($year, $period, $type, [Subject::应收利息]);//
        //应收股利
        $bs8 = $this->calculateArray($year, $period, $type, [Subject::应收股利]);//
        //其他应收款
        $bs9 = $this->calculateArray($year, $period, $type, [Subject::其他应收款, -Subject::其他应收款坏账准备]);//
        //存货
        $bs10 = $this->calculateArray($year, $period, $type, [Subject::材料采购, Subject::在途物资, Subject::原材料, Subject::材料成本差异,
            Subject::库存商品, Subject::委托加工物资, Subject::周转材料, Subject::消耗性生物资产,
            Subject::生产成本, Subject::制造费用, Subject::工程施工, -Subject::商品进销差价]);//
        //其他流动资产
        $bs11 = $this->calculateArray($year, $period, $type, [Subject::待处理财产损溢, Subject::衍生工具]);
        //流动资产合计
        $bs12 = $bs1 + $bs2 + $bs4 + $bs5 + $bs6 + $bs7 + $bs8 + $bs9 + $bs10 + $bs11;

        //可供出售金融资产
        $bs13 = $this->calculateArray($year, $period, $type, [Subject::可供出售金融资产]);
        //持有至到期投资
        $bs14 = $this->calculateArray($year, $period, $type, [Subject::持有至到期投资, -Subject::持有至到期投资减值准备]);
        //长期应收款
        $bs15 = $this->calculateArray($year, $period, $type, [Subject::长期应收款, -Subject::未实现融资收益]);
        //长期股权投资
        $bs16 = $this->calculateArray($year, $period, $type, [Subject::长期股权投资, -Subject::长期股权投资减值准备]);
        //投资性房地产
        $bs17 = $this->calculateArray($year, $period, $type, [Subject::投资性房地产]);
        //固定资产
        $bs18 = $this->calculateArray($year, $period, $type, [Subject::固定资产, -Subject::累计折旧, -Subject::固定资产减值准备]);
        //在建工程
        $bs19 = $this->calculateArray($year, $period, $type, [Subject::在建工程]);
        //工程物资
        $bs20 = $this->calculateArray($year, $period, $type, [Subject::工程物资]);
        //固定资产清理
        $bs21 = $this->calculateArray($year, $period, $type, [Subject::固定资产清理]);
        //生产性生物资产
        $bs22 = $this->calculateArray($year, $period, $type, [Subject::生产性生物资产, -Subject::生产性生物资产累计折旧]);
        //油气资产
        $bs23 = $this->calculateArray($year, $period, $type, []);
        //无形资产
        $bs24 = $this->calculateArray($year, $period, $type, [Subject::无形资产, -Subject::累计摊销]);
        //开发支出
        $bs25 = $this->calculateArray($year, $period, $type, [Subject::研发支出]);
        //商誉
        $bs26 = $this->calculateArray($year, $period, $type, [Subject::商誉]);
        //长期待摊费用
        $bs27 = $this->calculateArray($year, $period, $type, [Subject::长期待摊费用]);
        //递延所得税资产
        $bs28 = $this->calculateArray($year, $period, $type, [Subject::递延所得税资产]);
        //其他非流动资产
        $bs29 = $this->calculateArray($year, $period, $type, [Subject::待处理非流动资产损溢]);
        //非流动资产合计
        $bs30 = $bs13 + $bs14 + $bs15 + $bs16 + $bs17 + $bs18 + $bs19 + $bs20 + $bs21 + $bs22 + $bs23 + $bs24 + $bs25 + $bs26 + $bs27 + $bs28 + $bs29;
        //资产总计
        $bs31 = $bs12 + $bs30;

        //短期借款
        $bs32 = $this->calculateArray($year, $period, $type, [Subject::短期借款]);
        //以公允价值计量且其变动计入当期损益的金融负债
        $bs33 = $this->calculateArray($year, $period, $type, [Subject::交易性金融负债]);
        //应付票据
        $bs34 = $this->calculateArray($year, $period, $type, [Subject::应付票据]);
        //应付账款 = 预付账款(贷)（1123）+ 应付账款(贷)（2202）
        $bs35 = $this->calculateArray($year, $period, $type, []);
        //预收款项 = 应收账款(贷)（1122）+ 预收账款(贷)（2203）
        $bs36 = $this->calculateArray($year, $period, $type, []);
        //应付职工薪酬
        $bs37 = $this->calculateArray($year, $period, $type, [Subject::应付职工薪酬]);
        //应交税费
        $bs38 = $this->calculateArray($year, $period, $type, [Subject::应交税费]);
        //应付利息
        $bs39 = $this->calculateArray($year, $period, $type, [Subject::应付利息]);
        //应付股利
        $bs40 = $this->calculateArray($year, $period, $type, [Subject::应付股利]);
        //其他应付款
        $bs41 = $this->calculateArray($year, $period, $type, [Subject::其他应付款]);
        //一年内到期的非流动负债
        $bs42 = $this->calculateArray($year, $period, $type, []);
        //其他流动负债
        $bs43 = $this->calculateArray($year, $period, $type, []);
        //流动负债合计
        $bs44 = $bs32 + $bs33 + $bs34 + $bs35 + $bs36 + $bs37 + $bs38 + $bs39 + $bs40 + $bs41 + $bs42 + $bs43;

        //长期借款
        $bs46 = $this->calculateArray($year, $period, $type, [Subject::长期借款]);
        //应付债券
        $bs47 = $this->calculateArray($year, $period, $type, [Subject::应付债券]);
        //长期应付款
        $bs48 = $this->calculateArray($year, $period, $type, [Subject::长期应付款, -Subject::未确认融资费用]);
        //专项应付款
        $bs49 = $this->calculateArray($year, $period, $type, [Subject::专项应付款]);
        //预计负债
        $bs50 = $this->calculateArray($year, $period, $type, [Subject::预计负债]);
        //递延收益
        $bs51 = $this->calculateArray($year, $period, $type, [Subject::递延收益]);
        //递延所得税负债
        $bs52 = $this->calculateArray($year, $period, $type, [Subject::递延所得税负债]);
        //其他非流动负债
        $bs53 = $this->calculateArray($year, $period, $type, []);
        //非流动负债合计
        $bs54 = $bs46 + $bs47 + $bs48 + $bs49 + $bs50 + $bs51 + $bs52 + $bs53;
        //负债合计
        $bs55 = $bs44 + $bs54;

        //实收资本
        $bs56 = $this->calculateArray($year, $period, $type, [Subject::实收资本]);
        //资本公积
        $bs57 = $this->calculateArray($year, $period, $type, [Subject::资本公积]);
        //库存股
        $bs58 = $this->calculateArray($year, $period, $type, [Subject::库存股]);
        //其他综合收益
        $bs59 = $this->calculateArray($year, $period, $type, [Subject::其他权益工具]);
        //盈余公积
        $bs60 = $this->calculateArray($year, $period, $type, [Subject::盈余公积]);
        //未分配利润
        $bs61 = $this->calculateArray($year, $period, $type, [Subject::本年利润, Subject::利润分配]);
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
     * @param $period 会计期间
     * @param $type 1:期末,2:年初
     * @param $array 科目加减数组
     * @return float|int
     */
    private function calculateArray($year, $period, $type, $array)
    {
        $total = 0.0;
        foreach ($array as $value) {
            $sign = $this->sign($value);
            $balance = $this->getBalanceById(abs($value));
            $total = $total + $sign * $balance;
        }
        return $total;
    }

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
        return SubjectModel::where("id", $id)->value("balance");
    }

}
