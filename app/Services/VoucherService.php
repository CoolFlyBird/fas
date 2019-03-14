<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 */
namespace App\Services;

use App\Models\VoucherDetailModel;
use App\Models\VoucherModel;
use App\Models\VoucherTemplateDetailModel;
use App\Models\VoucherTemplateModel;

class VoucherService
{
    public function __construct(VoucherModel $voucherModel, VoucherDetailModel $voucherDetailModel,
                                VoucherTemplateModel $voucherTemplateModel, VoucherTemplateDetailModel $voucherTemplateDetailModel)
    {
        $this->voucherModel = $voucherModel;
        $this->voucherDetailModel = $voucherDetailModel;
        $this->voucherTemplateModel = $voucherTemplateModel;
        $this->voucherTemplateDetailModel = $voucherTemplateDetailModel;
    }

    public function create($params)
    {
        $this->voucherModel;
    }
}
