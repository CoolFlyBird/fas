<?php
Route::post('login', 'Auth\LoginController@login')->name('login');

//不需要验证
Route::get('account', 'System\AccountSetController@getList');//账套列表

//需要验证登录权限
Route::group(['middleware' => 'login'], function () {
    Route::get('permission', 'System\PermissionController@getList');//权限列表
});

//需要权限验证
Route::group(['middleware' => 'auth'], function () {
    //系统设置-账套
    Route::group(['prefix' => 'account'], function () {
        Route::post('create', 'System\AccountSetController@create');//创建账套
        Route::put('edit', 'System\AccountSetController@edit');//编辑账套
        Route::get('{id}', 'System\AccountSetController@getDetail');//账套详情
        Route::delete('{id}', 'System\AccountSetController@del');//删除账套
    });
    //系统设置-职员（系统设置-辅助核算-职员）
    Route::group(['prefix' => 'employee'], function () {
        Route::post('create', 'System\EmployeeController@createEmployee');//创建职员
        Route::put('edit', 'System\EmployeeController@editEmployee');//编辑职员
        Route::get('{id}', 'System\EmployeeController@getEmployeeDetail');//职员详情
        Route::delete('{id}', 'System\EmployeeController@delEmployee');//删除职员
        Route::get('', 'System\EmployeeController@getEmployeeList');//职员列表
        Route::put('password', 'System\PasswordController@editPassword');//修改密码
    });
    //系统设置-部门（系统设置-辅助核算-部门）
    Route::group(['prefix' => 'department'], function () {
        Route::post('create', 'System\EmployeeController@createDepartment');//创建部门
        Route::put('edit', 'System\EmployeeController@editDepartment');//编辑部门
        Route::delete('{id}', 'System\EmployeeController@delDepartment');//删除部门
        Route::get('', 'System\EmployeeController@getDepartmentList');//部门列表
    });
    //系统设置-凭证字
    Route::group(['prefix' => 'word'], function () {
        Route::post('create', 'System\ProofWordController@create');//添加凭证字
        Route::put('edit', 'System\ProofWordController@edit');//编辑凭证字
        Route::delete('{id}', 'System\ProofWordController@del');//删除凭证字
        Route::get('', 'System\ProofWordController@getList');//凭证字列表
    });
    //系统设置-辅助核算-客户
    Route::group(['prefix' => 'client'], function () {
        Route::post('create', 'System\AssistController@createClient');//添加客户
        Route::put('edit', 'System\AssistController@editClient');//编辑客户
        Route::delete('{id}', 'System\AssistController@delClient');//删除客户
        Route::get('', 'System\AssistController@getClientList');//客户列表
        Route::get('{id}', 'System\AssistController@getClientDetail');//客户详情
    });
    //系统设置-辅助核算-供应商
    Route::group(['prefix' => 'supplier'], function () {
        Route::post('create', 'System\AssistController@createSupplier');//添加供应商
        Route::put('edit', 'System\AssistController@editSupplier');//编辑供应商
        Route::delete('{id}', 'System\AssistController@delSupplier');//删除供应商
        Route::get('', 'System\AssistController@getSupplierList');//供应商列表
        Route::get('{id}', 'System\AssistController@getSupplierDetail');//供应商详情
    });
    //系统设置-辅助核算-项目
    Route::group(['prefix' => 'project'], function () {
        Route::post('create', 'System\AssistController@createProject');//添加项目
        Route::put('edit', 'System\AssistController@editProject');//编辑项目
        Route::delete('{id}', 'System\AssistController@delProject');//删除项目
        Route::get('', 'System\AssistController@getProjectList');//项目列表
        Route::get('{id}', 'System\AssistController@getProjectDetail');//项目详情
    });
    //系统设置-辅助核算-存货
    Route::group(['prefix' => 'stock'], function () {
        Route::post('create', 'System\AssistController@createStock');//添加存货
        Route::put('edit', 'System\AssistController@editStock');//编辑存货
        Route::delete('{id}', 'System\AssistController@delStock');//删除存货
        Route::get('', 'System\AssistController@getStockList');//存货列表
        Route::get('{id}', 'System\AssistController@getStockDetail');//存货详情
    });
    //系统设置-科目维护
    Route::group(['prefix' => 'subject'], function () {
        Route::post('create', 'System\SubjectController@create');//添加科目
        Route::put('edit', 'System\SubjectController@edit');//编辑科目
        Route::delete('{id}', 'System\SubjectController@del');//删除科目
        Route::get('', 'System\SubjectController@getList');//科目列表
        Route::get('{id}', 'System\SubjectController@getDetail');//科目详情
        Route::put('{id}/start', 'System\SubjectController@start');//启用科目
    });
    //财务处理-期初余额录入
    Route::group(['prefix' => 'balance'], function () {
        Route::get('{type?}', 'Finance\BalanceController@getList');//期初余额列表
        Route::put('', 'Finance\BalanceController@editInitialBalance');//编辑期初余额
        Route::put('amount', 'Finance\BalanceController@editAmount');//编辑计量单位
        Route::get('calculate', 'Finance\BalanceController@calculate');//试算平衡
    });
    //财务处理-录入凭证
    Route::group(['prefix' => 'voucher'], function () {
        Route::post('create', 'Finance\VoucherController@createVoucher');//创建凭证
    });
    //财务处理-录入凭证-凭证模板类别
    Route::group(['prefix' => 'voucher/template/type'], function () {
        Route::post('create', 'Finance\VoucherController@createVoucherTemplateType');//创建凭证模板类别
        Route::put('edit', 'Finance\VoucherController@editVoucherTemplateType');//编辑凭证模板类别
        Route::get('', 'Finance\VoucherController@getVoucherTemplateTypeList');//凭证模板类别列表
        Route::delete('{id}', 'Finance\VoucherController@delVoucherTemplateType');//删除凭证模板类别
    });
    //财务处理-录入凭证-凭证模板
    Route::group(['prefix' => 'voucher/template'], function () {
        Route::post('create', 'Finance\VoucherController@createVoucherTemplate');//创建凭证模板
        Route::get('{id}', 'Finance\VoucherController@getVoucherTemplateDetail');//凭证模板详情
        Route::get('', 'Finance\VoucherController@getVoucherTemplateList');//凭证模板列表
        Route::delete('{id}', 'Finance\VoucherController@delVoucherTemplate');//删除凭证模板
    });
    //财务处理-凭证管理
    Route::group(['prefix' => 'voucher/manage'], function () {
        Route::put('edit', 'Finance\VoucherManageController@editVoucher');//编辑凭证
        Route::get('{id}', 'Finance\VoucherManageController@getVoucherDetail');//凭证详情
        Route::get('', 'Finance\VoucherManageController@getVoucherList');//凭证列表
        Route::post('audit', 'Finance\VoucherManageController@audit');//审核凭证
        Route::post('review', 'Finance\VoucherManageController@review');//反审核凭证
    });
    //财务处理-期末结账
    Route::group(['prefix' => 'final'], function () {
        Route::post('settle', 'Finance\SettleAccountController@settleAccount');//结账
        Route::post('checkout', 'Finance\SettleAccountController@checkout');//反结账
    });
});

Route::group(['prefix' => 'report'], function () {//公司报表
    Route::get('test', 'report\SheetController@reportTest');//测试 事务，报表计算
    Route::get('balance', 'report\SheetController@balanceSheet');//资产负债表
    Route::get('income', 'report\SheetController@incomeSheet');//利润表表
    Route::get('cash', 'report\SheetController@cashFlowSheet');//现金流量表
});