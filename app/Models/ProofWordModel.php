<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Models;

class ProofWordModel extends BaseModel
{
    protected $table = 'proof_word';
    public $timestamps = false;

    /**
     * 凭证字列表
     * @author huxinlu
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getList()
    {
        return $this->query()->get();
    }
}
