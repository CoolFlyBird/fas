<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;


use Illuminate\Support\Facades\DB;

class VoucherDetailModel extends BaseModel
{
    protected $table = 'voucher_detail';

    /**
     * 摘要对应的凭证ID
     * @author huxinlu
     * @param $summary
     * @return mixed
     */
    public function getSummaryLikeVoucherIds($summary)
    {
        return self::where('summary', 'like', '%' . $summary . '%')->pluck('voucherId')->toArray();
    }

    /**
     * 金额对应的凭证ID
     * @author huxinlu
     * @param $money
     * @return mixed
     */
    public function getMoneyVoucherIds($money)
    {
        return self::where('debit', $money)->orWhere('credit')->pluck('voucherId')->toArray();
    }

    /**
     * 凭证列表
     * @author huxinlu
     * @param $params
     * @return mixed
     */
    public function getVoucherList($params)
    {
        $query = $this->query()->leftjoin('voucher', 'voucher_detail.voucherId', '=', 'voucher.id');
        switch ($params['range']) {
            //未审核
            case 1:
                $query = $query->where('status', $params['status']);
                break;
            //本年
            case 3:
                $query = $query->whereYear('voucher.date', date('Y'));
                break;
            //时间段
            case 4:
                $query = $query->whereBetween('voucher.date', [$params['startDate'], $params['endDate']]);
                break;
            //本期
            default:
                $query = $query->whereMonth('voucher.date', $params['period']);
                break;
        }

        //凭证类别
        if ($params['classes'] != -1) {
            $query = $query->where('proofWordId', $params['classes']);
        }

        //摘要
        if (!empty($params['summary'])) {
            $query = $query->where('summary', 'like', '%' . $params['summary'] . '%');;
        }

        //金额
        if (!empty($params['money'])) {
            $query = $query->where(function ($query) use ($params) {
                $query->where('debit', $params['money'])
                    ->orWhere('credit', $params['money']);
            });
        }

        return $query->paginate($params['limit'])->toArray();
    }

    /**
     * 当前期的凭证列表
     * @author huxinlu
     * @param $year int 年份
     * @param $month int 月份
     * @return mixed
     */
    public function getCurrentVoucherList($year, $month)
    {
        return $this->from('voucher_detail as detail')
            ->leftJoin('subject', 'detail.subjectId', '=', 'subject.id')
            ->whereYear('detail.date', $year)
            ->whereMonth('detail.date', $month)
            ->groupBy('detail.subjectId')
            ->get(['detail.subjectId', 'subject', DB::raw('SUM(debit) as debitEndingBalance'), DB::raw('SUM(credit) as creditEndingBalance'), 'subject.direction', 'subject.balance'])
            ->toArray();
    }
}
