<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

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
    public function getLastPeriodBeginBalance($year, $month, $subjectId)
    {
        return self::where(['subjectId' => $subjectId, 'year' => $year, 'month' => $month])
            ->get(['debitEndingBalance', 'creditEndingBalance'])
            ->toArray();
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
}
