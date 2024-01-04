<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class DeepConfig extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'deep_config';
    
    /**
     * 设置缓存
     */
    public static function SetListCache()
    {
        $key = 'DeepConfigList';
        $MyRedis = new MyRedis();
        $list = DeepConfig::query()
            ->get(['deep','rate'])
            ->toArray();
        if ($list) {
            $MyRedis->set_key($key, serialize($list));
            return $list;
        }
        if ($MyRedis->exists_key($key)) {
            $MyRedis->del_lock($key);
        }
        return [];
    }
    
    /**
     * 获取缓存
     */
    public static function GetListCache()
    {
        $key = 'DeepConfigList';
        $MyRedis = new MyRedis();
        $list = $MyRedis->get_key($key);
        if (!$list) {
            return self::SetListCache();
        } else {
            return unserialize($list);
        }
    }
    
}
