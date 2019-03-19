<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

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
}
