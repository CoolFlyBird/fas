<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models\System;

use App\Models\BaseModel;

class RolePermissionModel extends BaseModel
{
    protected $table = 'role_permission';

    /**
     * 该角色都是否账套权限
     * @author huxinlu
     * @param $rid int 角色ID
     * @param $accountSetId int 账套ID
     * @return mixed
     */
    public function isHasAccountSetPermission(int $rid, int $accountSetId)
    {
        return self::where(['rid' => $rid, 'accountSetId' => $accountSetId])->exists();
    }

    /**
     * 是否有访问权限
     * @author huxinlu
     * @param int $rid 角色ID
     * @param int $pid 权限ID
     * @param int $accountSetId 账套ID
     * @return mixed
     */
    public function isHasPermission(int $rid, int $pid, int $accountSetId)
    {
        return self::where(['rid' => $rid, 'pid' => $pid, 'accountSetId' => $accountSetId])->exists();
    }

    /**
     * 获取该角色权限
     * @author huxinlu
     * @param int $rid 角色ID
     * @param int $accountSetId 账套ID
     * @return mixed
     */
    public function getPermissionId(int $rid, int $accountSetId)
    {
        return self::where(['rid' => $rid, 'accountSetId' => $accountSetId])->pluck('id')->toArray();
    }
}
