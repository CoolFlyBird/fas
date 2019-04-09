<?php

namespace App\Http\Controllers\Finance;

use App\Models\VoucherTemplateModel;
use App\Models\VoucherTemplateTypeModel;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    public function __construct(VoucherService $voucherService, VoucherTemplateTypeModel $voucherTemplateTypeModel,
                                VoucherTemplateModel $voucherTemplateModel)
    {
        $this->voucherService           = $voucherService;
        $this->voucherTemplateTypeModel = $voucherTemplateTypeModel;
        $this->voucherTemplateModel     = $voucherTemplateModel;
    }

    /**
     * 添加凭证
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createVoucher(Request $request)
    {
        $params    = $request->only(['proofWordId', 'voucherNo', 'billAmount', 'date', 'detail']);
        $validator = Validator::make($params, [
            'proofWordId'        => 'required|exists:proof_word,id',
            'voucherNo'          => 'required|integer',
            'billAmount'         => 'required|integer',
            'date'               => 'required|date_format:Y-m-d',
            'detail.*.summary'   => 'required|max:100',
            'detail.*.subjectId' => 'required|exists:subject,id',
            'detail.*.code'      => 'required|exists:subject,code',
            'detail.*.debit'     => 'required|numeric',
            'detail.*.credit'    => 'required|numeric',
        ], [
            'proofWordId.required'              => '凭证类别不能为空',
            'proofWordId.exists'                => '凭证类别不存在',
            'voucherNo.required'                => '凭证号不能为空',
            'voucherNo.integer'                 => '凭证号只能是整数',
            'billAmount.required'               => '单据数量不能为空',
            'billAmount.integer'                => '单据数量只能是整数',
            'date.required'                     => '凭证日期不能为空',
            'date.date_format'                  => '凭证日期格式不正确，正确格式为：' . date('Y-m-d'),
            'detail.*.summary.required'         => '摘要不能为空',
            'detail.*.summary.max'              => '摘要不能超过100个字符',
            'detail.*.subjectId.required'       => '科目ID不能为空',
            'detail.*.subjectId.exists'         => '该科目不存在',
            'detail.*.code.required'            => '科目编码不能为空',
            'detail.*.code.exists'              => '该科目不存在',
            'detail.*.auxiliaryTypeId.required' => '辅助核算类型ID不能为空',
            'detail.*.auxiliaryId.required'     => '辅助核算ID不能为空',
            'detail.*.debit.required'           => '借方金额不能为空',
            'detail.*.debit.numeric'            => '借方金额只能是数字',
            'detail.*.credit.required'          => '贷方金额不能为空',
            'detail.*.credit.numeric'           => '贷方金额只能是数字',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherService->create($params);
        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 添加模板类别
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createVoucherTemplateType(Request $request)
    {
        $params    = $request->only(['name']);
        $validator = Validator::make($params, [
            'name' => 'required|unique:voucher_template_type',
        ], [
            'name.required' => '类别不能为空',
            'name.unique'   => '类别不能重复',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherTemplateTypeModel->add(['name' => $params['name']]);

        return $res ? $this->success() : $this->fail('添加模板类别失败');
    }

    /**
     * 编辑模板类别
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editVoucherTemplateType(Request $request)
    {
        $params    = $request->only(['id', 'name']);
        $validator = Validator::make($params, [
            'id'   => 'required|exists:voucher_template_type',
            'name' => 'required',
        ], [
            'id.required'   => '类别ID不能为空',
            'id.exists'     => '改模板类别不存在',
            'name.required' => '类别不能为空',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherService->editVoucherTemplateType($params);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 模板类别列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVoucherTemplateTypeList()
    {
        $list = $this->voucherTemplateTypeModel->getList();

        return $this->success($list);
    }

    /**
     * 删除凭证模板类别
     * @author huxinlu
     * @param int $id 凭证模板类别ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delVoucherTemplateType(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:voucher_template_type',
        ], [
            'id.required' => '凭证类别ID不能为空',
            'id.exists'   => '该凭证类别不存在',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherTemplateTypeModel->del($id);

        return $res ? $this->success() : $this->fail('删除凭证模板类别失败');
    }

    /**
     * 添加模板
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createVoucherTemplate(Request $request)
    {
        $params    = $request->only(['name', 'type', 'proofWordId', 'voucherNo', 'billAmount', 'date', 'detail']);
        $validator = Validator::make($params, [
            'name'               => 'required|unique:voucher_template',
            'type'               => 'required|exists:voucher_template_type,id',
            'proofWordId'        => 'required|exists:proof_word,id',
            'voucherNo'          => 'required|integer',
            'billAmount'         => 'required|integer',
            'date'               => 'required|date_format:Y-m-d',
            'detail.*.summary'   => 'required|max:100',
            'detail.*.subjectId' => 'required|exists:subject,id',
            'detail.*.code'      => 'required|exists:subject,code',
            'detail.*.debit'     => 'required|numeric',
            'detail.*.credit'    => 'required|numeric',
        ], [
            'name.required'                    => '模板名称不能为空',
            'name.unique'                      => '模板名称不能重复',
            'type.required'                    => '模板类别不能为空',
            'type.exists'                      => '模板类别不存在',
            'proofWordId.required'             => '凭证类别不能为空',
            'proofWordId.exists'               => '凭证类别不存在',
            'voucherNo.required'               => '凭证号不能为空',
            'voucherNo.integer'                => '凭证号只能是整数',
            'billAmount.required'              => '单据数量不能为空',
            'billAmount.integer'               => '单据数量只能是整数',
            'date.required'                    => '凭证日期不能为空',
            'date.date_format'                 => '凭证日期格式不正确，正确格式为：' . date('Y-m-d'),
            'detail.*.summary.required'        => '摘要不能为空',
            'detail.*.summary.max'             => '摘要不能超过100个字符',
            'detail.*.subjectId.required'      => '科目ID不能为空',
            'detail.*.subjectId.exists'        => '该科目不存在',
            'detail.*.code.required'           => '科目编码不能为空',
            'detail.*.code.exists'             => '该科目不存在',
            'detail.*.auxiliaryTypeId.required' => '辅助核算类型ID不能为空',
            'detail.*.auxiliaryId.required'     => '辅助核算ID不能为空',
            'detail.*.debit.required'          => '借方金额不能为空',
            'detail.*.debit.numeric'           => '借方金额只能是数字',
            'detail.*.credit.required'         => '贷方金额不能为空',
            'detail.*.credit.numeric'          => '贷方金额只能是数字',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherService->createVoucherTemplate($params);
        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 模板详情
     * @author huxinlu
     * @param int $id 模板ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVoucherTemplateDetail(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:voucher_template',
        ], [
            'id.required' => '模板ID不能为空',
            'id.exists'   => '该模板不存在',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $detail = $this->voucherService->getVoucherTemplateDetail($id);

        return $this->success($detail);
    }

    /**
     * 模板列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVoucherTemplateList()
    {
        $list = $this->voucherService->getVoucherTemplateList();

        return $this->success($list);
    }

    /**
     * 删除模板
     * @author huxinlu
     * @param int $id 模板ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delVoucherTemplate(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:voucher_template',
        ], [
            'id.required' => '模板ID不能为空',
            'id.exists'   => '该模板不存在',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherTemplateModel->del($id);

        return $res ? $this->success() : $this->fail('删除模板失败');
    }
}
