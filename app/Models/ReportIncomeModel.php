<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */

namespace App\Models;

class ReportIncomeModel extends BaseModel
{
    public $timestamps = false;

    protected $table = 'report_income';

    public function loadResult($year, $period)
    {
        $result = $this->query()
            ->leftJoin('report_income_name', 'report_income_name.id', '=', 'report_income.id')
            ->where(["year" => $year, "period" => $period])
            ->get();
        return $result;
    }
}
