<?php

namespace App\Http\Controllers\Finance;

use App\Services\VoucherService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VoucherManageController extends Controller
{
    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * 审核
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit(Request $request)
    {
        $params    = $request->only(['id']);
        $validator = Validator::make($params, [
            'id' => 'required',
        ], [
            'id.required' => '凭证ID不能为空',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherService->audit($params['id']);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 反审核
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function review(Request $request)
    {
        $params    = $request->only(['id']);
        $validator = Validator::make($params, [
            'id' => 'required',
        ], [
            'id.required' => '凭证ID不能为空',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherService->review($params['id']);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 编辑凭证
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editVoucher(Request $request)
    {
        $params    = $request->only(['id', 'proofWordId', 'voucherNo', 'billAmount', 'date', 'detail']);
        $validator = Validator::make($params, [
            'id'                       => 'required|exists:voucher',
            'proofWordId'              => 'required|exists:proof_word,id',
            'voucherNo'                => 'required|integer',
            'billAmount'               => 'required|integer',
            'date'                     => 'required|date_format:Y-m-d',
            'detail.*.summary'         => 'required|max:100',
            'detail.*.subjectId'       => 'required|exists:subject,id',
            'detail.*.auxiliaryTypeId' => 'required',
            'detail.*.auxiliaryId'     => 'required',
            'detail.*.code'            => 'required|exists:subject,code',
            'detail.*.debit'           => 'required|numeric',
            'detail.*.credit'          => 'required|numeric',
        ], [
            'id.required'                       => '凭证ID不能为空',
            'id.exists'                         => '该凭证不存在',
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

        $res = $this->voucherService->edit($params);
        return $res ? $this->success() : $this->fail('编辑凭证失败');
    }

    /**
     * 凭证详情
     * @author huxinlu
     * @param int $id 凭证ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVoucherDetail(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:voucher',
        ], [
            'id.required' => '凭证ID不能为空',
            'id.exists'   => '该凭证不存在',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $detail = $this->voucherService->getVoucherDetail($id);

        return $this->success($detail);
    }

    /**
     * 凭证列表
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVoucherList(Request $request)
    {
        $params    = $request->only(['range', 'classes', 'money', 'summary', 'limit']);
        $validator = Validator::make($params, [
            'range'   => 'in:1,2,3,4',
            'money'   => 'numeric',
            'summary' => 'max:50',
            'limit'   => 'integer|min:1',
        ], [
            'range.in'       => '范围类型不正确',
            'classes.exists' => '凭证类别存在',
            'money.numeric'  => '金额只能是数字',
            'summary.max'    => '摘要不能超过50个字符',
            'limit.integer'  => '每页显示数只能是整数',
            'limit.min'      => '每页显示数最小是1',
        ]);
        $validator->sometimes(['startDate', 'endDate'], 'required', function ($input) {
            return $input->range == 4;
        });
        $validator->sometimes('classes', 'exists:proof_word,id', function ($input) {
            if (isset($input->classes) && $input->classes != -1) {
                return true;
            }
        });
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $params['range']   = $params['range'] ?? 2;
        $params['classes'] = $params['classes'] ?? -1;
        $params['money']   = $params['money'] ?? 0.00;
        $params['summary'] = $params['summary'] ?? '';
        $params['limit']   = $params['limit'] ?? 20;

        $list = $this->voucherService->getVoucherList($params);

        return $this->success($list);
    }

    /**
     * 删除凭证
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delVoucher(Request $request)
    {
        $params    = $request->only(['id']);
        $validator = Validator::make($params, [
            'id' => 'required',
        ], [
            'id.required' => '凭证ID不能为空',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->voucherService->delVoucher($params['id']);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }
}
