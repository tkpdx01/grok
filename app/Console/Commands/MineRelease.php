<?php

namespace App\Console\Commands;

use App\Models\BattleLog;
use App\Models\MachineSpeedDate;
use App\Models\UserMachine;
use App\Models\UsersCoin;
use App\Models\UserUsdt;
use Illuminate\Console\Command;

class MineRelease extends Command
{

    // 自定义脚本命令签名
    protected $signature = 'sync:MineRelease';

    // 自定义脚本命令描述
    protected $description = '对战冻结释放';


    // 创建一个新的命令实例
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = date('Ymd');
        $list = BattleLog::query()->where('status', 2)
            ->where('status', 2)
            ->where('battle_result', 3)
            ->where('release_date', '<', $date)
            ->where('reset', '>', 0)
            ->whereDate('created_at', '<', date('Y-m-d'))
            ->limit(2000)
            ->get();
        if ($list->isEmpty()) {
            return;
        }
        $profitRate = config('release_profit');
        if ($profitRate <= 0) {
            return;
        }
        foreach ($list as $item) {
            $profit = bcmul($item->total, $profitRate, 2) / 100;
            if ($profit < 0.1) {
                continue;
            }
            $profit >= $item->reset ? $award = $item->reset : $award = $profit;

            UsersCoin::monsIncome($item->user_id, 1, $award, 4, '战败释放');
            if ($profit >= $item->reset) {
                $item->status = 3;
                $item->reset  = 0;
            } else {
                $item->reset = bcsub($item->reset, $award, 2);
            }
            $item->release_date = $date;
            $item->save();
        }
    }

}
