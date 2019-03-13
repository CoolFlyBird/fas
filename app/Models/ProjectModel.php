<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class ProjectModel extends BaseModel
{
    protected $table = 'project';
    public $timestamps = false;

    /**
     * 添加项目
     * @author huxinlu
     * @param array $params
     * @return bool
     */
    public function create(array $params)
    {
        $params['code'] = str_pad($this->getMaxCode() + 1, 3, '0', STR_PAD_LEFT);
        return $this->add($params);
    }

    /**
     * 最大编码
     * @author huxinlu
     * @return mixed
     */
    public function getMaxCode()
    {
        return $this->query()->max('code');
    }
}
