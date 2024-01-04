<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class UsersCoin extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'users_coin';

    /**
     * 获取账户余额
     * @param $userId
     * @param $type
     * @return int|mixed
     */
    public static function getAmount($userId,$type){
        $amount = self::query()->where(['user_id'=>$userId,'type'=>$type])->value('amount');
        if (is_null($amount)){
            self::query()->insert(['user_id'=>$userId,'type'=>$type,'created_at'=>date('Y-m-d H:i:s')]);
            $amount = 0;
        }
        return $amount;
    }


    public static function monsIncome($userId,$amountType,$amount,$type,$remark,$fromId = 0){
        $user = self::query()->where('user_id',$userId)->where('type',$amountType)->first();
        if ($amount != 0){
            if (empty($user)){
                $user = new self();
                $user->user_id = $userId;
                $user->type = $amountType;
                $user->amount = 0;
                $user->created_at = date('Y-m-d H:i:s');
                $user->save();
            }
            $before = $user->amount;
            $after = bcadd($user->amount,$amount,4);
            IncomeLog::query()->insert([
                'user_id' => $userId,
                'from_id' => $fromId,
                'amount_type' => $amountType,
                'before' => $before,
                'total' => $amount,
                'after' => $after,
                'type' => $type,
                'remark' => $remark,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            if ($amount > 0){
                self::query()->where('user_id',$userId)->where('type',$amountType)->increment('amount',$amount);
            }else{
                self::query()->where('user_id',$userId)->where('type',$amountType)->decrement('amount',abs($amount));
            }
        }
    }
}
