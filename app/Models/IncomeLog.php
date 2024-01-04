<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class IncomeLog extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'income_log';

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function from(){
        return $this->hasOne(User::class,'id','from_id');
    }
}
