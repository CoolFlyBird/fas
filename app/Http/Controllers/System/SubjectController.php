<?php

namespace App\Http\Controllers\System;

use App\Models\SubjectModel;
use App\Services\SubjectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    public function __construct(SubjectService $subjectService, SubjectModel $subjectModel)
    {
        $this->subjectService = $subjectService;
        $this->subjectModel   = $subjectModel;
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
            'auxiliaryTypeId.exists'   => '该辅助核算类型不存在',
            'amount.numeric'           => '计量单位只能是数值',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
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
            'auxiliaryTypeId' => 'exists:auxiliary_type,id',
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
            'auxiliaryTypeId.exists' => '该辅助核算类型不存在',
            'amount.numeric'         => '计量单位只能是数值',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
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
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->subjectModel->del($id);

        return $res ? $this->success() : $this->fail('删除科目失败');
    }

    /**
     * 科目列表
     * @author huxinlu
     * @param int $type 科目类型：1-资产，2-负债，3-共同，4-权益，5-成本，6-损益
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(int $type = 0)
    {
        if ($type != 0) {
            $validator = Validator::make(['type' => $type], [
                'type' => 'required|between:1,6',
            ], [
                'type.required' => '科目类型不能为空不能为空',
                'type.between'  => '该科目类型不存在',
            ]);

            if ($validator->fails()) {
                return $this->fail($validator->errors()->first(), 2001);
            }
        }

        $list = $this->subjectService->getList($type);

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
            return $this->fail($validator->errors()->first(), 2001);
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
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->subjectService->start($id);

        return $res['res'] ? $this->success() : $this->fail($res['msg']);
    }
}
