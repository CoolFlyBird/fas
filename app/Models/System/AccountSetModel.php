<?php

namespace App\Models\System;

use App\Models\BaseModel;

class AccountSetModel extends BaseModel
{
    protected $table = 'account_set';

    /**
     * 账套详情
     * @author huxinlu
     * @param int $id 账套ID
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     */
    public function getAccountSetDetail(int $id)
    {
        return $this->query()->find($id);
    }

    /**
     * 账套列表
     * @author huxinlu
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAccountSetList()
    {
        return $this->query()->get();
    }

    /**
     * 删除账套
     * @author huxinlu
     * @param int $id 账套ID
     * @return mixed
     */
    public function delAccountSet(int $id)
    {
        return $this->query()->where('id', $id)->delete();
    }
}
