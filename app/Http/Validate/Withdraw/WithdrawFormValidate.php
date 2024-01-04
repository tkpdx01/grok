<?php


namespace App\Http\Validate\Withdraw;



use App\Http\Validate\BaseValidate;

class WithdrawFormValidate extends BaseValidate
{

    public function rules(){
        return [
            'num' => 'required|regex:/^\d+(?:\.\d{1,2})?$/',
//            'type' => 'required|in:1,2',
        ];
    }


}
