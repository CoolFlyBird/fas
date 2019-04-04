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

    public function voucher()
    {
        return $this->belongsTo('App\Models\VoucherModel', 'proofWordId', 'id');
    }
}
