<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

class CurrentPeriodModel extends BaseModel
{
    protected $table = 'current_period';
    public $timestamps = false;

    /**
     * 当前所在年份
     * @author huxinlu
     * @return mixed
     */
    public function getCurrentYear()
    {
        return $this->query()->first()->value('year');
    }

    /**
     * 当前所在期间
     * @author huxinlu
     * @return mixed
     */
    public function getCurrentPeriod()
    {
        return $this->query()->first()->value('period');
    }

    /**
     * 编辑当前的所在期间
     * @author huxinlu
     * @param $year int 当前所在期间年份
     * @param $month int 当前所在期间月份
     * @param $nextYear int 下一期所在期间年份
     * @param $nextMonth int 下一期所在期间月份
     * @return mixed
     */
    public function editCurrent($year, $month, $nextYear, $nextMonth)
    {
        return self::where(['year' => $year, 'period' => $month])->update(['year' => $nextYear, 'period' => $nextMonth]);
    }

    /**
     * 当前期间
     * @author huxinlu
     * @return mixed
     */
    public function getCurrentDate()
    {
        return $this->query()->first();
    }
}
