<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class CashFlowTypeModel extends BaseModel
{
    protected $table = 'cash_flow_type';

    /**
     * 现金流量核算名称
     * @author huxinlu
     * @param $id int 现金流量核算ID
     * @return mixed
     */
    public function getName($id)
    {
        return self::where('id', $id)->value('name');
    }
}
