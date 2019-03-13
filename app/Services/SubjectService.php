<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Services;

use App\Models\SubjectModel;

class SubjectService
{
    /**
     * 添加科目
     * @author huxinlu
     * @param $params
     * @return array
     */
    public function create($params)
    {
        $subjectModel = new SubjectModel();

        //最大编码
        $maxCode = $subjectModel->getMaxCode($params['parentSubjectCode'], $params['type']);
        switch ($params['type']) {
            //类型=资产
            case $subjectModel::TYPE_PROPERTY:
                $firstDefaultCode = '1000';
                if (!($params['classes'] == '流动资产' || $params['classes'] == '非流动资产')) {
                    $msg = '资产类别不正确';
                }
                break;
            //类型=负债
            case $subjectModel::TYPE_DEBT:
                $firstDefaultCode = '2000';
                if (!($params['classes'] == '流动负债' || $params['classes'] == '非流动负债')) {
                    $msg = '负债类别不正确';
                }
                break;
            //类型=共同
            case $subjectModel::TYPE_COMMON:
                $firstDefaultCode = '3000';
                $params['classes'] = '共同';
                break;
            //类型=权益
            case $subjectModel::TYPE_EQUITY:
                $firstDefaultCode = '4000';
                $params['classes'] = '所有者权益';
                break;
            //类型=成本
            case $subjectModel::TYPE_COST:
                $firstDefaultCode = '5000';
                $params['classes'] = '成本';
                break;
            //类型=损益
            case $subjectModel::TYPE_PROFIT:
                $firstDefaultCode = '6000';
                if (!($params['classes'] == '营业收入' || $params['classes'] == '其他收益' || $params['classes'] == '营业成本及税金' ||
                    $params['classes'] == '其他损失' || $params['classes'] == '期间费用' || $params['classes'] == '所得税' ||
                    $params['classes'] == '以前年度损益调整')) {
                    $msg = '损益类别不正确';
                }
                break;
            default:
                $firstDefaultCode = '';
                break;
        }
        if ($params['parentSubjectCode'] == 0) {
            if (empty($firstDefaultCode)) {
                $msg = '类型不正确';
            }
            $params['code'] = empty($maxCode) ? $firstDefaultCode : $maxCode + 1;
        } else {
            if (strlen($params['parentSubjectCode']) > 6) {
                $msg = '最多只能是三级科目';
            }
            $params['code'] = empty($maxCode) ? $params['parentSubjectCode'] . '01' : $maxCode + 1;
        }

        if (isset($msg) && !empty($msg)) {
            return ['res' => false, 'msg' => $msg];
        } else {
            $res = $subjectModel->add($params);
            return $res ? ['res' => true] : ['res' => false, 'msg' => '添加科目失败'];
        }
    }

    /**
     * 科目列表
     * @author huxinlu
     * @param int $type 科目类型
     * @return array
     */
    public function getList(int $type)
    {
        $subjectModel = new SubjectModel();
        $where = ['parentSubjectCode' => '', 'status' => $subjectModel::STATUS_START];
        if ($type != 0) {
            $where['type'] = $type;
        }

        $list = $subjectModel->getPageList($where);
        $data = [];
        foreach ($list['data'] as $k => $v) {
            $data = array_merge($data, $this->getNextList($v));
        }

        return $data;
    }

    /**
     * 所有下级列表
     * @author huxinlu
     * @param array $data 上级科目一维数组
     * @return array
     */
    private function getNextList(array $data)
    {
        $subjectModel = new SubjectModel();

        $list[] = $data;
        $nextArr = $subjectModel->getList(['type' => $data['type'], 'parentSubjectCode' => $data['code'], 'status' => $subjectModel::STATUS_START]);
        foreach ($nextArr as $v) {
            $res = $this->getNextList($v);
            $list = array_merge($list, $res);
        }

        return $list;
    }

    /**
     * 启用科目
     * @author huxinlu
     * @param int $id 科目ID
     * @return array
     */
    public function start(int $id)
    {
        $subjectModel  = new SubjectModel();

        //科目详情
        $detail = $subjectModel->getDetail($id);
        if ($detail['status'] == $subjectModel::STATUS_NOT_START) {
            $res = $subjectModel->edit(['id' => $id, 'status' => $subjectModel::STATUS_START]);
            return $res ? ['res' => true] : ['res' => false, 'msg' => '启用科目失败'];
        } else {
            return ['res' => false, 'msg' => '该状态下无法启用'];
        }
    }

    /**
     * 编辑期初余额
     * @author huxinlu
     * @param int $id 科目ID
     * @param float $initialBalance 期初余额
     * @return array
     */
    public function editInitialBalance(int $id, float $initialBalance)
    {
        $subjectModel = new SubjectModel();

        //是否存在已启用的科目
        $isExist = $subjectModel->isExistStartSubject($id);
        if ($isExist) {
            //编辑期初余额
            $res = $subjectModel->edit(['id' => $id, 'initialBalance' => $initialBalance]);
            return $res ? ['res' => true] : ['res' => false, 'msg' => '编辑期初余额失败'];
        } else {
            return ['res' => false, 'msg' => '该科目不存在或未启用'];
        }
    }

    /**
     * 编辑数量
     * @author huxinlu
     * @param int $id 科目ID
     * @param float $amount 数量
     * @return array
     */
    public function editAmount(int $id, float $amount)
    {
        $subjectModel = new SubjectModel();

        //是否存在已启用的科目
        $isExist = $subjectModel->isExistStartSubject($id);
        if ($isExist) {
            //编辑数量
            $res = $subjectModel->edit(['id' => $id, 'amount' => $amount]);
            return $res ? ['res' => true] : ['res' => false, 'msg' => '编辑数量失败'];
        } else {
            return ['res' => false, 'msg' => '该科目不存在或未启用'];
        }
    }

    public function calculateBalance()
    {
        $subjectModel = new SubjectModel();

        //总借方金额
        $allDebitBalance = $subjectModel->getAllDebitBalance();
        //总贷方金额
        $allCreditBalance = $subjectModel->getAllCreditBalance();
        //总差额
        $allBalance = abs($allDebitBalance - $allCreditBalance);
        //本年借方金额
        $currentDebitBalance = $subjectModel->getCurrentYearDebitBalance();
        //本年贷方金额
        $currentCreditBalance = $subjectModel->getCurrentYearCreditBalance();
        //本年差额
        $currentBalance = abs($currentDebitBalance - $currentCreditBalance);

        return [
            [
                'name' => '期初余额',
                'debit' => $allDebitBalance,
                'credit' => $allCreditBalance,
                'balance' => $allBalance,
            ],
            [
                'name' => '本年累计发生额',
                'debit' => $currentDebitBalance,
                'credit' => $currentCreditBalance,
                'balance' => $currentBalance
            ]
        ];
    }
}
