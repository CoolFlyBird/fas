<?php

namespace App\Http\Controllers\System;

use App\Models\AuxiliaryTypeModel;
use App\Models\CashFlowTypeModel;
use App\Models\SubjectBalanceModel;
use App\Models\SubjectModel;
use App\Services\SubjectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    public function __construct(SubjectService $subjectService, SubjectModel $subjectModel, AuxiliaryTypeModel $auxiliaryTypeModel,
                                SubjectBalanceModel $subjectBalanceModel, CashFlowTypeModel $cashFlowTypeModel)
    {
        $this->subjectService      = $subjectService;
        $this->subjectModel        = $subjectModel;
        $this->auxiliaryTypeModel  = $auxiliaryTypeModel;
        $this->subjectBalanceModel = $subjectBalanceModel;
        $this->cashFlowTypeModel   = $cashFlowTypeModel;
    }

    /**
     * 添加科目
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $params    = $request->only(['name', 'parentSubjectCode', 'classes', 'type', 'direction', 'auxiliaryTypeId', 'amount']);
        $validator = Validator::make($params, [
            'name'              => 'required|max:45|unique:subject',
            'parentSubjectCode' => 'exists:subject,code',
            'classes'           => 'required|max:20',
            'type'              => 'required|between:1,6',
            'direction'         => 'required|in:1,2',
            'auxiliaryTypeId'   => 'exists:auxiliary_type,id',
            'amount'            => 'numeric'
        ], [
            'name.required'            => '科目名称不能为空',
            'name.max'                 => '科目名称不能超过45个字符',
            'name.unique'              => '科目名称不能重复',
            'parentSubjectCode.exists' => '上级科目不存在',
            'classes.required'         => '科目类别不能为空',
            'classes.max'              => '科目类别不能超过20个字符',
            'type.required'            => '科目类型不能为空',
            'type.between'             => '科目类型不正确',
            'direction.required'       => '余额方向不能为空',
            'direction.in'             => '余额方向类型不正确',
//            'auxiliaryTypeId.exists'   => '该辅助核算类型不存在',
            'amount.numeric'           => '计量单位只能是数值',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $params['parentSubjectCode'] = $params['parentSubjectCode'] ?? '';

        $res = $this->subjectService->create($params);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 编辑科目
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $params    = $request->only(['id', 'name', 'classes', 'direction', 'auxiliaryTypeId', 'amount']);
        $validator = Validator::make($params, [
            'id'              => 'required|exists:subject',
            'name'            => 'required|max:45',
            'classes'         => 'required|max:20',
            'direction'       => 'required|in:1,2',
//            'auxiliaryTypeId' => 'exists:auxiliary_type,id',
            'amount'          => 'numeric'
        ], [
            'id.required'            => '科目ID不能为空',
            'id.exists'              => '该科目不存在',
            'name.required'          => '科目名称不能为空',
            'name.max'               => '科目名称不能超过45个字符',
            'classes.required'       => '科目类别不能为空',
            'classes.max'            => '科目类别不能超过20个字符',
            'direction.required'     => '余额方向不能为空',
            'direction.in'           => '余额方向类型不正确',
//            'auxiliaryTypeId.exists' => '该辅助核算类型不存在',
            'amount.numeric'         => '计量单位只能是数值',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->subjectModel->edit($params);

        return $res ? $this->success() : $this->fail('编辑科目失败');
    }

    /**
     * 删除科目
     * @author huxinlu
     * @param int $id 科目ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:subject',
        ], [
            'id.required' => '科目ID不能为空',
            'id.exists'   => '该科目不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->subjectModel->del($id);

        return $res ? $this->success() : $this->fail('删除科目失败');
    }

    /**
     * 科目列表
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        $params    = $request->only(['type', 'limit']);
        $validator = Validator::make($params, [
            'type' => 'required|between:1,6'
        ], [
            'type.required' => '科目类型不能为空不能为空',
            'type.between'  => '该科目类型不存在',
            'limit.integer' => '每页显示数只能是整数',
            'limit.min'     => '每页显示数最小是1',
        ]);
        $validator->sometimes('limit', 'integer|min:1', function ($input) {
            return isset($input->limit);
        });

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $type  = $params['type'] ?? 0;
        $limit = $params['limit'] ?? 20;

        $list = $this->subjectService->getList($type, $limit);

        return $this->success($list);
    }

    /**
     * 科目详情
     * @author huxinlu
     * @param int $id 科目ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:subject',
        ], [
            'id.required' => '科目ID不能为空',
            'id.exists'   => '该科目不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $detail = $this->subjectModel->getDetail($id);

        return $this->success($detail);
    }

    /**
     * 启用科目
     * @author huxinlu
     * @param int $id 科目ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:subject',
        ], [
            'id.required' => '科目ID不能为空',
            'id.exists'   => '该科目不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->subjectService->start($id);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }

    /**
     * 辅助核算列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuxiliaryList()
    {
        $list = $this->auxiliaryTypeModel->getList();
        return $this->success($list);
    }

    /**
     * 科目搜索列表
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSearchList(Request $request)
    {
        $params = $request->only(['filter']);
        $filter = $params['filter'] ?? '';
        $list   = $this->subjectBalanceModel->getSearchList($filter);

        return $this->success($list);
    }

    /**
     * 会计科目列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVoucherSubjectList()
    {
        $list = $this->subjectModel->getVoucherSubjectList();

        return $this->success($list);
    }

    /**
     * 现金流量核算类型列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCashFlowTypeList()
    {
        $list = $this->cashFlowTypeModel->getList();

        return $this->success($list);
    }
}
