<?php

namespace App\Http\Middleware;

use App\Models\PermissionModel;
use App\Models\RolePermissionModel;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    protected function redirectTo($request)
    {

    }

    public function handle($request, \Closure $next, ...$guards)
    {
        $user = Auth::user();
        if (empty($user)) {
            return response()->json([
                'code'    => 2001,
                'message' => '请登录',
                'data'    => (object)[],
            ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        } else {
            $url        = $request->route()->getActionName();
            $url        = substr($url, 0, strpos($url, '@'));
            $url        = explode('\\', $url);
            $module     = lcfirst($url[3]);
            $controller = lcfirst($url[4]);
            if (strpos($controller, 'Controller') !== false) {
                $controller = str_replace('Controller', '', $controller);
            }

            $permissionModel     = new PermissionModel();
            $rolePermissionModel = new RolePermissionModel();

            //获取权限ID
            $pid = $permissionModel->getPid($module, $controller);

            $isExist = $rolePermissionModel->isHasPermission(Auth::user()->rid ?? 0, $pid ?? 0, session('accountSetId') ?? 0);
            if (!$isExist) {
                return response()->json([
                    'code'    => 3001,
                    'message' => '无访问权限',
                    'data'    => (object)[],
                ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            }
        }

        return parent::handle($request, $next, $guards);
    }
}
