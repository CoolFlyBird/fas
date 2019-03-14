<?php

namespace App\Http\Controllers\System;

use App\Models\ClientModel;
use App\Models\ProjectModel;
use App\Models\StockModel;
use App\Models\SubjectModel;
use App\Models\SupplierModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AssistController extends Controller
{
    public function __construct(ClientModel $clientModel, ProjectModel $projectModel, StockModel $stockModel,
                                SubjectModel $subjectModel, SupplierModel $supplierModel)
    {
        $this->clientModel  = $clientModel;
        $this->projectModel = $projectModel;
        $this->stockModel   = $stockModel;
        $this->subjectModel = $subjectModel;
    }

    /**
     * 创建客户
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createClient(Request $request)
    {
        $params    = $request->only(['name']);
        $validator = Validator::make($params, [
            'name' => 'required|max:30|unique:client'
        ], [
            'name.required' => '客户名称不能为空',
            'name.max'      => '客户名称不能超过30个字符',
            'name.unique'   => '客户名称不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->clientModel->create($params);

        return $res ? $this->success() : $this->fail('客户添加失败');
    }

    /**
     * 编辑客户
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editClient(Request $request)
    {
        $params    = $request->only(['id', 'name']);
        $validator = Validator::make($params, [
            'id'   => 'required|exists:client',
            'name' => 'required|max:30|unique:client'
        ], [
            'id.required'   => '客户ID不能为空',
            'id.exists'     => '该客户不存在',
            'name.required' => '客户名称不能为空',
            'name.max'      => '客户名称不能超过30个字符',
            'name.unique'   => '客户名称不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->clientModel->edit($params);

        return $res ? $this->success() : $this->fail('客户编辑失败');
    }

    /**
     * 删除客户
     * @author huxinlu
     * @param int $id 客户ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delClient(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:client',
        ], [
            'id.required' => '客户ID不能为空',
            'id.exists'   => '该客户不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->clientModel->del($id);

        return $res ? $this->success() : $this->fail('客户删除失败');
    }

    /**
     * 客户列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientList()
    {
        $list = $this->clientModel->getList();

        return $this->success($list);
    }

    /**
     * 客户详情
     * @author huxinlu
     * @param int $id 客户ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientDetail(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:client',
        ], [
            'id.required' => '客户ID不能为空',
            'id.exists'   => '该客户不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $detail = $this->clientModel->getDetail($id);

        return $this->success($detail);
    }

    /**
     * 创建供应商
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSupplier(Request $request)
    {
        $params    = $request->only(['name', 'address', 'contact', 'phone', 'email', 'remark']);
        $validator = Validator::make($params, [
            'name'    => 'required|max:30|unique:supplier',
            'address' => 'max:100',
            'contact' => 'max:10',
            'phone'   => 'max:11',
            'email'   => 'email|max:20',
            'remark'  => 'max:100',
        ], [
            'name.required' => '供应商名称不能为空',
            'name.max'      => '供应商名称不能超过30个字符',
            'name.unique'   => '供应商名称不能重复',
            'address.max'   => '地址不能超过100个字符',
            'contact.max'   => '联系人不能超过10个字符',
            'phone.max'     => '电话不能超过11个字符',
            'email.email'   => '邮箱格式不正确',
            'email.max'     => '邮箱不能超过20个字符',
            'remark.max'    => '备注信息不能超过100个字符',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->subjectModel->create($params);

        return $res ? $this->success() : $this->fail('供应商添加失败');
    }

    /**
     * 编辑供应商
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editSupplier(Request $request)
    {
        $params    = $request->only(['id', 'name', 'address', 'contact', 'phone', 'email', 'remark']);
        $validator = Validator::make($params, [
            'id'      => 'required|exists:supplier',
            'name'    => 'required|max:30|unique:supplier',
            'address' => 'max:100',
            'contact' => 'max:10',
            'phone'   => 'max:11',
            'email'   => 'email|max:20',
            'remark'  => 'max:100',
        ], [
            'id.required'   => '供应商ID不能为空',
            'id.exists'     => '该供应商不存在',
            'name.required' => '供应商名称不能为空',
            'name.max'      => '供应商名称不能超过30个字符',
            'name.unique'   => '供应商名称不能重复',
            'address.max'   => '地址不能超过100个字符',
            'contact.max'   => '联系人不能超过10个字符',
            'phone.max'     => '电话不能超过11个字符',
            'email.email'   => '邮箱格式不正确',
            'email.max'     => '邮箱不能超过20个字符',
            'remark.max'    => '备注信息不能超过100个字符',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->subjectModel->edit($params);

        return $res ? $this->success() : $this->fail('供应商编辑失败');
    }

    /**
     * 删除供应商
     * @author huxinlu
     * @param int $id 客户ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delSupplier(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:client',
        ], [
            'id.required' => '供应商ID不能为空',
            'id.exists'   => '该供应商不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->subjectModel->del($id);

        return $res ? $this->success() : $this->fail('供应商删除失败');
    }

    /**
     * 供应商列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupplierList()
    {
        $list = $this->subjectModel->getList();

        return $this->success($list);
    }

    /**
     * 供应商详情
     * @author huxinlu
     * @param int $id 客户ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupplierDetail(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:client',
        ], [
            'id.required' => '供应商ID不能为空',
            'id.exists'   => '该供应商不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $detail = $this->subjectModel->getDetail($id);

        return $this->success($detail);
    }

    /**
     * 创建项目
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProject(Request $request)
    {
        $params    = $request->only(['name']);
        $validator = Validator::make($params, [
            'name' => 'required|max:20|unique:project'
        ], [
            'name.required' => '项目名称不能为空',
            'name.max'      => '项目名称不能超过20个字符',
            'name.unique'   => '项目名称不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->projectModel->create($params);

        return $res ? $this->success() : $this->fail('项目添加失败');
    }

    /**
     * 编辑项目
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editProject(Request $request)
    {
        $params    = $request->only(['id', 'name']);
        $validator = Validator::make($params, [
            'id'   => 'required|exists:project',
            'name' => 'required|max:20|unique:project'
        ], [
            'id.required'   => '项目ID不能为空',
            'id.exists'     => '该项目不存在',
            'name.required' => '项目名称不能为空',
            'name.max'      => '项目名称不能超过20个字符',
            'name.unique'   => '项目名称不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->projectModel->edit($params);

        return $res ? $this->success() : $this->fail('项目编辑失败');
    }

    /**
     * 删除项目
     * @author huxinlu
     * @param int $id 客户ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delProject(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:project',
        ], [
            'id.required' => '项目ID不能为空',
            'id.exists'   => '该项目不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->projectModel->del($id);

        return $res ? $this->success() : $this->fail('删除项目失败');
    }

    /**
     * 项目列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectList()
    {
        $list = $this->projectModel->getList();

        return $this->success($list);
    }

    /**
     * 项目详情
     * @author huxinlu
     * @param int $id 项目ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectDetail(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:project',
        ], [
            'id.required' => '项目ID不能为空',
            'id.exists'   => '该项目不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $detail = $this->projectModel->getDetail($id);

        return $this->success($detail);
    }

    /**
     * 创建存货
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createStock(Request $request)
    {
        $params    = $request->only(['name']);
        $validator = Validator::make($params, [
            'name' => 'required|max:20|unique:stock'
        ], [
            'name.required' => '存货名称不能为空',
            'name.max'      => '存货名称不能超过20个字符',
            'name.unique'   => '存货名称不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->stockModel->create($params);

        return $res ? $this->success() : $this->fail('存货添加失败');
    }

    /**
     * 编辑存货
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editStock(Request $request)
    {
        $params    = $request->only(['id', 'name']);
        $validator = Validator::make($params, [
            'id'   => 'required|exists:stock',
            'name' => 'required|max:20|unique:stock'
        ], [
            'id.required'   => '存货ID不能为空',
            'id.exists'     => '该存货不存在',
            'name.required' => '存货名称不能为空',
            'name.max'      => '存货名称不能超过20个字符',
            'name.unique'   => '存货名称不能重复',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->stockModel->edit($params);

        return $res ? $this->success() : $this->fail('存货编辑失败');
    }

    /**
     * 删除存货
     * @author huxinlu
     * @param int $id 客户ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delStock(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:stock',
        ], [
            'id.required' => '存货ID不能为空',
            'id.exists'   => '该存货不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $res = $this->stockModel->del($id);

        return $res ? $this->success() : $this->fail('删除存货失败');
    }

    /**
     * 存货列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStockList()
    {
        $list = $this->stockModel->getList();

        return $this->success($list);
    }

    /**
     * 存货详情
     * @author huxinlu
     * @param int $id 项目ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStockDetail(int $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:stock',
        ], [
            'id.required' => '存货ID不能为空',
            'id.exists'   => '该存货不存在',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $detail = $this->stockModel->getDetail($id);

        return $this->success($detail);
    }
}
