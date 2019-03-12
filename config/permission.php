<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2019/3/9
 * Time: 14:54
 */
return [
    'system'  => [
        'name'     => '系统设置',
        'children' => [
            'subject'    => '科目维护',
            'proofWord'  => '凭证字',
            'accountSet' => '账套管理',
            'assist'     => '辅助核算',
            'employee'   => '职员管理',
            'password'   => '密码管理',
            'log'        => '操作日志',
        ]
    ],
    'finance' => [
        'name'     => '财务管理',
        'children' => [
            'balance'       => '期初余额录入',
            'voucher'       => '录入凭证',
            'voucherManage' => '凭证管理',
            'settleAccount' => '期末结账',
        ]
    ],
    'report'  => [
        'name'     => '公司报表',
        'children' => [
            'subjectBalance' => '科目余额表',
            'detail'         => '明细账',
            'assistDetail'   => '核算项目明细账',
            'balance'        => '资产负债表',
            'profit'         => '利润表',
        ],
    ],
];