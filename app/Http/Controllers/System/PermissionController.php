<?php

namespace App\Http\Controllers\System;

use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * 权限列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $service = new PermissionService();
        $list = $service->getPermission(Auth::user()->rid, session('accountSetId'));

        return $this->success($list);
    }
}
