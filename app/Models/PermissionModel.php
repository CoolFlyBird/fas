<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class PermissionModel extends BaseModel
{
    protected $table = 'permission';

    /**
     * 获取权限ID
     * @author huxinlu
     * @param $module string 模块名
     * @param $controller string 控制器名
     * @return mixed
     */
    public function getPid(string $module, string $controller)
    {
        return self::where(['module' => $module, 'controller' => $controller])->value('id', 0);
    }

    /**
     * 获取权限列表
     * @author huxinlu
     * @param array $pidArr 权限ID
     * @return mixed
     */
    public function getPermissionList(array $pidArr)
    {
        return self::whereIn('id', $pidArr)->get();
    }
}
