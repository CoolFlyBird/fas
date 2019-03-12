<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models\System;

use App\Models\BaseModel;

class SubjectModel extends BaseModel
{
    protected $table = 'subject';

    const TYPE_PROPERTY = 1;//资产
    const TYPE_DEBT = 2;//负债
    const TYPE_COMMON = 3;//共同
    const TYPE_EQUITY = 4;//权益
    const TYPE_COST = 5;//成本
    const TYPE_PROFIT = 6;//损益
    const STATUS_NOT_START = 0;//未启用
    const STATUS_START = 1;//启用

    /**
     * 统一级别最大编码
     * @author huxinlu
     * @param $parentSubjectCode
     * @param $type
     * @return mixed
     */
    public function getMaxCode($parentSubjectCode, $type)
    {
        return self::where(['parentSubjectCode' => $parentSubjectCode, 'type' => $type])->max('code');
    }
}
