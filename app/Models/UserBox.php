<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class UserBox extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'user_box';


    public static function insertBox($userId,$amount){
        $user = self::query()->where('user_id',$userId)->first();;
        if ($amount != 0){
            if (empty($user)){
                $user = new self();
                $user->user_id = $userId;
                $user->box_id = 1;
                $user->num = 0;
                $user->created_at = date('Y-m-d H:i:s');
                $user->save();
            }
            if ($amount > 0){
                self::query()->where('user_id',$userId)->increment('num',$amount);
            }else{
                self::query()->where('user_id',$userId)->decrement('num',abs($amount));
            }
        }
    }
}
