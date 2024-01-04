<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Recharged extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'recharged';
    public $timestamps = false;

}
