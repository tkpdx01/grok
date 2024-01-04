<?php

namespace App\Models;

use App\Jobs\test;
use App\Jobs\UpdateDynamicPowerJob;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\ModelTree;
use GuzzleHttp\Client;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable ,HasDateTimeFormatter, ModelTree;

    protected $titleColumn = 'name';

    protected $parentColumn = 'parent_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'wallet',
        'path',
        'code',
        'parent_id',
        'deep',
        'headimgurl',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model){
            //生成邀请码
            while (true){
                $code = getRandStr(8);
                if (!self::query()->where('code',$code)->exists()){
                    break;
                }
            }
            $model->code = $code;
        });
            
        static::created(function ($model){
            //更新直推 团队
            $pUser = explode('-',trim($model->path,'-'));
            $pUserId = $pUser[count($pUser)-1];
            //给上级直推人数加1 ，以及整个链条上的所有人团队人数+1
            self::query()->where('id',$pUserId)->increment('zhi_num');
            self::query()->whereIn('id',$pUser)->increment('group_num');
            //自动获取地址
            //$model->wallet = getWallet($model->id);
            $model->save();
        });
    }
    
    public function group(){
        return $this->hasOne(LevelConfig::class,'id','level_id');
    }

    public function parent(){
        return $this->hasOne(self::class,'id','parent_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public function coin1(){
        return $this->hasOne(UsersCoin::class,'user_id','id')->where('type',1);
    }
    
    /**
     * 给上级增加业绩
     */
    public function handlePerformance($path, $num, $type=1)
    {
        $parentIds = explode('-',trim($path,'-'));
        $parentIds = array_reverse($parentIds);
        $parentIds = array_filter($parentIds);
        if ($parentIds) {
            if ($type==1) {
                $up['teamperfor'] = DB::raw("`teamperfor`+{$num}");
                $up['total_teamperfor'] = DB::raw("`total_teamperfor`+{$num}");
                User::query()->whereIn('id', $parentIds)->update($up);
            } else {
                $up['teamperfor'] = DB::raw("`teamperfor`-{$num}");
                $up['total_teamperfor'] = DB::raw("`total_teamperfor`-{$num}");
                User::query()->whereIn('id', $parentIds)->update($up);
            }
        }
    }
    
    /**
     * 给自己增加业绩
     */
    public function handleAchievement($user_id, $num, $type=1)
    {
        if ($type==1) {
            $up['myperfor'] = DB::raw("`myperfor`+{$num}");
            $up['total_teamperfor'] = DB::raw("`total_teamperfor`+{$num}");
            User::query()->where('id',$user_id)->update($up);
        } else {
            $up['myperfor'] = DB::raw("`myperfor`-{$num}");
            $up['total_teamperfor'] = DB::raw("`total_teamperfor`-{$num}");
            User::query()->where('id',$user_id)->update($up);
        }
    }


}
