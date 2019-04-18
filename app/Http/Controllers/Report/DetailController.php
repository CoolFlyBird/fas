<?php

namespace App\Http\Controllers\Report;

use App\Services\ReportService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DetailController extends Controller
{
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * 明细账
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalanceDetailList(Request $request)
    {
        $params    = $request->only(['subjectId', 'startPeriod', 'endPeriod', 'page', 'limit']);
        $validator = Validator::make($params, [
            'subjectId'   => 'required|exists:subject,id',
            'page'        => 'required|integer|min:1',
            'limit'       => 'required|integer|min:1',
            'startPeriod' => 'integer|min:1',
            'endPeriod'   => 'integer|min:1',
        ], [
            'subjectId.required'  => '科目ID不能为空',
            'subjectId.exists'    => '科目不存在',
            'page.required'       => '偏移量不能为空',
            'page.integer'        => '偏移量只能是整数',
            'page.min'            => '偏移量最小是1',
            'limit.required'      => '每页显示数不能为空',
            'limit.integer'       => '每页显示数只能是整数',
            'limit.min'           => '每页显示数最小是1',
            'startPeriod.integer' => '开始期间只能是整数',
            'startPeriod.min'     => '开始期间最小是1',
            'endPeriod.integer'   => '结束期间只能是整数',
            'endPeriod.min'       => '结束期间最小是1',
        ]);

        $params['startPeriod'] = $params['startPeriod'] ?? 0;
        $params['endPeriod']   = $params['endPeriod'] ?? 0;
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $list = $this->reportService->getBalanceDetailList($params);
        if(empty($list)){
            $list = null;
        }
        return $this->success($list);
    }
}
