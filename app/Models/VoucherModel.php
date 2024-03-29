<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class VoucherModel extends BaseModel
{
    protected $table = 'voucher';

    const STATUS_UNCHECKED = 0;//未审核
    const STATUS_PASS = 1;//审核通过
    const DIRECTION_DEBIT = 1;//借
    const DIRECTION_CREDIT = 2;//贷

    public function voucherDetails()
    {
        return $this->hasMany('App\Models\VoucherDetailModel', 'voucherId', 'id');
    }

    public function proofWord()
    {
        return $this->hasOne('App\Models\ProofWordModel', 'id', 'proofWordId');
    }

    /**
     * 是否存在相同的凭证号
     * @author huxinlu
     * @param int $proofWordId 凭证字ID
     * @param int $voucherNo 凭证数字
     * @return mixed
     */
    public function isExistVoucher(int $proofWordId, int $voucherNo)
    {
        return self::where(['proofWordId' => $proofWordId, 'voucherNo' => $voucherNo])->exists();
    }

    /**
     * 是否存在除了自身之外相同的凭证号
     * @author huxinlu
     * @param int $id 凭证ID
     * @param int $proofWordId 凭证字ID
     * @param int $voucherNo 凭证数字
     * @return mixed
     */
    public function isExistVoucherExceptSelf(int $id, int $proofWordId, int $voucherNo)
    {
        return self::where(['proofWordId' => $proofWordId, 'voucherNo' => $voucherNo, 'id' => ['<>', $id]])->exists();
    }

    /**
     * 审核通过
     * @author huxinlu
     * @param int $id 凭证ID
     * @param string $auditor 审核人
     * @return mixed
     */
    public function editStatusPass(int $id, string $auditor)
    {
        return self::where(['id' => $id, 'status' => self::STATUS_UNCHECKED])
            ->update(['status' => self::STATUS_PASS, 'auditor' => $auditor, 'auditDate' => date('Y-m-d')]);
    }

    /**
     * 是否存在未审核的凭证
     * @author huxinlu
     * @param int $id 凭证ID
     * @return mixed
     */
    public function isExistUnchecked(int $id)
    {
        return self::where(['id' => $id, 'status' => self::STATUS_UNCHECKED])->exists();
    }

    /**
     * 审核通过
     * @author huxinlu
     * @param int $id 凭证ID
     * @param string $reviewer 反审核人
     * @return mixed
     */
    public function editStatusReview(int $id, string $reviewer)
    {
        return self::where(['id' => $id, 'status' => self::STATUS_PASS])
            ->update(['status' => self::STATUS_UNCHECKED, 'reviewer' => $reviewer, 'reviewDate' => date('Y-m-d')]);
    }

    /**
     * 是否存在审核通过的凭证
     * @author huxinlu
     * @param int $id 凭证ID
     * @return mixed
     */
    public function isExistPass(int $id)
    {
        return self::where(['id' => $id, 'status' => self::STATUS_PASS])->exists();
    }

    /**
     * 是否本期存在未审核的凭证
     * @author huxinlu
     * @param int $period 期数
     * @return mixed
     */
    public function isExistCurrentUnchecked($period)
    {
        return self::where('status',  self::STATUS_UNCHECKED)->whereMonth('date', $period)->exists();
    }

    /**
     * 获取最大凭证号
     * @author huxinlu
     * @param $date string 日期
     * @param $proofWordId int 凭证字
     * @return mixed
     */
    public function getMaxVoucherNo($date, $proofWordId)
    {
        return self::where(['date' => $date, 'proofWordId' => $proofWordId])->max('voucherNo');
    }

    /**
     * 是否存在已审核的凭证
     * @author huxinlu
     * @param $idArr array 凭证ID数组
     * @return mixed
     */
    public function isExistAudit($idArr)
    {
        return self::whereIn('id', $idArr)->where('status', self::STATUS_PASS)->exists();
    }

    /**
     * 删除凭证
     * @author huxinlu
     * @param $idArr array 凭证ID数组
     * @return mixed
     */
    public function delVoucher($idArr)
    {
        return self::whereIn('id', $idArr)->delete();
    }
}
