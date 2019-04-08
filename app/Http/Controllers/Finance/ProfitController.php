<?php

namespace App\Http\Controllers\Finance;

use App\Services\FinanceService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProfitController extends Controller
{
    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * 结转损益
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateProfit(Request $request)
    {
        $params = $request->only(['date', 'summary', 'proofWordId']);
        $validator = Validator::make($params, [
            'date' => 'required|date_format:Y-m-d',
            'summary' => 'required|max:100',
            'proofWordId' => 'required|exists:proof_word,id'
        ], [
            'date.required' => '凭证日期不能为空',
            'date.date_format' => '凭证日期格式不正确',
            'summary.required' => '摘要不能为空',
            'summary.max' => '摘要不能超过100个字符',
            'proofWordId.required' => '凭证字不能为空',
            'proofWordId.exists' => '凭证字不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->financeService->calculateProfit($params);
        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }
}
