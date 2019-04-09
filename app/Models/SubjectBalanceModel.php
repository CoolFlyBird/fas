<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

use Illuminate\Support\Facades\DB;

class SubjectBalanceModel extends BaseModel
{
    protected $table = 'subject_balance';

    /**
     * 是否存在数据
     * @author huxinlu
     * @return bool
     */
    public function isExistData()
    {
        $count = $this->query()->count('id');
        return $count > 0 ? false : true;
    }

    /**
     * 上期期末余额
     * @author huxinlu
     * @param $year int 年份
     * @param $month int 月份
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getLastPeriodEndingBalance($year, $month, $subjectId)
    {
        return self::where(['subjectId' => $subjectId, 'year' => $year, 'month' => $month])->value('endingBalance');
    }

    /**
     * 获取科目ID
     * @author huxinlu
     * @param $year int 年份
     * @param $month int 月份
     * @return mixed
     */
    public function delSubject($year, $month)
    {
        return $this->delAll(['year' => $year, 'month' => $month]);
    }

    /**
     * 科目余额列表
     * @author huxinlu
     * @param $params
     * @return mixed
     */
    public function getSubjectBalanceList($params)
    {
        return $this->from('subject_balance as sb')
            ->leftJoin('subject as s', 'sb.subjectId', '=', 's.id')
            ->where([
                ['s.status', '=', 1],
                [DB::raw('LENGTH(s.code)'), '<=', $params['length']],
                ['sb.month', '>=', $params['startPeriod']],
                ['sb.month', '<=', $params['endPeriod']],
                ['sb.year', '=', date('Y')]
            ])
            ->when($params['isDisplay'] == 0, function ($query) {
                return $query->where(function ($whereQuery) {
                    $whereQuery->orwhere('sb.endingBalance', '<>', 0)
                        ->orWhere('debitBalance', '<>', 0)
                        ->orWhere('creditBalance', '<>', 0);
                });
            })->when(!empty($params['filter']), function ($query) use ($params) {
                return $query->where(function ($whereQuery) use ($params) {
                    $whereQuery->orWhere('s.code', 'like', '%' . $params['filter'] . '%')
                        ->orWhere('s.name', 'like', '%' . $params['filter'] . '%');
                });
            })
            ->select(['beginBalance', 'endingBalance', 'debitBalance', 'creditBalance', 'code', 'name', 'parentSubjectCode', 'yearDebitBalance', 'yearCreditBalance'])
            ->orderBy(DB::raw('RPAD(code,10,0)'), 'asc')
            ->paginate($params['limit'])
            ->toArray();
    }

    /**
     * 当年最大会计期间
     * @author huxinlu
     * @return mixed
     */
    public function getYearMaxMonth()
    {
        return self::where('year', date('Y'))->max('month');
    }

    /**
     * 年度发生额
     * @author huxinlu
     * @param $year int 年份
     * @return mixed
     */
    public function getYearBalance($year)
    {
        return self::where('year', $year)
            ->get(DB::raw('sum(debitBalance) as yearDebitBalance'), DB::raw('sum(creditBalance) as yearCreditBalance'))
            ->toArray();
    }

    /**
     * 期初余额
     * @author huxinlu
     * @param $year int 年份
     * @param $month int 月份
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getSubjectBeginBalance($year, $month, $subjectId)
    {
        return self::where(['year' => $year, 'month' => $month, 'subjectId' => $subjectId])->value('beginBalance');
    }

    /**
     * 搜索科目列表
     * @author huxinlu
     * @param $filter string 搜索词
     * @return mixed
     */
    public function getSearchList($filter)
    {
        return $this->from('subject_balance as sb')
            ->leftJoin('subject as s', 'sb.subjectId', '=', 's.id')
            ->when(!empty($filter), function ($query) use ($filter) {
                $query->orWhere('s.code', 'like', '%' . $filter . '%')
                    ->orWhere('s.name', 'like', '%' . $filter . '%');
            })
            ->groupBy('s.id')
            ->orderBy('s.id', 'asc')
            ->get(['s.id', 's.code', 's.name'])
            ->toArray();
    }

    /**
     * 科目每月余额详情
     * @author huxinlu
     * @param $year int 年份
     * @param $month int 月份
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getSubjectMonthYearBalanceDetail($year, $month, $subjectId)
    {
        return self::where(['year' => $year, 'month' => $month, 'subjectId' => $subjectId])->get()->first();
    }

    /**
     * 科目期初余额
     * @author huxinlu
     * @param $year int 年份
     * @param $startMonth int 起始月份
     * @param $endMonth int 结束月份
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getSubjectInitialBalance($year, $startMonth, $endMonth, $subjectId)
    {
        return self::where(['year' => $year, 'subjectId' => $subjectId])
            ->where('month', '>=', $startMonth)
            ->where('month', '<=', $endMonth)
            ->orderBy('month', 'asc')
            ->limit(1)
            ->value('beginBalance');
    }

    /**
     * 科目最小月份
     * @author huxinlu
     * @param $year int 年份
     * @param $startMonth int 起始月份
     * @param $endMonth int 结束月份
     * @param $subjectId int 科目ID
     * @return mixed
     */
    public function getSubjectMinMonth($year, $startMonth, $endMonth, $subjectId)
    {
        return self::where(['year' => $year, 'subjectId' => $subjectId])
            ->where('month', '>=', $startMonth)
            ->where('month', '<=', $endMonth)
            ->orderBy('month', 'asc')
            ->limit(1)
            ->value('month');
    }
}
