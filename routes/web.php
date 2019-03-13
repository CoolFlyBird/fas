<?php

Auth::routes();
Route::get('home', 'HomeController@index')->name('home');
Route::post('login', 'Auth\LoginController@login')->name('login');

//不需要验证
Route::get('account/set', 'System\AccountSetController@getList');//账套列表

//需要验证登录权限
Route::group(['middleware' => 'login'], function () {
    Route::get('permission', 'System\PermissionController@getList');//权限列表
});

//需要权限验证
Route::group(['middleware' => 'auth'], function () {
    //账套
    Route::group(['prefix' => 'account'], function () {
        Route::post('create', 'System\AccountSetController@create');//创建账套
        Route::put('edit', 'System\AccountSetController@edit');//编辑账套
        Route::get('{id}', 'System\AccountSetController@getDetail');//账套详情
        Route::delete('{id}', 'System\AccountSetController@del');//删除账套
    });
    //职员（辅助核算-职员）
    Route::group(['prefix' => 'employee'], function () {
        Route::post('create', 'System\EmployeeController@createEmployee');//创建职员
        Route::put('edit', 'System\EmployeeController@editEmployee');//编辑职员
        Route::get('{id}', 'System\EmployeeController@getEmployeeDetail');//职员详情
        Route::delete('{id}', 'System\EmployeeController@delEmployee');//删除职员
        Route::get('', 'System\EmployeeController@getEmployeeList');//职员列表
        Route::put('password', 'System\PasswordController@editPassword');//修改密码
    });
    //部门（辅助核算-部门）
    Route::group(['prefix' => 'department'], function () {
        Route::post('create', 'System\EmployeeController@createDepartment');//创建部门
        Route::put('edit', 'System\EmployeeController@editDepartment');//编辑部门
        Route::delete('{id}', 'System\EmployeeController@delDepartment');//删除部门
        Route::get('', 'System\EmployeeController@getDepartmentList');//部门列表
    });
    //凭证字
    Route::group(['prefix' => 'voucher'], function () {
        Route::post('create', 'System\ProofWordController@create');//添加凭证字
        Route::put('edit', 'System\ProofWordController@edit');//编辑凭证字
        Route::delete('{id}', 'System\ProofWordController@del');//删除凭证字
        Route::get('', 'System\ProofWordController@getList');//凭证字列表
    });
    //辅助核算-客户
    Route::group(['prefix' => 'client'], function () {
        Route::post('create', 'System\AssistController@createClient');//添加客户
        Route::put('edit', 'System\AssistController@editClient');//编辑客户
        Route::delete('{id}', 'System\AssistController@delClient');//删除客户
        Route::get('', 'System\AssistController@getClientList');//客户列表
        Route::get('{id}', 'System\AssistController@getClientDetail');//客户详情
    });
    //辅助核算-供应商
    Route::group(['prefix' => 'supplier'], function () {
        Route::post('create', 'System\AssistController@createSupplier');//添加供应商
        Route::put('edit', 'System\AssistController@editSupplier');//编辑供应商
        Route::delete('{id}', 'System\AssistController@delSupplier');//删除供应商
        Route::get('', 'System\AssistController@getSupplierList');//供应商列表
        Route::get('{id}', 'System\AssistController@getSupplierDetail');//供应商详情
    });
    //辅助核算-项目
    Route::group(['prefix' => 'project'], function () {
        Route::post('create', 'System\AssistController@createProject');//添加项目
        Route::put('edit', 'System\AssistController@editProject');//编辑项目
        Route::delete('{id}', 'System\AssistController@delProject');//删除项目
        Route::get('', 'System\AssistController@getProjectList');//项目列表
        Route::get('{id}', 'System\AssistController@getProjectDetail');//项目详情
    });
    //辅助核算-存货
    Route::group(['prefix' => 'stock'], function () {
        Route::post('create', 'System\AssistController@createStock');//添加存货
        Route::put('edit', 'System\AssistController@editStock');//编辑存货
        Route::delete('{id}', 'System\AssistController@delStock');//删除存货
        Route::get('', 'System\AssistController@getStockList');//存货列表
        Route::get('{id}', 'System\AssistController@getStockDetail');//存货详情
    });
    //科目
    Route::group(['prefix' => 'subject'], function () {
        Route::post('create', 'System\SubjectController@create');//添加科目
        Route::put('edit', 'System\SubjectController@edit');//编辑科目
        Route::delete('{id}', 'System\SubjectController@del');//删除科目
        Route::get('list/{type}/{parentCode?}', 'System\SubjectController@getList');//科目列表
        Route::get('{id}', 'System\SubjectController@getDetail');//科目详情
        Route::put('{id}/start', 'System\SubjectController@start');//启用科目
    });
});
