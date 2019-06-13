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
    public $timestamps = false;

    public function voucher()
    {
        return $this->belongsTo('App\Models\VoucherModel', 'voucherId', 'id');
    }

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
            ->get(['detail.subjectId', 'subject', DB::raw('SUM(debit) as debitBalance'), DB::raw('SUM(credit) as creditBalance'), 'subject.direction', 'subject.balance'])
            ->toArray();
    }

    /**
     * 明细账列表
     * @author huxinlu
     * @param $params
     * @return mixed
     */
    public function getDetailList($params)
    {
        return $this->from('voucher_detail as d')
            ->leftJoin('voucher as v', 'd.voucherId', '=', 'v.id')
            ->leftJoin('proof_word as w', 'v.proofWordId', '=', 'w.id')
            ->leftJoin('subject as s', 'd.subjectId', '=', 's.id')
            ->whereYear('d.date', date('Y'))
            ->where(DB::raw('month(d.date)'), '>=', (int)$params['startPeriod'])
            ->where(DB::raw('month(d.date)'), '<=', (int)$params['endPeriod'])
            ->where('d.subjectId', $params['subjectId'])
            ->orderBy('d.date', 'asc')
            ->select(['d.date',DB::raw('CONCAT(s.code,s.name) as name'), DB::raw("CONCAT_WS('-',w.name,v.voucherNo) as voucherNo"), 'd.summary', 'd.debit', 'd.credit', 's.direction', 's.balance as beginBalance', 'd.subjectId'])
            ->paginate($params['limit'])
            ->toArray();
    }

    /**
     * 每月科目月份
     * @author huxinlu
     * @param $auxiliaryTypeId int 辅助核算类型ID
     * @param $startPeriod int 开始期间
     * @param $endPeriod int 结束期间
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getMonthlySubjectDateArr($auxiliaryTypeId, $startPeriod, $endPeriod, $subjectId)
    {
        return self::where([
            ['auxiliaryTypeId', '=', $auxiliaryTypeId],
            [DB::raw('month(date)'), '>=', (int)$startPeriod],
            [DB::raw('month(date)'), '<=', (int)$endPeriod],
            [DB::raw('year(date)'), '=', date('Y')],
            ['subjectId', '=', $subjectId]
        ])
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->select([DB::raw("date_format(date, \"%Y-%m\") as month")])
            ->pluck('month')
            ->toArray();
    }

    /**
     * 科目每月辅助核算列表
     * @author huxinlu
     * @param $auxiliaryTypeId int 辅助核算类型ID
     * @param $monthDate string 年月：2019-01
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getAssistSubjectMonthlyList($auxiliaryTypeId, $monthDate, $subjectId)
    {
        return $this->from('voucher_detail as d')
            ->leftJoin('voucher as v', 'd.voucherId', '=', 'v.id')
            ->leftJoin('proof_word as w', 'v.proofWordId', '=', 'w.id')
            ->where([
                ['d.auxiliaryTypeId', '=', $auxiliaryTypeId],
                [DB::raw('date_format(d.date, "%Y-%m")'), '=', $monthDate],
                ['d.subjectId', '=', $subjectId]
            ])
            ->orderBy('d.date', 'asc')
            ->get([DB::raw("CONCAT_WS('-',w.name,v.voucherNo) as voucherNo"), 'd.date', 'd.summary', 'd.debit', 'd.credit'])
            ->toArray();
    }

    /**
     * 成本和损益列表
     * @author huxinlu
     * @param $year int 年份
     * @param $month int 月份
     * @return mixed
     */
    public function getProfitList($year, $month)
    {
        return self::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where(function ($query) {
//                $query->where('subjectId', 'like', '5%')
//                    ->orWhere('subjectId', 'like', '6%');
                $query->where('subjectId', '>=', '167');
            })
            ->get()
            ->toArray();
    }

    /**
     * 当前期间借贷方总金额
     * @author huxinlu
     * @param $year int 年份
     * @param $month int 月份
     * @return mixed
     */
    public function getCurrentAllMoney($year, $month)
    {
        return self::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where(function ($query) {
//                $query->where('subjectId', 'like', '5%')
//                    ->orWhere('subjectId', 'like', '6%');
                $query->where('subjectId', '>=', '167');
            })
            ->get([DB::raw('sum(debit) as allDebit'), DB::raw('sum(credit) as allCredit')])
            ->first()
            ->toArray();
    }

    /**
     * 是否存在当前期本年利润数据
     * @author huxinlu
     * @param $date string 日期
     * @return mixed
     */
    public function isExistCurrentProfit($date)
    {
        return self::where(['date' => $date, 'subjectId' => 157])->exists();
    }

    /**
     * 删除凭证详情
     * @author huxinlu
     * @param $idArr array 凭证ID数组
     * @return mixed
     */
    public function delVoucherDetail($idArr)
    {
        return self::whereIn('voucherId', $idArr)->delete();
    }
}
