<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

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
    const DIRECTION_DEBIT = 1;//借
    const DIRECTION_CREDIT = 2;//贷

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

    /**
     * 是否存在已启用的科目
     * @author huxinlu
     * @param int $id 科目ID
     * @return mixed
     */
    public function isExistStartSubject(int $id)
    {
        return self::where(['id' => $id, 'status' => self::STATUS_START])->exists();
    }

    /**
     * 所有借方金额
     * @author huxinlu
     * @return mixed
     */
    public function getAllDebitBalance()
    {
        return self::where('direction', self::DIRECTION_DEBIT)->sum('balance');
    }

    /**
     * 所有贷方金额
     * @author huxinlu
     * @return mixed
     */
    public function getAllCreditBalance()
    {
        return self::where('direction', self::DIRECTION_CREDIT)->sum('balance');
    }

    /**
     * 获取当年借方金额
     * @author huxinlu
     * @return mixed
     */
    public function getCurrentYearDebitBalance()
    {
        return self::where('direction', self::DIRECTION_DEBIT)->whereYear('createTime', date('Y'))->sum('balance');
    }

    /**
     * 获取当年贷方金额
     * @author huxinlu
     * @return mixed
     */
    public function getCurrentYearCreditBalance()
    {
        return self::where('direction', self::DIRECTION_CREDIT)->whereYear('createTime', date('Y'))->sum('balance');
    }

    /**
     * 根据编码获取科目方向
     * @author huxinlu
     * @param $code string  科目编码
     * @return mixed
     */
    public function getDirectionByCode($code)
    {
        return self::where('code', $code)->value('direction');
    }
}
