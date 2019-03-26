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

    public function loadResult($year, $period)
    {
        $result = $this->query()
            ->leftJoin('report_balance_name', 'report_balance_name.id', '=', 'report_balance.id')
            ->where(["year" => $year, "period" => $period])
            ->get();
        return $result;
    }

}
