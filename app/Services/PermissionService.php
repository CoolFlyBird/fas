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
    public function __construct(PermissionModel $permissionModel, RolePermissionModel $rolePermissionModel)
    {
        $this->permissionModel     = $permissionModel;
        $this->rolePermissionModel = $rolePermissionModel;
    }

    /**
     * 权限列表
     * @author huxinlu
     * @param int $rid 角色ID
     * @param int $accountSetId 账套ID
     * @return array
     */
    public function getPermission(int $rid, int $accountSetId)
    {
        //获取权限ID
        $pidArr = $this->rolePermissionModel->getPermissionId($rid, $accountSetId);

        //权限列表
        $list = $this->permissionModel->getPermissionList($pidArr);

        $data = [];
        foreach ($list as $k => $v) {
            $data[] = get_permission_cn($v['module'], $v['controller']);
        }

        return $data;
    }
}
