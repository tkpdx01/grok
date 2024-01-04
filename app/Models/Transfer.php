<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'transfer';

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    public function other(){
        return $this->hasOne(User::class,'id','transfer_id');
    }
}
