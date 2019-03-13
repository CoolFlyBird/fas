<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * 新增
     * @author huxinlu
     * @param array $options
     * @return bool
     */
    public function add(array $options = [])
    {
        if (!empty($options)) {
            foreach ($options as $k => $v) {
                $this->$k = $v;
            }
        }

        return $this->save();
    }

    /**
     * 主键更新
     * @author huxinlu
     * @param array $option 更新数据
     * @return bool
     */
    public function edit(array $option)
    {
        $query = $this->query()->find($option['id']);
        foreach ($option as $k => $v) {
            $query->$k = $v;
        }

        return $query->save();
    }

    /**
     * 条件更新
     * @author huxinlu
     * @param array $where 查询条件
     * @param array $options 更新数据
     * @return int
     */
    public function update(array $where = [], array $options = [])
    {
        return $this->query()->where($where)->update($options);
    }

    /**
     * 主键删除
     * @author huxinlu
     * @param $id int 主键
     * @return mixed
     */
    public function del(int $id)
    {
        return $this->query()->where('id', $id)->delete();
    }

    /**
     * 主键获取详情
     * @author huxinlu
     * @param int $id 主键ID
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getDetail(int $id)
    {
        return $this->query()->where('id', $id)->get()->first()->toArray();
    }

    /**
     * 列表
     * @author huxinlu
     * @param array $where 查询条件
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getList(array $where = [])
    {
        return $this->query()->where($where)->get()->toArray();
    }

    /**
     * 分页列表
     * @author huxinlu
     * @param array $where 查询条件
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPageList(array $where = [])
    {
        return $this->query()->where($where)->paginate(20)->toArray();
    }
}
