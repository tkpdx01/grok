<?php

namespace App\Http\Validate\Transfer;



use App\Http\Validate\BaseValidate;

class TransferListValidate extends BaseValidate
{

    public function rules(){
        return [
            'page' => 'required|integer',
            'size' => 'required|integer'
        ];
    }

}

