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
}
