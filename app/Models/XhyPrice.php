<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class XhyPrice extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'xhy_price';
    
}
