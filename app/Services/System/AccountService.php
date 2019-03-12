<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2019/2/28
 * Time: 11:21
 */
namespace App\Services\System;

use App\Models\System\AccountSetModel;

class AccountService
{
    /**
     * 创建账套
     * @author huxinlu
     * @param $params array
     * @param name string 账套名称
     * @param companyName string 企业名称
     * @param standardMoneyType int 本位币类型：1-人民币(CNY),2-美元(USD)
     * @param institution int 会计制度：1-2013年小企业会计准则，2-新会计准则
     * @param date int 启用日期，例：2019-02
     * @return bool
     */
    public function createAccountSet(array $params)
    {
        $accountSetModel = new AccountSetModel();
        return $accountSetModel->add($params);
    }

    /**
     * 编辑账套
     * @author huxinlu
     * @param $params
     * @param name string 账套名称
     * @param companyName string 企业名称
     * @return bool
     */
    public function editAccountSet(array $params)
    {
        $accountSetModel = new AccountSetModel();
        return $accountSetModel->edit($params);
    }
}