<?php
/**
 * Created by PhpStorm.
 * Author: ${user}
 */

namespace App\Services;

use App\Models\CurrentPeriodModel;
use App\Models\ReportBalanceModel;
use App\Models\ReportCashFlowModel;
use App\Models\ReportIncomeModel;
use App\Models\SubjectModel;
use App\Subject;

class ReportCashFlowService
{
    /**
     * @var ReportCashFlowModel $cashFlowModel
     */
    private $cashFlowModel;

    /**
     * @var CurrentPeriodModel $periodModel
     */
    private $periodModel;

    /**
     * 现金流量表 关键字
     */
    private $keys = [];

    /**
     * ReportCashFlowService constructor.
     * @param ReportCashFlowModel $cashFlowModel
     * @param CurrentPeriodModel $periodModel
     */
    public function __construct(ReportCashFlowModel $cashFlowModel, CurrentPeriodModel $periodModel)
    {
        $this->cashFlowModel = $cashFlowModel;
        $this->periodModel = $periodModel;
        for ($i = 1; $i <= 61; $i++) {
            array_push($this->keys, 'cs' . $i);
        }
    }

    /**
     * 结算时候需要计算报表
     * 调用时间在科目余额计算之后
     * @return bool
     */
    public function calculateMonthCashFlow()
    {
        $year = $this->periodModel->getCurrentYear();
        $period = $this->periodModel->getCurrentPeriod();
        return $this->calculateCashFlow($year, $period);
    }

    /**
     * 结算时候需要计算报表
     * 调用时间在12月科目余额计算之后
     * 设置下一年的年初值
     * @return bool
     */
    public function calculateYearCashFlow()
    {
        $year = $this->periodModel->getCurrentYear();
        $year = (int)$year + 1;
        return $this->calculateCashFlow($year, '00');
    }

    /**
     * @param $year
     * @param $period
     * @return bool
     */
    private function calculateCashFlow($year, $period)
    {
        $total = $this->getCashFlowArray($year, $period);
        $result = [];
        foreach ($this->keys as $key) {
            $id = str_replace("cs", "", $key);
            $all = $total[$key];//所有数额
            $yearAmount = $this->getPrePeriodTotalAmount($year, '01', $id);//截止去年所有数额
            $lastAmount = $this->getPrePeriodTotalAmount($year, $period, $id);//截止上月所有数额
            array_push($result, ['year' => $year, 'period' => $period, 'id' => $id, 'totalAmount' => $all, 'yearAmount' => ($all - $yearAmount), 'amount' => ($all - $lastAmount)]);
        }
        return $this->cashFlowModel->addAll($result);
    }


    private function getCashFlowArray($year, $period)
    {
        //销售商品、提供劳务收到的现金
        $cs1 = $this->calculateArray([Subject::销项税额, -Subject::应收账款坏账准备, -Subject::应收票据贴现利息])
            + $this->calculateISArray($year, $period, [1])
            + $this->calculateBSArray($year, $period, [-4, -5, 36]);
        //收到的税费返还
        $cs2 = $this->calculateArray([Subject::减免税款, Subject::政府补助, Subject::当期所得税费用]);

        //购买商品、接受劳务支付的现金
        $cs5 = $this->calculateArray([Subject::进项税额])
            + $this->calculateISArray($year, $period, [2])
            + $this->calculateBSArray($year, $period, [6, 10, -34, -35]);
        //支付给职工以及为职工支付的现金
        $cs6 = $this->calculateArray([Subject::职工薪酬_169, Subject::职工薪酬_173, Subject::职工薪酬_177, Subject::职工薪酬_223, Subject::职工薪酬_239, -Subject::职工薪酬_54, -Subject::职工薪酬_56, -Subject::职工薪酬_58, -Subject::职工薪酬_60])
            + $this->calculateBSArray($year, $period, [-37]);
        //支付的各项税费
        $cs7 = $this->calculateArray([Subject::当期所得税费用, Subject::税金及附加, Subject::已交税金, -Subject::应交所得税]);
        //支付其他与经营活动有关的现金
        $cs8 = $this->calculateArray([-Subject::利息_247, -Subject::应付职工薪酬, -Subject::累计折旧_50, -Subject::长期待摊费用, -Subject::累计摊销])
            + $this->calculateISArray($year, $period, [3, 4, 5, 6, 14, 17])
            + $this->calculateBSArray($year, $period, [11, -41, -37, -43]);
        //经营活动现金流出小计
        $cs9 = $cs5 + $cs6 + $cs7 + $cs8;

        //收回投资收到的现金
        $cs11 = $this->calculateISArray($year, $period, [8])
            + $this->calculateBSArray($year, $period, [-2, -13, -14, -16, -17]);
        //取得投资收益收到的现金
        $cs12 = $this->calculateISArray($year, $period, [9])
            + $this->calculateBSArray($year, $period, [-7, -8]);
        //处置固定资产、无形资产和其他长期资产收回的现金净额
        $cs13 = $this->calculateArray([Subject::固定资产清理])
            + $this->calculateBSArray($year, $period, [24, 27, 29]);
        //处置子公司及其他营业单位收到的现金净额
        $cs14 = $this->calculateArray([]);
        //收到其他与投资活动有关的现金
        $cs15 = $this->calculateArray([Subject::捐赠收益]);
        //收到其他与投资活动有关的现金
        $cs16 = $cs11 + $cs12 + $cs13 + $cs14 + $cs15;
        //购建固定资产、无形资产和其他长期资产支付的现金
        $cs17 = $this->calculateBSArray($year, $period, [18, 19, 20, 21, 24, 25, 27, 29]);
        //投资支付的现金
        $cs18 = $this->calculateArray([]);
        //取得子公司及其他营业单位支付的现金净额
        $cs19 = $this->calculateArray([]);
        //支付其他与投资活动有关的现金
        $cs20 = $this->calculateArray([]);
        //投资活动现金流出小计
        $cs21 = $cs17 + $cs18 + $cs19 + $cs20;
        //投资活动产生的现金流量净额
        $cs22 = $cs16 - $cs21;

        //吸收投资收到的现金
        $cs23 = $this->calculateBSArray($year, $period, [56]);
        //取得借款收到的现金
        $cs24 = $this->calculateBSArray($year, $period, [32, 33, 46, 47, 48, 49, 53]);
        //发行债券收到的现金
        $cs25 = $this->calculateArray([]);
        //收到其他与筹资活动有关的现金
        $cs26 = $this->calculateArray([]);
        //筹资活动现金流入小计
        $cs27 = $cs23 + $cs24 + $cs25 + $cs26;
        //偿还债务支付的现金
        $cs28 = $this->calculateArray([]);
        //分配股利、利润或偿付利息支付的现金
        $cs29 = $this->calculateArray([Subject::利息_247])
            + $this->calculateBSArray($year, $period, [-39, -40]);
        //支付其他与筹资活动有关的现金
        $cs30 = $this->calculateArray([]);
        //筹资活动现金流出小计
        $cs31 = $cs28 + $cs29 + $cs30;
        //筹资活动产生的现金流量净额
        $cs32 = $cs27 - $cs31;
        //四、汇率变动对现金及现金等价物的影响
        $cs33 = $this->calculateArray([Subject::汇兑损益]);

        //加：期初现金及现金等价物余额
        $cs35 = $this->calculateBSArray($year, $period, [1]);//期初，TODO:待修改
        //六、期末现金及现金等价物余额
        $cs36 = $this->calculateBSArray($year, $period, [1]);//期末，TODO:待修改

        //五、现金及现金等价物净增加额
        $cs34 = $cs36 - $cs35;

        //收到其他与经营活动有关的现金
        $cs3 = $cs34 - $cs1 - $cs2 + $cs9 - $cs22 - $cs32;
        //经营活动现金流入小计
        $cs4 = $cs1 + $cs2 + $cs3;

        //经营活动产生的现金流量净额
        $cs10 = $cs34 - $cs32 - $cs22;

        //净利润
        $cs37 = $this->calculateISArray($year, $period, [19]);//期末
        //加:资产减值准备
        $cs38 = $this->calculateArray([Subject::坏账准备, Subject::存货跌价准备, Subject::持有至到期投资减值准备, Subject::长期股权投资减值准备,
            Subject::投资性房地产减值准备, Subject::固定资产减值准备, Subject::无形资产减值准备]);//期末
        //固定资产折旧、油气资产折耗、生产性生物资产折旧
        $cs39 = $this->calculateArray([Subject::投资性房地产累计折旧, Subject::累计折旧_50, Subject::生产性生物资产累计折旧]);
        //无形资产摊销
        $cs40 = $this->calculateArray([Subject::累计摊销]);
        //长期待摊费用摊销
        $cs41 = $this->calculateArray([Subject::长期待摊费用]);
        //处置固定资产、无形资产和其他长期资产的损失（收益以“－”号填列）
        $cs42 = $this->calculateArray([Subject::非流动资产处置净损失, -Subject::非流动资产处置利得]);
        //固定资产报废损失（收益以“－”号填列）
        $cs43 = $this->calculateArray([]);
        //公允价值变动损失（收益以“－”号填列）
        $cs44 = $this->calculateISArray($year, $period, [-8]);
        //财务费用（收益以“－”号填列）
        $cs45 = $this->calculateArray([Subject::利息_247, Subject::汇兑损益]);
        //投资损失（收益以“－”号填列）
        $cs46 = $this->calculateISArray($year, $period, [-9]);
        //递延所得税资产减少（增加以“－”号填列）
        $cs47 = $this->calculateBSArray($year, $period, [-28]);
        //递延所得税负债增加（减少以“－”号填列）
        $cs48 = $this->calculateBSArray($year, $period, [52]);
        //存货的减少（增加以“－”号填列）
        $cs49 = $this->calculateBSArray($year, $period, [-10]);
        //经营性应收项目的减少（增加以“－”号填列）
        $cs50 = $this->calculateBSArray($year, $period, [-4, -5, -6, -9, -11, -27]);
        //经营性应付项目的增加（减少以“－”号填列）
        $cs51 = $this->calculateBSArray($year, $period, [34, 35, 36, 37, 38, 41, 42, 43]);
        //其他
        $cs52 = $this->calculateArray([]);
        //经营活动产生的现金流量净额
        $cs53 = $cs37 + $cs38 + $cs39 + $cs40 + $cs41 + $cs42 + $cs43 + $cs44 + $cs45 + $cs46 + $cs47
            + $cs48 + $cs49 + $cs50 + $cs51 + $cs52;
        //债务转为资本
        $cs54 = $this->calculateArray([]);
        //一年内到期的可转换公司债券
        $cs55 = $this->calculateArray([]);
        //融资租入固定资产
        $cs56 = $this->calculateArray([]);

        //现金的期末余额
        $cs57 = $this->calculateBSArray($year, $period, [1]);//TODO:期末,代修改
        //减：现金的期初余额
        $cs58 = $this->calculateBSArray($year, $period, [1]);//TODO:期初,代修改

        //加：现金等价物的期末余额
        $cs59 = $this->calculateArray([]);
        //减：现金等价物的期初余额
        $cs60 = $this->calculateArray([]);
        //现金及现金等价物净增加额
        $cs61 = $cs57 - $cs58 + $cs59 - $cs60;
        return array("cs1" => $cs1, "cs2" => $cs2, "cs3" => $cs3, "cs4" => $cs4, "cs5" => $cs5, "cs6" => $cs6, "cs7" => $cs7, "cs8" => $cs8, "cs9" => $cs9,
            "cs10" => $cs10, "cs11" => $cs11, "cs12" => $cs12, "cs13" => $cs13, "cs14" => $cs14, "cs15" => $cs15, "cs16" => $cs16, "cs17" => $cs17, "cs18" => $cs18, "cs19" => $cs19,
            "cs20" => $cs20, "cs21" => $cs21, "cs22" => $cs22, "cs23" => $cs23, "cs24" => $cs24, "cs25" => $cs25, "cs26" => $cs26, "cs27" => $cs27, "cs28" => $cs28, "cs29" => $cs29,
            "cs30" => $cs30, "cs31" => $cs31, "cs32" => $cs32, "cs33" => $cs33, "cs34" => $cs34, "cs35" => $cs35, "cs36" => $cs36, "cs37" => $cs37, "cs38" => $cs38, "cs39" => $cs39,
            "cs40" => $cs40, "cs41" => $cs41, "cs42" => $cs42, "cs43" => $cs43, "cs44" => $cs44, "cs45" => $cs45, "cs46" => $cs46, "cs47" => $cs47, "cs48" => $cs48, "cs49" => $cs49,
            "cs50" => $cs50, "cs51" => $cs51, "cs52" => $cs52, "cs53" => $cs53, "cs54" => $cs54, "cs55" => $cs55, "cs56" => $cs56, "cs57" => $cs57, "cs58" => $cs58, "cs59" => $cs59,
            "cs60" => $cs60, "cs61" => $cs61);
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
     * 待优化，可一次查询
     * @param $year
     * @param $period
     * @param $array
     * @return float|int
     */
    private function calculateBSArray($year, $period, $array)
    {
        //TODO:待修改
        $total = 0.0;
        foreach ($array as $value) {
            $sign = $this->sign($value);
            $balance = $this->getBSById($year, $period, abs($value));
            $total = $total + $sign * $balance;
        }
        return $total;
    }


    /**
     * 待优化，可一次查询
     * @param $year
     * @param $period
     * @param $array
     * @return float|int
     */
    private function calculateISArray($year, $period, $array)
    {
        //TODO:待修改
        $total = 0.0;
        foreach ($array as $value) {
            $sign = $this->sign($value);
            $balance = $this->getISById($year, $period, abs($value));
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
        $value = ReportCashFlowModel::where(['year' => $year, 'period' => $p, 'id' => $id])->value("totalAmount");
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


    /**
     * 查询资产负债表总额
     * @param $year
     * @param $period
     * @param $id
     * @return mixed
     */
    private function getBSById($year, $period, $id)
    {
        $value = ReportBalanceModel::where(["year" => $year, "period" => $period, "id" => $id])->value("endValue");
        if (!$value) {
            $value = 0;
        }
        return $value;
    }

    /**
     * 查询利润表累计总额
     * @param $year
     * @param $period
     * @param $id
     * @return mixed
     */
    private function getISById($year, $period, $id)
    {
        $value = ReportIncomeModel::where(["year" => $year, "period" => $period, "id" => $id])->value("totalAmount");
        if (!$value) {
            $value = 0;
        }
        return $value;
    }

}
