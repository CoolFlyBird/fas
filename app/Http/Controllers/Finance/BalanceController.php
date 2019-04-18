<?php

namespace App\Http\Controllers\Finance;

use App\Services\SubjectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class BalanceController extends Controller
{
    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    /**
     * 期初余额录入列表
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getList(Request $request)
    {
        $params = $request->only(['type', 'limit']);
        $validator = Validator::make($params, [], [
            'type.required' => '科目类型不能为空不能为空',
            'type.between'  => '该科目类型不存在',
            'limit.integer' => '每页显示数只能是整数',
            'limit.min'     => '每页显示数最小是1',
        ]);
        $validator->sometimes('type', 'required|between:1,6', function ($input) {
            if (isset($input->type) && $input->type != 0) {
                return true;
            }
        });
        $validator->sometimes('limit', 'integer|min:1', function ($input) {
            return isset($input->limit);
        });

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $type = $params['type'] ?? 0;
        $limit = $params['limit'] ?? 20;

        $list = $this->subjectService->getList($type, $limit);

        return $this->success($list);
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
            return $this->fail($validator->errors()->first(), 2002);
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
            return $this->fail($validator->errors()->first(), 2002);
        }

        $params['amount'] = $params['amount'] ?? 0.00;

        $res     = $this->subjectService->editAmount((int)$params['id'], (float)$params['amount']);

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
