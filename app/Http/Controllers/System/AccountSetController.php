<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Http\Controllers\System;

use App\Http\Services\System\AccountService;
use App\Models\System\AccountSetModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AccountSetController extends Controller
{
    /**
     * 创建账套
     * @author huxinlu
     * @param Request $request
     * @param name string 账套名称
     * @param companyName string 企业名称
     * @param standardMoneyType int 本位币类型：1-人民币(CNY),2-美元(USD)
     * @param institution int 会计制度：1-2013年小企业会计准则，2-新会计准则
     * @param date string 启用日期，例：2019-02
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $params = $request->only(['name', 'companyName', 'standardMoneyType', 'institution', 'date']);
        $validator = Validator::make($params, [
            'name'              => 'required|unique:account_set|max:20',
            'companyName'       => 'required|max:30',
            'standardMoneyType' => 'required|in:1,2',
            'institution'       => 'required|in:1,2',
            'date'              => 'required|date_format:Y-m',
        ], [
            'name.require'              => '账套名称不能为空',
            'name.unique'               => '账套名称不能重复',
            'name.max'                  => '账套名称不能超过20个字符',
            'companyName.require'       => '企业名称不能为空',
            'companyName.max'           => '企业名称不能超过30个字符',
            'standardMoneyType.require' => '本位币不能为空',
            'standardMoneyType.in'      => '本位币类型不正确',
            'institution.require'       => '会计制度不能为空',
            'institution.in'            => '会计制度类型不正确',
            'date.require'              => '启用时间不能为空',
            'date.date_format'          => '启用时间格式不正确，正确格式为' . date('Y-m'),
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->fail($error, 2001);
        }

        $accountService = new AccountService();
        $res = $accountService->createAccountSet($params);

        return $res ? $this->success() : $this->fail('创建失败');
    }

    /**
     * 编辑账套
     * @author huxinlu
     * @param Request $request
     * @param id int 账套ID
     * @param name string 账套名称
     * @param companyName string 企业名称
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $params = $request->only(['id', 'name', 'companyName']);
        $validator = Validator::make($params, [
            'id'                => 'required|integer',
            'name'              => 'required|max:20',
            'companyName'       => 'required|max:30',
        ], [
            'id.require'                => '账套ID不能为空',
            'id.integer'                => '账套ID只能是整数',
            'name.require'              => '账套名称不能为空',
            'name.unique'               => '账套名称不能重复',
            'name.max'                  => '账套名称不能超过20个字符',
            'companyName.require'       => '企业名称不能为空',
            'companyName.max'           => '企业名称不能超过30个字符',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->fail($error, 2001);
        }

        $accountService = new AccountService();
        $res = $accountService->editAccountSet($params);

        return $res ? $this->success() : $this->fail('编辑失败');
    }

    /**
     * 账套详情
     * @author huxinlu
     * @param int $id 账套ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(int $id)
    {
        $accountSetModel = new AccountSetModel();
        $detail = $accountSetModel->getAccountSetDetail($id);

        return $this->success($detail);
    }

    /**
     * 账套列表
     * @author huxinlu
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $accountSetModel = new AccountSetModel();
        $detail = $accountSetModel->getAccountSetList();

        return $this->success($detail);
    }

    /**
     * 删除账套
     * @author huxinlu
     * @param int $id 账套ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(int $id)
    {
        $validator =Validator::make(['id' => $id], [
            'id' => 'exists:account_set',
        ], [
            'id.exists' => '该账套不存在'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->fail($error, 2001);
        }

        $accountSetModel = new AccountSetModel();
        $res = $accountSetModel->delAccountSet($id);

        return $res ? $this->success() : $this->fail();
    }
}
