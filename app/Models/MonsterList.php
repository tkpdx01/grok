<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class MonsterList extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'monster_list';

    protected $appends = ['icon_path'];

    public function getIconPathAttribute(){
        return assertUrl($this->icon,'admin');
    }

}
