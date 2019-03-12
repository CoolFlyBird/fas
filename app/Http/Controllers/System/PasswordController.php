<?php

namespace App\Http\Controllers\System;

use App\Models\System\EmployeeModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    /**
     * 修改密码
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editPassword(Request $request)
    {
        $params    = $request->only(['oldPassword', 'newPassword', 'confirmPassword']);
        $validator = Validator::make($params, [
            'oldPassword'     => 'required',
            'newPassword'     => 'required|different:oldPassword',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'oldPassword.required'     => '原密码不能为空',
            'newPassword.required'     => '新密码不能为空',
            'newPassword.different'    => '新密码不能和原密码相同',
            'confirmPassword.required' => '请输入确认密码',
            'confirmPassword.same'     => '密码输入不一致',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $employeeModel = new EmployeeModel();
        $res           = $employeeModel->editPassword(Auth::user()->id, $params['confirmPassword']);
        if ($res) {
            Auth::logout();
            return $this->success();
        } else {
            return $this->fail('密码修改失败');
        }
    }
}
