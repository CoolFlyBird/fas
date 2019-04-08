<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeModel extends User
{
    protected $table = 'employee';
    protected $rememberTokenName = '';
    protected $hidden = ['password'];

    const STATUS_ON = 1;//在职
    const STATUS_OFF = 2;//离职
    const DISABLED = 1;

    /**
     * 创建职员
     * @author huxinlu
     * @param $params
     * @return bool
     */
    public function create(array $params)
    {
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $this->$k = $v;
                if ($k == 'password') {
                    $this->$k = Hash::make($v);
                }
            }
            $this->status = self::STATUS_ON;
        }

        $this->code = str_pad($this->getMaxCode() + 1, 6, "0", STR_PAD_LEFT);

        return $this->save();
    }

    /**
     * 获取最大编码
     * @author huxinlu
     * @return mixed
     */
    public function getMaxCode()
    {
        return $this->query()->max('code');
    }

    /**
     * 编辑职员
     * @author huxinlu
     * @param $params
     * @return bool
     */
    public function edit(array $params)
    {
        $query = $this->query()->find($params['id']);
        foreach ($params as $k => $v) {
            $query->$k = $v;
            if ($k == 'password') {
                $query->$k = Hash::make($v);
            }
        }

        return $query->save();
    }

    /**
     * 职员详情
     * @author huxinlu
     * @param int $id 职员ID
     * @return mixed
     */
    public function getDetail(int $id)
    {
        return self::where('id', $id)->get()->first();
    }

    /**
     * 删除职员
     * @author huxinlu
     * @param int $id 职员ID
     * @return mixed
     */
    public function del(int $id)
    {
        return self::where('id', $id)->delete();
    }

    /**
     * 职员列表
     * @author huxinlu
     * @param $limit int 每页显示数
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList($limit)
    {
        return $this->query()
            ->leftJoin('department', 'employee.departmentId', '=', 'department.id')
            ->select(['employee.*', DB::raw('if(employee.departmentId = 0, "", department.name) as departmentName')])
            ->paginate($limit);
    }

    /**
     * 修改密码
     * @author huxinlu
     * @param int $id 职员ID
     * @param string $password 密码
     * @return bool
     */
    public function editPassword(int $id, string $password)
    {
        $query           = $this->query()->find($id);
        $query->password = Hash::make($password);
        return $query->save();
    }
}
