<?php

namespace App\Http\Controllers\Report;

use App\Services\ReportService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AssistController extends Controller
{
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * 核算项目明细账
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssistSubjectList(Request $request)
    {
        $params    = $request->only(['code', 'startPeriod', 'endPeriod', 'page', 'limit', 'auxiliaryTypeId']);
        $validator = Validator::make($params, [
            'auxiliaryTypeId' => 'required|in:1,2,3,4,5,6,7',
            'page'            => 'required|integer|min:1',
            'limit'           => 'required|integer|min:1',
            'startPeriod'     => 'integer|min:1',
            'endPeriod'       => 'integer|min:1',
        ], [
            'page.required'            => '偏移量不能为空',
            'auxiliaryTypeId.required' => '辅助类别不能为空',
            'auxiliaryTypeId.in'       => '辅助类别正确',
            'page.integer'             => '偏移量只能是整数',
            'page.min'                 => '偏移量最小是1',
            'limit.required'           => '每页显示数不能为空',
            'limit.integer'            => '每页显示数只能是整数',
            'limit.min'                => '每页显示数最小是1',
            'startPeriod.integer'      => '开始期间只能是整数',
            'startPeriod.min'          => '开始期间最小是1',
            'endPeriod.integer'        => '结束期间只能是整数',
            'endPeriod.min'            => '结束期间最小是1',
        ]);

        $params['code'] = $params['code'] ?? 0;
        $params['startPeriod'] = $params['startPeriod'] ?? 0;
        $params['endPeriod']   = $params['endPeriod'] ?? 0;
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $list = $this->reportService->getAssistSubjectList($params);
        return $this->success($list);
    }
}
