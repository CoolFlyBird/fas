<?php

namespace App\Http\Controllers\System;

use App\Models\System\DepartmentModel;
use App\Models\System\EmployeeModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * 创建职员
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEmployee(Request $request)
    {
        $params   = $request->only(['username', 'password', 'sex', 'nation', 'birthDate', 'rid', 'education', 'entryDate', 'duty', 'departmentId',
            'nativePlace', 'idCard', 'bankName', 'bankCard']);
        $validate = Validator::make($params, [
            'username'     => 'required|max:10',
            'password'     => 'required',
            'sex'          => 'in:1,2',
            'nation'       => 'max:15',
            'birthDate'    => 'date',
            'rid'          => 'exists:role,id',
            'education'    => 'between:1,8',
            'entryDate'    => 'required|date_format:Y-m-d',
            'duty'         => 'max:20',
            'departmentId' => 'exists:department,id',
            'nativePlace'  => 'max:50',
            'idCard'       => 'regex:/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/',
            'bankName'     => 'max:20',
            'bankCard'     => 'max:20',
        ], [
            'username.required'     => '用户姓名不能为空',
            'username.max'          => '用户姓名不能超过10个字符',
            'password.required'     => '密码不能为空',
            'sex.in'                => '性别类型不正确',
            'nation.max'            => '民族不能超过15个字符',
            'birthDate.date'        => '出生日期格式不正确，正确格式为2019-01-01',
            'rid.exists'            => '该角色不存在',
            'education.between'     => '文化程度类型不正确',
            'entryDate.required'    => '入职日期不能为空',
            'entryDate.date_format' => '入职日期格式不正确，正确格式为2019-01-01',
            'duty.max'              => '职务不能超过15个字符',
            'departmentId.exists'   => '该部门不存在',
            'nativePlace.max'       => '籍贯不能超过50个字符',
            'idCard.regex'          => '身份证号不正确',
            'bankName.max'          => '银行名称不能超过20个字符',
            'bankCard.max'          => '银行卡号不能超过20个字符',
        ]);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first(), 2001);
        }

        $employeeModel = new EmployeeModel();
        $employeeModel->create($params);

        return $this->success();
    }

    /**
     * 编辑职员
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editEmployee(Request $request)
    {
        $params   = $request->only(['id', 'username', 'password', 'sex', 'nation', 'birthDate', 'rid', 'education', 'entryDate', 'duty', 'departmentId',
            'status', 'departureDate', 'nativePlace', 'idCard', 'bankName', 'bankCard', 'isDisable']);
        $validate = Validator::make($params, [
            'id'            => 'required|exists:employee',
            'username'      => 'max:10',
            'sex'           => 'in:1,2',
            'nation'        => 'max:15',
            'birthDate'     => 'date',
            'rid'           => 'exists:role,id',
            'education'     => 'between:1,8',
            'entryDate'     => 'date_format:Y-m-d',
            'status'        => 'in:1,2',
            'departureDate' => 'date_format:Y-m-d',
            'duty'          => 'max:20',
            'departmentId'  => 'exists:department,id',
            'nativePlace'   => 'max:50',
            'idCard'        => 'regex:/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/',
            'bankName'      => 'max:20',
            'bankCard'      => 'max:20',
            'isDisable'     => 'in:0,1'
        ], [
            'id.required'               => '职员ID不能为空',
            'id.exists'                 => '该职员不存在',
            'username.max'              => '用户姓名不能超过10个字符',
            'sex.in'                    => '性别类型不正确',
            'nation.max'                => '民族不能超过15个字符',
            'birthDate.date'            => '出生日期格式不正确，正确格式为2019-01-01',
            'rid.exists'                => '该角色不存在',
            'education.between'         => '文化程度类型不正确',
            'entryDate.date_format'     => '入职日期格式不正确，正确格式为2019-01-01',
            'status.in'                 => '职员状态不能超过15个字符',
            'departureDate.date_format' => '离职日期格式不正确，正确格式为2019-01-01',
            'duty.max'                  => '职务不能超过15个字符',
            'departmentId.exists'       => '该部门不存在',
            'nativePlace.max'           => '籍贯不能超过50个字符',
            'idCard.regex'              => '身份证号不正确',
            'bankName.max'              => '银行名称不能超过20个字符',
            'bankCard.max'              => '银行卡号不能超过20个字符',
            'isDisable.in'              => '是否禁用类型不正确',
        ]);
        if ($validate->fails()) {
            return $this->fail($validate->errors()->first(), 2001);
        }

        $employeeModel = new EmployeeModel();

        //在职员工不能禁用
        if (isset($params['status']) && isset($params['isDisable']) && $params['status'] == $employeeModel::STATUS_ON && $params['isDisable'] == $employeeModel::DISABLED) {
            return $this->fail('在职员工不能禁用', 2001);
        }
        $employeeModel->edit($params);

        return $this->success();
    }

    /**
     * 职员详情
     * @author huxinlu
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeDetail(int $id)
    {
        $employeeModel = new EmployeeModel();
        $detail        = $employeeModel->getDetail($id);

        return $this->success($detail);
    }

    /**
     * 删除职员
     * @author huxinlu
     * @param int $id 职员ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delEmployee(int $id)
    {
        $employeeModel = new EmployeeModel();
        $res           = $employeeModel->del($id);

        return $res ? $this->success() : $this->fail();
    }

    /**
     * 职员列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeList()
    {
        $employeeModel = new EmployeeModel();
        $list          = $employeeModel->getList();

        return $this->success(['data' => $list->items(), 'totalCount' => $list->total()]);
    }

    /**
     * 创建部门
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDepartment(Request $request)
    {
        $params    = $request->only(['name', 'leader', 'phone', 'remark']);
        $validator = Validator::make($params, [
            'name'   => 'required|max:10',
            'leader' => 'max:10',
            'phone'  => 'max:11',
            'remark' => 'max:100',
        ], [
            'name.required' => '部门名称不能为空',
            'name.max'      => '部门名称不能超过10个字符',
            'leader.max'    => '部门主管不能超过10个字符',
            'phone.max'     => '部门电话不能超过10个字符',
            'remark.max'    => '备注不能超过100个字符',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $model = new DepartmentModel();
        $res   = $model->create($params);

        return $res ? $this->success() : $this->fail('部门添加失败');
    }

    /**
     * 编辑部门
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editDepartment(Request $request)
    {
        $params    = $request->only(['id', 'name', 'leader', 'phone', 'remark']);
        $validator = Validator::make($params, [
            'id'     => 'required|exists:department',
            'name'   => 'required|max:10',
            'leader' => 'max:10',
            'phone'  => 'max:11',
            'remark' => 'max:100',
        ], [
            'id.required'   => '部门ID不能为空',
            'id.exists'     => '该部门不存在',
            'name.required' => '部门名称不能为空',
            'name.max'      => '部门名称不能超过10个字符',
            'leader.max'    => '部门主管不能超过10个字符',
            'phone.max'     => '部门电话不能超过10个字符',
            'remark.max'    => '备注不能超过100个字符',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $model = new DepartmentModel();
        $res   = $model->edit($params);

        return $res ? $this->success() : $this->fail('部门编辑失败');
    }

    /**
     * 删除部门
     * @author huxinlu
     * @param int $id 部门ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delDepartment(int $id)
    {
        $model = new DepartmentModel();
        $res   = $model->del($id);

        return $res ? $this->success() : $this->fail();
    }

    /**
     * 部门列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartmentList()
    {
        $model = new DepartmentModel();
        $list  = $model->getList();

        return $this->success($list);
    }
}
