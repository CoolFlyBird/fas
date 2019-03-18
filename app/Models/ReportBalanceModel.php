<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */

namespace App\Models;

class ReportBalanceModel extends BaseModel
{
    public $timestamps = false;

    protected $table = 'report_balance';

    /**
     * 资产负债表
     * @author unual
     * @param $year
     * @param $month
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getBalanceSheetByMonth($year, $month)
    {
        return $this->query()->where(['year' => $year, 'month' => $month])->orderBy("id", "desc")->get();
    }


}
