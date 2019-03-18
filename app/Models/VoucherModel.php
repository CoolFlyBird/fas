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

    public function voucherDetail()
    {
        return $this->hasMany('App\Models\VoucherDetailModel');
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

    public function getVoucherList($params)
    {
        switch ($params['range']) {
            //未审核
            case 1:
                $query = self::where('status', self::STATUS_UNCHECKED);
                break;
            //本年
            case 3:
                $query = self::whereYear('date', date('Y'));
                break;
            //时间段
            case 4:
                $query = self::whereBetween('date', [$params['startDate'], $params['endDate']]);
                break;
            //本期
            default:
                $query = self::whereMonth('date', $params['period']);
                break;
        }

        //凭证类别
        if ($params['classes'] != -1) {
            $query = $query->where('proofWordId', $params['classes']);
        }

        //金额
        if (!empty($params['money'])) {
            $query = $query->where(function ($query, $params) {
                $query->where('allDebit', $params['money'])->orWhere('allCredit', $params['money']);
            });
        }

        //摘要
        if (!empty($params['summary'])) {
            $query = $query->where('');
        }

        $list = $query->paginate(20);
    }
}
