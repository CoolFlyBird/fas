<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2019/3/7
 * Time: 16:15
 */
/**
 * 获取访问权限中文名称
 * @author huxinlu
 * @param $module string 模块名称
 * @param $controller string 控制器名称
 * @return array
 */
function get_permission_cn($module, $controller)
{
    $permission = config('permission');
    $moduleCn = $permission[$module]['name'];
    $controllerCn = $permission[$module]['children'][$controller];

    return ['module' => $moduleCn, 'controller' => $controllerCn];
}