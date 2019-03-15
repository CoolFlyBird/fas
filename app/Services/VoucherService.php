<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Services;

use App\Models\CashFlowTypeModel;
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
                                CashFlowTypeModel $cashFlowTypeModel)
    {
        $this->subjectModel  = $subjectModel;
        $this->voucherModel = $voucherModel;
        $this->voucherDetailModel = $voucherDetailModel;
        $this->voucherTemplateModel = $voucherTemplateModel;
        $this->voucherTemplateDetailModel = $voucherTemplateDetailModel;
        $this->cashFlowTypeModel = $cashFlowTypeModel;
    }

    public function create($params)
    {
        DB::beginTransaction();
        try {
            $detailArr = collect($params['detail']);
            //总借方金额
            $allDebit = $detailArr->sum('debit');
            //总贷方金额
            $allCredit = $detailArr->sum('credit');

            //凭证信息
            $voucherData = [
                'voucherNo' => $params['voucherNo'],
                'allDebit' => $allDebit,
                'allCredit' => $allCredit,
                'billAmount' => $params['billAmount'],
                'date' => $params['date'],
                'maker' => Auth::user()->username,
            ];
            $voucherId = $this->voucherModel->insertGetId($voucherData);

            //凭证详情信息
            $voucherDetailData = [];
            foreach ($params['detail'] as $k => $v) {
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

                $voucherDetailData[$k]['voucherId'] = $voucherId;
                $voucherDetailData[$k]['summary'] = $v['summary'];
                $voucherDetailData[$k]['subjectId'] = $v['subjectId'];
                $voucherDetailData[$k]['cashFlowTypeId'] = $v['cashFlowTypeId'];
                $voucherDetailData[$k]['subject'] = $subject;
                $voucherDetailData[$k]['debit'] = $v['debit'];
                $voucherDetailData[$k]['credit'] = $v['credit'];
                $voucherDetailData[$k]['date'] = $params['date'];
            }

            $this->voucherDetailModel->addAll($voucherDetailData);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
