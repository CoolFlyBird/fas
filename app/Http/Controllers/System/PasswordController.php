<?php

namespace App\Http\Controllers\System;

use App\Models\EmployeeModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function __construct(EmployeeModel $employeeModel)
    {
        $this->employeeModel = $employeeModel;
    }

    /**
     * 修改密码
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editPassword(Request $request)
    {
        $params    = $request->only(['password']);
        $validator = Validator::make($params, [
            'password' => 'required|min:6',
        ], [
            'password.required' => '密码不能为空',
            'password.min'      => '密码最少6位',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2002);
        }

        $res = $this->employeeModel->editPassword(Auth::user()->id, $params['password']);
        if ($res) {
            Auth::logout();
            return $this->success();
        } else {
            return $this->fail('密码修改失败');
        }
    }
}
