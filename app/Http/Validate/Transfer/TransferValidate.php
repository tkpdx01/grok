<?php

namespace App\Http\Validate\Transfer;

use App\Http\Validate\BaseValidate;

class TransferValidate extends BaseValidate
{

    public function rules(){
        return [
            'num' => 'required|regex:/^\d+$/',
            'address' => 'required',
        ];
    }

}
