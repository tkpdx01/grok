<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OpenLog extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'open_log';
    
}
