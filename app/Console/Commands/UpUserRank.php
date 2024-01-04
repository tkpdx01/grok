<?php
namespace App\Console\Commands;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Config;
use App\Models\RankConfig;
use App\Models\MyRedis;
use App\Models\GameOrder;

class UpUserRank extends Command
{

    // 自定义脚本命令签名
    protected $signature = 'sync:UpUserRank';

    // 自定义脚本命令描述
    protected $description = '同步用户等级';


    // 创建一个新的命令实例
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lockKey = 'sync:UpUserRank';
        $MyRedis = new MyRedis();
//                 $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 180);
        if ($lock)
        {
            $time = time();
            $yTime = $time-86400;
            
            $RankModel = new RankConfig();
            $rankConfig = RankConfig::GetListCache();
            if ($rankConfig) {
                $rankConfig = array_column($rankConfig, null, 'lv');
            }
            
            //更新用户等级  每个账号每天拍3次为活跃账号（24小时），享受互助推荐奖励，未活跃账号互助推荐奖励通缩到公司号。
            $userList = User::query()
                ->where('is_active', 1)
                ->get(['id','path'])
                ->toArray();
            if ($userList && $rankConfig)
            {
                foreach ($userList as $user) 
                {
                    $oNum = GameOrder::query()
                        ->where('user_id', $user['id'])
                        ->where('created_at', '>', date('Y-m-d H:i:s', $yTime))
                        ->count();
                    if ($oNum<3) {
                        User::query()->where('id', $user['id'])->update(['is_active'=>0]);
                        $RankModel->upUserRank($user, $rankConfig);
                    }
                }
            }
            
            $MyRedis = new MyRedis();
            $MyRedis->del_lock($lockKey);
        }
    }
}
