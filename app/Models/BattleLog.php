<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class BattleLog extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'battle_log';
    public $timestamps = false;

    public function nft(){
        return $this->hasOne(NftList::class,'id','nft_id');
    }

    public function monster(){
        return $this->hasOne(MonsterList::class,'id','monster_id');
    }
}
