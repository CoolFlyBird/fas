<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\System\RolePermissionModel;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    /**
     * 登录
     * @author huxinlu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $params = $request->only('username', 'password', 'isRemember', 'id');
        $validator   = Validator::make($params, [
            'username' => 'required|max:45',
            'password' => 'required',
            'isRemember' => 'required|in:0,1',
            'id' => 'required|exists:account_set'
        ], [
            'username.required' => '用户昵称不能为空',
            'username.max'     => '用户昵称字数过长，不能超过45个字符',
            'password.required' => '密码不能为空',
            'isRemember.required' => '是否保存密码不能为空',
            'isRemember.in' => '是否保存密码类型有误',
            'id.required' => '请选择账套',
            'id.exists' => '账套不存在',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 2001);
        }

        $validate = Auth::validate(['username' => $params['username'], 'password' => $params['password']]);
        if ($validate) {
            Auth::login(Auth::getLastAttempted());
            $rid = Auth::user()->rid ?? 0;//角色ID

            $rolePermissionModel = new RolePermissionModel();
            $login = $rolePermissionModel->isHasAccountSetPermission($rid, (int)$params['id']);
            if ($login) {
                $request->session()->put('accountSetId', $params['id']);
                Auth::login(Auth::getLastAttempted());

                return $this->success();
            } else {
                Auth::logout();
                return $this->fail('无该账套权限');
            }
        } else {
            return $this->fail('用户名密码有误，请重新输入');
        }
    }
}
