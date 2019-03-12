<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models\System;

use App\Models\BaseModel;

class ClientModel extends BaseModel
{
    protected $table = 'client';

    /**
     * 创建客户
     * @author huxinlu
     * @param $params
     * @return bool
     */
    public function create($params)
    {
        $params['code'] = str_pad($this->getMaxCode() + 1, 3, '0', STR_PAD_LEFT);
        return $this->add($params);
    }

    /**
     * 最大编码
     * @author huxinlu
     * @return mixed
     */
    public function getMaxCode()
    {
        return $this->query()->max('code');
    }

    /**
     * 客户列表
     * @author huxinlu
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getList()
    {
        return $this->query()->get(['id', 'code', 'name']);
    }

    /**
     * 客户详情
     * @author huxinlu
     * @param int $id 客户ID
     * @return mixed
     */
    public function getDetail(int $id)
    {
        return self::where('id', $id)->get(['name']);
    }
}
