<?php

namespace App\Http\Controllers\System;

use App\Models\System\ProofWordModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProofWordController extends Controller
{
    /**
     * 添加凭证字
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $params = $request->only(['name']);
        $validator = Validator::make($params, [
            'name' => 'required|max:10|unique:proof_word'
        ], [
            'name.required' => '凭证字不能为空',
            'name.max' => '凭证字不能超过10个字符',
            'name.unique' => '凭证字不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $model = new ProofWordModel();
        $res = $model->add($params);

        return $res ? $this->success() : $this->fail('添加失败');
    }

    /**
     * 编辑凭证字
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $params = $request->only(['name', 'id']);
        $validator = Validator::make($params, [
            'id' => 'required|exists:proof_word',
            'name' => 'required|max:10|unique:proof_word'
        ], [
            'id.required' => '凭证字不能为空',
            'id.exists' => '该凭证字不存在',
            'name.required' => '凭证字不能为空',
            'name.max' => '凭证字不能超过10个字符',
            'name.unique' => '凭证字不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $model = new ProofWordModel();
        $res = $model->edit($params);

        return $res ? $this->success() : $this->fail('编辑失败');
    }

    /**
     * 删除凭证字
     * @author huxinlu
     * @param int $id 凭证字ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:proof_word'
        ], [
            'id.required' => '凭证字不能为空',
            'id.exists' => '该凭证字不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $model = new ProofWordModel();
        $res = $model->del($id);

        return $res ? $this->success() : $this->fail('编辑失败');
    }

    /**
     * 凭证字列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $model = new ProofWordModel();
        $list = $model->getList();

        return $this->success($list);
    }
}
