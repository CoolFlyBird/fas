<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class CurrentPeriodModel extends BaseModel
{
    protected $table = 'current_period';

    /**
     * 当前所在期间
     * @author huxinlu
     * @return mixed
     */
    public function getCurrentPeriod()
    {
        return $this->query()->first()->value('period');
    }
}
