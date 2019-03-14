<?php

namespace App\Http\Controllers\Finance;

use App\Services\VoucherService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function createVoucher(Request $request)
    {
        $params    = $request->only(['voucherNo', 'billAmount', 'date', 'detail']);
        $validator = Validator::make($params, [
            'voucherNo'      => 'required|max:10|unique:voucher',
            'billAmount'     => 'required|integer',
            'date'           => 'required|date_format:Y-m-d',
            'detail.*.summary' => 'required|max:100',
            'detail.*.subject' => 'required|max:100',
            'detail.*.debit'   => 'required|numeric',
            'detail.*.credit'  => 'required|numeric',
        ], [
            'voucherNo.required'      => '凭证号不能为空',
            'voucherNo.max'           => '凭证号不能超过10个字符',
            'voucherNo.unique'        => '凭证号不能重复',
            'billAmount.required'     => '单据数量不能为空',
            'billAmount.integer'      => '单据数量只能是整数',
            'date.required'           => '凭证日期不能为空',
            'date.date_format'        => '凭证日期格式不正确，正确格式为：' . date('Y-m-d'),
            'detail.*.summary.required' => '摘要不能为空',
            'detail.*.summary.max'      => '摘要不能超过100个字符',
            'detail.*.subject.required' => '科目不能为空',
            'detail.*.subject.max'      => '科目不能超过100个字符',
            'detail.*.debit.required'   => '借方金额不能为空',
            'detail.*.debit.numeric'    => '借方金额只能是数字',
            'detail.*.credit.required'  => '贷方金额不能为空',
            'detail.*.credit.numeric'   => '贷方金额只能是数字',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $this->voucherService;
        return $this->success();
    }

    public function editVoucher()
    {

    }
}
