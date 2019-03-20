<?php

namespace App\Http\Controllers\Finance;

use App\Services\SubjectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class InitialBalanceController extends Controller
{
    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    /**
     * 期初余额录入列表
     * @author huxinlu
     * @param int $type 科目类型：0-全部，1-资产，2-负债，3-共同，4-权益，5-成本，6-损益
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getList(int $type = 0)
    {
        return Redirect::route('subjectList', $type);
    }

    /**
     * 编辑期初余额
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editInitialBalance(Request $request)
    {
        $params    = $request->only(['id', 'initialBalance']);
        $validator = Validator::make($params, [
            'id'             => 'required|exists:subject',
            'initialBalance' => 'numeric'
        ], [
            'id.required'            => '科目ID不能为空',
            'id.exists'              => '该科目不存在',
            'initialBalance.numeric' => '期初余额只能是数字',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $params['initialBalance'] = $params['initialBalance'] ?? 0.00;

        $res     = $this->subjectService->editInitialBalance((int)$params['id'], (float)$params['initialBalance']);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 编辑数量
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editAmount(Request $request)
    {
        $params    = $request->only(['id', 'amount']);
        $validator = Validator::make($params, [
            'id'     => 'required|exists:subject',
            'amount' => 'numeric'
        ], [
            'id.required'    => '科目ID不能为空',
            'id.exists'      => '该科目不存在',
            'amount.numeric' => '数量只能是数字',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $params['amount'] = $params['amount'] ?? 0.00;

        $res     = $this->subjectService->editAmount((int)$params['id'], (float)$params['initialBalance']);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 试算平衡
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate()
    {
        $list    = $this->subjectService->calculateBalance();

        return $this->success($list);
    }
}
