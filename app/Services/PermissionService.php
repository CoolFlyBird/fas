<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Services;

use App\Models\PermissionModel;
use App\Models\RolePermissionModel;

class PermissionService
{
    /**
     * 权限列表
     * @author huxinlu
     * @param int $rid 角色ID
     * @param int $accountSetId 账套ID
     * @return array
     */
    public function getPermission(int $rid, int $accountSetId)
    {
        $rolePermissionModel = new RolePermissionModel();
        $permissionModel = new PermissionModel();

        //获取权限ID
        $pidArr = $rolePermissionModel->getPermissionId($rid, $accountSetId);

        //权限列表
        $list = $permissionModel->getPermissionList($pidArr);

        $data = [];
        foreach ($list as $k => $v) {
            $data[] = get_permission_cn($v['module'], $v['controller']);
        }

        return $data;
    }
}
