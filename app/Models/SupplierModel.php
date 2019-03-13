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
     * 供应商列表
     * @author huxinlu
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getList()
    {
        return $this->query()->get(['id', 'code', 'name']);
    }

    /**
     * 供应商详情
     * @author huxinlu
     * @param int $id 供应商ID
     * @return mixed
     */
    public function getDetail(int $id)
    {
        return self::where('id', $id)->get(['name']);
    }
}
