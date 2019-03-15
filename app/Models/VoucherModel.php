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

    public function getVoucherList($where, $whereMonth, $whereYear, $whereBetween)
    {
        return self::where($where)->whereMonth($whereMonth)->whereYear($whereYear)->whereBetween($whereBetween)->get();
    }
}
