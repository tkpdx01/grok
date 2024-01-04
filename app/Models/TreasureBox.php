<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TreasureBox extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'treasure_box';
    
}
