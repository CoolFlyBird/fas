<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Services;

use App\Models\CashFlowTypeModel;
use App\Models\ClientModel;
use App\Models\CurrentPeriodModel;
use App\Models\DepartmentModel;
use App\Models\EmployeeModel;
use App\Models\ProjectModel;
use App\Models\ProofWordModel;
use App\Models\StockModel;
use App\Models\SubjectModel;
use App\Models\SupplierModel;
use App\Models\VoucherDetailModel;
use App\Models\VoucherModel;
use App\Models\VoucherTemplateDetailModel;
use App\Models\VoucherTemplateModel;
use App\Models\VoucherTemplateTypeModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function __construct(SubjectModel $subjectModel, VoucherModel $voucherModel, VoucherDetailModel $voucherDetailModel,
                                VoucherTemplateModel $voucherTemplateModel, VoucherTemplateDetailModel $voucherTemplateDetailModel,
                                CashFlowTypeModel $cashFlowTypeModel, CurrentPeriodModel $currentPeriodModel, ClientModel $clientModel,
                                ProofWordModel $proofWordModel, VoucherTemplateTypeModel $voucherTemplateTypeModel, SupplierModel $supplierModel,
                                EmployeeModel $employeeModel, ProjectModel $projectModel, DepartmentModel $departmentModel,
                                StockModel $stockModel)
    {
        $this->subjectModel               = $subjectModel;
        $this->voucherModel               = $voucherModel;
        $this->voucherDetailModel         = $voucherDetailModel;
        $this->voucherTemplateModel       = $voucherTemplateModel;
        $this->voucherTemplateDetailModel = $voucherTemplateDetailModel;
        $this->cashFlowTypeModel          = $cashFlowTypeModel;
        $this->currentPeriodModel         = $currentPeriodModel;
        $this->proofWordModel             = $proofWordModel;
        $this->voucherTemplateTypeModel   = $voucherTemplateTypeModel;
        $this->clientModel                = $clientModel;
        $this->supplierModel              = $supplierModel;
        $this->employeeModel              = $employeeModel;
        $this->projectModel               = $projectModel;
        $this->departmentModel            = $departmentModel;
        $this->stockModel                 = $stockModel;
    }

    /**
     * 添加凭证
     * @author huxinlu
     * @param $params
     * @return bool
     */
    public function create($params)
    {
        ////判断凭证日期是否小于当前期
        $currentDate = $this->currentPeriodModel->getCurrentYear() . '-' . $this->currentPeriodModel->getCurrentPeriod();
        if ($params['date'] < $currentDate) {
            return ['res' => false, 'msg' => '该日期已结账'];
        }

        //判断凭证号是否重复
        $isExist = $this->voucherModel->isExistVoucher((int)$params['proofWordId'], (int)$params['voucherNo']);
        if ($isExist) {
            return ['res' => false, 'msg' => '凭证号重复'];
        }

        $detailArr = collect($params['detail']);
        //总借方金额
        $allDebit = $detailArr->sum('debit');
        //总贷方金额
        $allCredit = $detailArr->sum('credit');
        if ($allDebit != $allCredit) {
            return ['res' => false, 'msg' => '借贷金额不相等，请重新添加'];
        }

        foreach ($params['detail'] as $k => $v) {
            //判断科目是否有辅助核算
            $isExistAssist = $this->subjectModel->isExistAssist($v['subjectId'], $v['auxiliaryTypeId']);
            if (!$isExistAssist) {
                return ['res' => false, 'msg' => '辅助核算类型不正确'];
            }

            $params['detail'][$k]['subject'] = $this->getSubject($v['subjectId'], $v['auxiliaryTypeId'], $v['auxiliaryId']);
        }

        DB::beginTransaction();
        try {

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
            logger($e);
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

        foreach ($params['detail'] as $k => $v) {
            //判断科目是否有辅助核算
            $isExistAssist = $this->subjectModel->isExistAssist($v['subjectId'], $v['auxiliaryTypeId']);
            if (!$isExistAssist) {
                return ['res' => false, 'msg' => '辅助核算类型不正确'];
            }

            $params['detail'][$k]['subject'] = $this->getSubject($v['subjectId'], $v['auxiliaryTypeId'], $v['auxiliaryId']);
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

            $data[$k]['voucherId']       = $voucherId;
            $data[$k]['summary']         = $v['summary'];
            $data[$k]['subjectId']       = $v['subjectId'];
            $data[$k]['auxiliaryTypeId'] = $v['auxiliaryTypeId'];
            $data[$k]['auxiliaryId']     = $v['auxiliaryId'];
            $data[$k]['subject']         = $subject;
            $data[$k]['debit']           = $v['debit'];
            $data[$k]['credit']          = $v['credit'];
            $data[$k]['date']            = $date;
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
        $voucherDetail           = $this->voucherModel->getDetail($id);
        $voucherDetail['detail'] = $this->voucherDetailModel->getList(['voucherId' => $id]);

        return $voucherDetail;
    }

    /**
     * 凭证列表
     * @author huxinlu
     * @param $params
     * @return array
     */
    public function getVoucherList($params)
    {
        //当前期数
        $params['period'] = $this->currentPeriodModel->getCurrentPeriod();
        //未审核状态
        $params['status'] = $this->voucherModel::STATUS_UNCHECKED;

        $list = $this->voucherDetailModel->getVoucherList($params);
        $data = [];
        foreach ($list['data'] as $k => $v) {
            //凭证字
            $proofDetail = $this->proofWordModel->getDetail($v['proofWordId']);
            $voucherNo   = $proofDetail ? $proofDetail['name'] . $v['voucherNo'] : '';

            $data[$v['id']]['id']        = $v['id'];
            $data[$v['id']]['date']      = $v['date'];
            $data[$v['id']]['voucherNo'] = $voucherNo;
            $data[$v['id']]['status']    = $v['status'];
            $data[$v['id']]['maker']     = $v['maker'];
            $data[$v['id']]['auditor']   = $v['auditor'];
            $data[$v['id']]['reviewer']  = $v['reviewer'];
            $data[$v['id']]['detail'][]  = [
                'summary'         => $v['summary'],
                'subjectId'       => $v['subjectId'],
                'auxiliaryTypeId' => $v['auxiliaryTypeId'],
                'auxiliaryId'     => $v['auxiliaryId'],
                'subject'         => $v['subject'],
                'debit'           => $v['debit'],
                'credit'          => $v['credit'],
            ];

        }

        return ['data' => array_values($data), 'totalCount' => $list['total']];
    }

    /**
     * 审核
     * @author huxinlu
     * @param string $ids 凭证ID
     * @return array|mixed
     */
    public function audit($ids)
    {
        DB::beginTransaction();
        try {
            $idArr = explode(',', $ids);
            $error = '';
            foreach ($idArr as $id) {
                $isExist = $this->voucherModel->isExistUnchecked($id);
                if (!$isExist) {
                    //凭证详情
                    $detail = $this->voucherModel->getDetail($id);
                    //凭证字详情
                    $wordDetail = $this->proofWordModel->getDetail((int)$detail['proofWordId']);
                    $error .= $detail['date'] . '-' . $wordDetail['name'] . ',';
                } else {
                    $this->voucherModel->editStatusPass($id, Auth::user()->username);
                }
            }


            DB::commit();
            if (!empty($error)) {
                return ['res' => false, 'msg' => '日期凭证号为' . $error . '不能进行审核操作'];
            }
            return ['res' => true, 'msg' => '成功'];
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();
            return ['res' => false, 'msg' => '审核失败'];
        }
    }

    /**
     * 反审核
     * @author huxinlu
     * @param string $ids 凭证ID
     * @return array|mixed
     */
    public function review($ids)
    {
        DB::beginTransaction();
        try {
            $idArr = explode(',', $ids);
            $error = '';
            foreach ($idArr as $id) {
                $isExist = $this->voucherModel->isExistPass($id);
                if (!$isExist) {
                    //凭证详情
                    $detail = $this->voucherModel->getDetail($id);
                    //凭证字详情
                    $wordDetail = $this->proofWordModel->getDetail((int)$detail['proofWordId']);
                    $error .= $detail['date'] . '-' . $wordDetail['name'] . ',';
                } else {
                    $this->voucherModel->editStatusPass($id, Auth::user()->username);
                }
            }


            DB::commit();
            if (!empty($error)) {
                return ['res' => false, 'msg' => '日期凭证号为' . $error . '该状态下不能反审核'];
            }
            return ['res' => true, 'msg' => '成功'];
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();
            return ['res' => false, 'msg' => '反审核失败'];
        }
    }

    /**
     * 编辑类别
     * @author huxinlu
     * @param array $params
     * @return array
     */
    public function editVoucherTemplateType(array $params)
    {
        $isExist = $this->voucherTemplateTypeModel->isExistReceiptExceptSelf($params['id'], $params['name']);
        if ($isExist) {
            return ['res' => false, 'msg' => '类别名称不能重复'];
        }

        $res = $this->voucherTemplateTypeModel->edit($params);

        return $res ? ['res' => true, 'msg' => '成功'] : ['res' => false, 'msg' => '编辑模板类别失败'];
    }

    /**
     * 添加模板
     * @author huxinlu
     * @param $params
     * @return array
     */
    public function createVoucherTemplate($params)
    {
        DB::beginTransaction();
        try {
            $detailArr = collect($params['detail']);
            //总借方金额
            $allDebit = $detailArr->sum('debit');
            //总贷方金额
            $allCredit = $detailArr->sum('credit');

            //模板信息
            $templateData = [
                'name'        => $params['name'],
                'type'        => $params['type'],
                'proofWordId' => $params['proofWordId'],
                'voucherNo'   => $params['voucherNo'],
                'allDebit'    => $allDebit,
                'allCredit'   => $allCredit,
                'billAmount'  => $params['billAmount'],
                'date'        => $params['date'],
            ];
            $templateId   = $this->voucherTemplateModel->insertGetId($templateData);

            $templateDetailData = [];
            foreach ($params['detail'] as $k => $v) {
                //判断科目是否有辅助核算
                $isExistAssist = $this->subjectModel->isExistAssist($v['subjectId'], $v['auxiliaryTypeId']);
                if (!$isExistAssist) {
                    DB::rollBack();
                    return ['res' => false, 'msg' => '辅助核算类型不正确'];
                }

                $templateDetailData[$k]['voucherTemplateId'] = $templateId;
                $templateDetailData[$k]['summary']           = $v['summary'];
                $templateDetailData[$k]['subjectId']         = $v['subjectId'];
                $templateDetailData[$k]['auxiliaryTypeId']   = $v['auxiliaryTypeId'];
                $templateDetailData[$k]['auxiliaryId']       = $v['auxiliaryId'];
                $templateDetailData[$k]['subject']           = $this->getSubject($v['subjectId'], $v['auxiliaryTypeId'], $v['auxiliaryId']);
                $templateDetailData[$k]['debit']             = $v['debit'];
                $templateDetailData[$k]['credit']            = $v['credit'];
            }

            //凭证详情
            $this->voucherTemplateDetailModel->addAll($templateDetailData);

            DB::commit();
            return ['res' => true, 'msg' => '成功'];
        } catch (\Exception $e) {
            logger($e);
            DB::rollBack();
            return ['res' => false, 'msg' => '添加模板失败'];
        }
    }

    /**
     * 模板详情
     * @author huxinlu
     * @param int $id 模板ID
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getVoucherTemplateDetail(int $id)
    {
        $templateDetail                  = $this->voucherTemplateModel->getDetail($id);
        $detail                          = $this->proofWordModel->getDetail((int)$templateDetail['proofWordId']);
        $templateDetail['proofWordName'] = $detail ? $detail['name'] : '';
        $templateDetail['detail']        = $this->voucherTemplateDetailModel->getList(['voucherTemplateId' => $id]);

        return $templateDetail;
    }

    /**
     * 模板列表
     * @author huxinlu
     * @return array
     */
    public function getVoucherTemplateList()
    {
        $list = $this->voucherTemplateModel->getList();
        $data = [];
        foreach ($list as $k => $v) {
            $detail           = $this->voucherTemplateTypeModel->getDetail((int)$v['type']);
            $data[$k]['id']   = $v['id'];
            $data[$k]['type'] = $detail['name'] ?? '';
            $data[$k]['name'] = $v['name'];
        }

        return $data;
    }

    /**
     * 会计科目名称（科目编码+科目名称+辅助核算名称）
     * @author huxinlu
     * @param $subjectId int 科目ID
     * @param $auxiliaryTypeId int 辅助核算类型ID
     * @param $auxiliaryId int 辅助核算ID
     * @return string
     */
    private function getSubject($subjectId, $auxiliaryTypeId, $auxiliaryId)
    {
        switch ($auxiliaryTypeId) {
            //客户
            case $this->subjectModel::AUXILIARY_CLIENT:
                $model = $this->clientModel;
                break;
            //供应商
            case $this->subjectModel::AUXILIARY_SUPPLIER:
                $model = $this->supplierModel;
                break;
            //职员
            case $this->subjectModel::AUXILIARY_EMPLOYEE:
                $model = $this->employeeModel;
                break;
            //项目
            case $this->subjectModel::AUXILIARY_PROJECT:
                $model = $this->projectModel;
                break;
            //部门
            case $this->subjectModel::AUXILIARY_DEPARTMENT:
                $model = $this->departmentModel;
                break;
            //存货
            case $this->subjectModel::AUXILIARY_STOCK:
                $model = $this->stockModel;
                break;
            //现金流量核算
            case $this->subjectModel::AUXILIARY_CASH:
                $model = $this->cashFlowTypeModel;
                break;
            default:
                $model = null;
                break;
        }

        if ($model) {
            $auxiliaryName = $model->getName($auxiliaryId);
            $auxiliaryName = $auxiliaryName ?? '';
        } else {
            $auxiliaryName = '';
        }

        //科目详情
        $subjectDetail = $this->subjectModel->getDetail($subjectId);
        if ($subjectDetail) {
            $code = $subjectDetail['code'];
            $name = $subjectDetail['name'];
        } else {
            $code = $name = '';
        }

        return $code . ' ' . $name . ' ' . $auxiliaryName;
    }
}
