<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Extracted extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'extracted';
    public $timestamps = false;

}
