<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class StockModel extends BaseModel
{
    protected $table = 'stock';
    public $timestamps = false;

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
     * 添加存货
     * @author huxinlu
     * @param $params
     * @return bool
     */
    public function create($params)
    {
        $params['code'] = str_pad($this->getMaxCode() + 1, 3, '0', STR_PAD_LEFT);
        return $this->add($params);
    }
}
