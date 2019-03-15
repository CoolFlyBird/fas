<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Services;

use App\Models\CashFlowTypeModel;
use App\Models\CurrentPeriodModel;
use App\Models\SubjectModel;
use App\Models\VoucherDetailModel;
use App\Models\VoucherModel;
use App\Models\VoucherTemplateDetailModel;
use App\Models\VoucherTemplateModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function __construct(SubjectModel $subjectModel, VoucherModel $voucherModel, VoucherDetailModel $voucherDetailModel,
                                VoucherTemplateModel $voucherTemplateModel, VoucherTemplateDetailModel $voucherTemplateDetailModel,
                                CashFlowTypeModel $cashFlowTypeModel, CurrentPeriodModel $currentPeriodModel)
    {
        $this->subjectModel               = $subjectModel;
        $this->voucherModel               = $voucherModel;
        $this->voucherDetailModel         = $voucherDetailModel;
        $this->voucherTemplateModel       = $voucherTemplateModel;
        $this->voucherTemplateDetailModel = $voucherTemplateDetailModel;
        $this->cashFlowTypeModel          = $cashFlowTypeModel;
        $this->currentPeriodModel          = $currentPeriodModel;
    }

    /**
     * 添加凭证
     * @author huxinlu
     * @param $params
     * @return bool
     */
    public function create($params)
    {
        //判断凭证号是否重复
        $isExist = $this->voucherModel->isExistVoucher((int)$params['proofWordId'], (int)$params['voucherNo']);
        if ($isExist) {
            return ['res' => false, 'msg' => '凭证号重复'];
        }

        DB::beginTransaction();
        try {
            $detailArr = collect($params['detail']);
            //总借方金额
            $allDebit = $detailArr->sum('debit');
            //总贷方金额
            $allCredit = $detailArr->sum('credit');

            //凭证信息
            $voucherData = [
                'proofWordId' => $params['proofWordId'],
                'voucherNo'   => $params['voucherNo'],
                'allDebit'    => $allDebit,
                'allCredit'   => $allCredit,
                'billAmount'  => $params['billAmount'],
                'date'        => $params['date'],
                'maker'       => Auth::user()->username,
            ];
            $voucherId   = $this->voucherModel->insertGetId($voucherData);

            //凭证详情
            $this->voucherDetailModel->addAll($this->getVoucherDetailData($voucherId, $params['date'], $params['detail']));

            DB::commit();
            return ['res' => true, 'msg' => '成功'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['res' => false, 'msg' => '添加凭证失败'];
        }
    }

    /**
     * 编辑凭证
     * @author huxinlu
     * @param $params
     * @return bool
     */
    public function edit($params)
    {
        //判断凭证号是否重复
        $isExist = $this->voucherModel->isExistVoucherExceptSelf((int)$params['id'], (int)$params['proofWordId'], (int)$params['voucherNo']);
        if ($isExist) {
            return ['res' => false, 'msg' => '凭证号重复'];
        }

        DB::beginTransaction();
        try {
            $detailArr = collect($params['detail']);
            //总借方金额
            $allDebit = $detailArr->sum('debit');
            //总贷方金额
            $allCredit = $detailArr->sum('credit');

            //凭证信息
            $voucherData = [
                'id'          => $params['id'],
                'proofWordId' => $params['proofWordId'],
                'voucherNo'   => $params['voucherNo'],
                'allDebit'    => $allDebit,
                'allCredit'   => $allCredit,
                'billAmount'  => $params['billAmount'],
                'date'        => $params['date'],
                'maker'       => Auth::user()->username,
            ];
            $this->voucherModel->edit($voucherData);

            //删除之前的凭证详情
            $this->voucherDetailModel->delAll(['voucherId' => $params['id']]);

            //凭证详情
            $this->voucherDetailModel->addAll($this->getVoucherDetailData((int)$params['id'], $params['date'], $params['detail']));

            DB::commit();
            return true;
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();
            return false;
        }
    }

    /**
     * 获取凭证详情拼接数据
     * @author huxinlu
     * @param int $voucherId 凭证ID
     * @param string $date 凭证日期
     * @param $params
     * @return array
     */
    private function getVoucherDetailData(int $voucherId, string $date, array $params)
    {
        $data = [];
        foreach ($params as $k => $v) {
            //科目详情
            $subjectDetail = $this->subjectModel->getDetail($v['subjectId']);
            //科目-中文显示
            $subject = $subjectDetail['code'] . ' ' . $subjectDetail['name'];
            if (strlen($v['code']) == 4) {
                //辅助核算类型详情
                $typeDetail = $this->cashFlowTypeModel->getDetail($v['cashFlowTypeId']);
                $subject .= ' ' . $typeDetail['name'];
            } else {
                $v['cashFlowTypeId'] = 0;
            }

            $data[$k]['voucherId']      = $voucherId;
            $data[$k]['summary']        = $v['summary'];
            $data[$k]['subjectId']      = $v['subjectId'];
            $data[$k]['cashFlowTypeId'] = $v['cashFlowTypeId'];
            $data[$k]['subject']        = $subject;
            $data[$k]['debit']          = $v['debit'];
            $data[$k]['credit']         = $v['credit'];
            $data[$k]['date']           = $date;
        }

        return $data;
    }

    /**
     * 凭证详情
     * @author huxinlu
     * @param int $id 凭证ID
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getVoucherDetail(int $id)
    {
        $voucherDetail             = $this->voucherModel->getDetail($id);
        $voucherDetail['children'] = $this->voucherDetailModel->getList(['voucherId' => $id]);

        return $voucherDetail;
    }

    public function getVoucherList($params)
    {
        $where = $whereMonth = $whereYear = $whereBetween = [];
        if ($params['range'] != -1) {
            switch ($params['range']) {
                //未审核
                case 1:
                    $where['status'] = $this->voucherModel::STATUS_UNCHECKED;
                    break;
                //本期
                case 2:
                    $whereMonth = ['date' => $this->currentPeriodModel->getCurrentPeriod()];
                    break;
                //本年
                case 3:
                    $whereYear = ['date' => date('Y')];
                    break;
                //时间段
                case 4:
                    $whereBetween = ['date' => [$params['startDate'], $params['endDate']]];
                    break;
                default:
                    break;
            }
        }

        return $this->voucherModel->getVoucherList($where, $whereMonth, $whereYear, $whereBetween);
    }
}
