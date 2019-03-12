<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models\System;

use App\Models\BaseModel;

class DepartmentModel extends BaseModel
{
    protected $table = 'department';

    /**
     * 创建部门
     * @author huxinlu
     * @param array $params
     * @return bool
     */
    public function create(array $params)
    {
        $params['code'] = str_pad($this->getMaxCode() + 1, 3, "0", STR_PAD_LEFT);
        return $this->add($params);
    }

    /**
     * 获取最大编码
     * @author huxinlu
     * @return mixed
     */
    public function getMaxCode()
    {
        return $this->query()->max('code');
    }

    /**
     * 部门列表
     * @author huxinlu
     * @return array
     */
    public function getList()
    {
        return $this->query()->get(['id', 'code', 'name'])->toArray();
    }
}
