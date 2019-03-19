<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class VoucherTemplateTypeModel extends BaseModel
{
    protected $table = 'voucher_template_type';
    public $timestamps = false;

    /**
     * 是否存在重复值
     * @author huxinlu
     * @param $id int 类别ID
     * @param $name string 类别名称
     * @return mixed
     */
    public function isExistReceiptExceptSelf($id, $name)
    {
        return self::where([['id', '<>', $id], 'name' => $name])->exists();
    }
}
