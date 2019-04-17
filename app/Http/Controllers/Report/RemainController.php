<?php

namespace App\Http\Controllers\Report;

use App\Services\ReportService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RemainController extends Controller
{
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * 科目余额表
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubjectBalanceList(Request $request)
    {
        $params    = $request->only(['page', 'limit', 'filter', 'grade', 'isDisplay', 'startPeriod', 'endPeriod']);
        $validator = Validator::make($params, [
            'page'        => 'required|integer|min:1',
            'limit'       => 'required|integer|min:1',
            'filter'      => 'max:15',
            'grade'       => 'between:1,4',
            'isDisplay'   => 'in:0,1',
//            'startPeriod' => 'integer|min:1|max:' . date('m'),
//            'endPeriod'   => 'integer|min:1|max:' . date('m'),
        ], [
            'page.required'       => '偏移量不能为空',
            'page.integer'        => '偏移量只能是整数',
            'page.min'            => '偏移量不能小于1',
            'limit.required'      => '每页显示数不能为空',
            'limit.integer'       => '每页显示数只能是整数',
            'limit.min'           => '每页显示数不能小于1',
            'filter.max'          => '搜索词不能超过15个字符',
            'grade.between'       => '科目等级不存在',
            'isDisplay.in'        => '是否包含余额和本期发生额均为0的科目类型不正确',
            'startPeriod.integer' => '会计开始期间只能是整数',
            'startPeriod.min'     => '会计开始期间最小是1',
//            'startPeriod.max'     => '会计开始期间最大是' . date('m'),
            'endPeriod.integer'   => '会计结束期间只能是整数',
            'endPeriod.min'       => '会计结束期间最小是1',
//            'endPeriod.max'       => '会计结束期间最大是' . date('m'),
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $params['grade']       = $params['grade'] ?? 1;
        $params['isDisplay']   = $params['isDisplay'] ?? 1;
        $params['startPeriod'] = $params['startPeriod'] ?? 0;
        $params['endPeriod']   = $params['endPeriod'] ?? 0;
        $params['filter']      = $params['filter'] ?? '';

        $list = $this->reportService->getSubjectBalanceList($params);
        return $this->success($list);
    }
}
