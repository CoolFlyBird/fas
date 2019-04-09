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
    const AUXILIARY_CLIENT = 1;//辅助核算-客户
    const AUXILIARY_SUPPLIER = 2;//辅助核算-供应商
    const AUXILIARY_EMPLOYEE = 3;//辅助核算-职员
    const AUXILIARY_PROJECT = 4;//辅助核算-项目
    const AUXILIARY_DEPARTMENT = 5;//辅助核算-部门
    const AUXILIARY_STOCK = 6;//辅助核算-存货
    const AUXILIARY_CASH = 7;//辅助核算-现金流量核算

    public function voucherDetail()
    {
        return $this->hasMany('App\Models\VoucherDetailModel', 'subjectId', 'id');
    }

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

    /**
     * 根据科目ID获取科目方向
     * @author huxinlu
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getDirectionById($subjectId)
    {
        return self::where('id', $subjectId)->value('direction');
    }

    /**
     * 含有凭证明细的科目列表
     * @author huxinlu
     * @param $params
     * @return mixed
     */
    public function getSubjectList($params)
    {
        return $this->query()
            ->when(!empty($params['code']), function ($query) use ($params) {
                $query->when(is_array($params['code']), function($whereQuery) use ($params) {
                    return $whereQuery->whereIn('code', $params['code']);
                }, function ($whereQuery) use ($params) {
                    return $whereQuery->where('code', $params['code']);
                });
            })
            ->where('status', 1)
            ->has('voucherDetail', '>=', 1)
            ->select('id', 'code', 'name', 'direction')
            ->paginate($params['limit'])
            ->toArray();
    }

    /**
     * 会计科目
     * @author huxinlu
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getVoucherSubjectList()
    {
        return $this->query()->limit(2)->get(['id', 'code', 'name', 'auxiliaryTypeId']);
    }

    /**
     * 是否存在辅助核算
     * @author huxinlu
     * @param $subjectId int 科目ID
     * @param $auxiliaryTypeId int 辅助核算类型ID
     * @return mixed
     */
    public function isExistAssist($subjectId, $auxiliaryTypeId)
    {
        return self::where(['id' => $subjectId, 'auxiliaryTypeId' => $auxiliaryTypeId])->exists();
    }

    /**
     * 科目期初余额
     * @author huxinlu
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getInitialBalance($subjectId)
    {
        return self::where('id', $subjectId)->value('balance');
    }
}
