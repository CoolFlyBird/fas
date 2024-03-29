<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */

namespace App\Models;

class ReportCashFlowModel extends BaseModel
{
    public $timestamps = false;

    protected $table = 'report_cash_flow';

    public function loadResult($year, $period)
    {
        $result = $this->query()
            ->leftJoin('report_cash_flow_name', 'report_cash_flow_name.id', '=', 'report_cash_flow.id')
            ->where(["year" => $year, "period" => $period])
            ->get();
        return $result;
    }

    public function getTotalAmount($year, $period, $id)
    {
        $result = $this->query()
            ->where(["year" => $year, "period" => $period, "id" => $id])
            ->get();
        return $result;
    }

    public function getTotalAmountArray($year, $period)
    {
        $result = $this->query()
            ->where(["year" => $year, "period" => $period])
            ->get(["id", "totalAmount"])
            ->pluck("totalAmount", "id");
        return $result;
    }
}
