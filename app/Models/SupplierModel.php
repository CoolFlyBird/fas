<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class SupplierModel extends BaseModel
{
    protected $table = 'supplier';

    /**
     * 添加供应商
     * @author huxinlu
     * @param array $params
     * @return bool
     */
    public function create(array $params)
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
     * 供应商名称
     * @author huxinlu
     * @param $id int 供应商ID
     * @return mixed
     */
    public function getName($id)
    {
        return self::where('id', $id)->value('name');
    }
}
