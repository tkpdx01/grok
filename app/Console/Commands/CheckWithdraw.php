<?php
namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Config;
use App\Models\MyRedis;
use App\Models\UserUsdt;
use App\Models\WithdrawParent;
use App\Models\Withdraw;
use App\Models\User;
use App\Models\Extracted;
use App\Models\GameOrder;
use App\Models\WithdrawRepeatLog;

class CheckWithdraw extends Command
{

    // 自定义脚本命令签名
    protected $signature = 'sync:CheckWithdraw';

    // 自定义脚本命令描述
    protected $description = '检查提币订单';


    // 创建一个新的命令实例
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lockKey = 'sync:CheckWithdraw';
        $MyRedis = new MyRedis();
//                 $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 180);
        if ($lock)
        {
            //应该是22次起手续费50%
            $not_active_wnum = 21;
            $time = time();
            $date = date('Y-m-d H:i:s', $time);
            
            $pWithdrawList = WithdrawParent::query()
                ->where('state', '=', 1)    //状态0待上链1上链中2已完成
                ->get(['id','ordernum','w_type','state','end_time','game_team_id','hash'])
                ->toArray();
            if ($pWithdrawList) 
            {
                $ParentModel = new WithdrawParent();
                $userModel = new User();
                $repeatLog = [];
                
                foreach ($pWithdrawList as $val) 
                {
                    $extracted = Extracted::query()
                        ->where('round', $val['ordernum'])
                        ->first();
                    //提币完成
                    if ($extracted) 
                    {
                        WithdrawParent::query()
                            ->where('id', $val['id'])
                            ->update(['state'=>2,'finsh_time'=>$date,'hash'=>$extracted->hash]);
                        Withdraw::query()
                            ->where('p_id', $val['id'])
                            ->update(['status'=>1,'finsh_time'=>$date,'hash'=>$extracted->hash]);
                        if ($val['w_type']==6) {
                            $withdrawInfo = Withdraw::query()->where('p_id', $val['id'])->first(['id','user_id','is_active']);
                            if ($withdrawInfo) {
                                $userInfo = User::query()->where('id', $withdrawInfo->user_id)->first(['id','not_active_wnum']);
                                if ($userInfo) 
                                {
                                    if ($userInfo->not_active_wnum>=$not_active_wnum) {
                                        User::query()->where('id', $userInfo->id)->increment('not_active_wnum', 1);
                                    } else {
                                        if ($withdrawInfo->is_active==1) {
                                            User::query()->where('id', $userInfo->id)->update(['not_active_wnum'=>0]);
                                        } else {
                                            User::query()->where('id', $userInfo->id)->increment('not_active_wnum', 1);
                                        }
                                    }
                                }
                            }
                        }
                    } 
                    else 
                    {
                        $old_hash = $val['hash'];
                        //判断是否超过时间
                        if ($val['end_time']<=$date) 
                        {
                            if ($val['w_type']==6) //个人提现,退回到余额
                            {
                                $wList = Withdraw::query()
                                    ->where('p_id', $val['id'])
                                    ->get(['id','ordernum','w_type','user_id','usdt'])
                                    ->toArray();
                                if ($wList) {
                                    foreach ($wList as $order)
                                    {
                                        //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                        $cates = ['cate'=>3, 'msg'=>'提币失败', 'ordernum'=>$order['ordernum']];
                                        $userModel->handleUser('usdt', $order['user_id'], $order['usdt'], 1, $cates);
                                        User::query()->where('id', $order['user_id'])->decrement('machine_cash_usdt', $order['usdt']);
                                    }
                                    Withdraw::query()->where('p_id', $val['id'])->update(['status'=>2]);
                                }
                                WithdrawParent::query()->where('id', $val['id'])->update(['state' => 3]);
                            } 
                            else    //重新发起提币
                            {
                                //组装数据
                                $wList = Withdraw::query()
                                    ->where('p_id', $val['id'])
                                    ->get(['id','ordernum','w_type','user_id','usdt','receive_address'])
                                    ->toArray();
                                if ($wList) 
                                {
                                    $walletList = [];
                                    foreach ($wList as $oval)
                                    {
                                        if (!isset($walletList[$oval['receive_address']])) {
                                            $walletList[$oval['receive_address']] = [
                                                'wallet' => $oval['receive_address'],
                                                'usdt' => '0'
                                            ];
                                        }
                                        $walletList[$oval['receive_address']]['usdt'] = bcadd($walletList[$oval['receive_address']]['usdt'], $oval['usdt'], 6);
                                    }
                                    
                                    if ($walletList)
                                    {
                                        $distributed2 = '0';
                                        $users = $amounts = [];
                                        foreach ($walletList as $wval)
                                        {
                                            $users[] = $wval['wallet'];
                                            $amounts[] = $distributed2 = bcmul($wval['usdt'], pow(10, 18), 0);
                                        }
                                        
                                        $hash = '';
                                        if ($val['w_type']==1)  //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                        {
                                            if ($val['game_team_id']>0) 
                                            {
                                                $distributed =  GameOrder::query()->where('team_id', $val['game_team_id'])->where('is_win', 2)->value('join_usdt');   //中奖人参与时的总数量
                                                if ($distributed) {
                                                    $distributed = bcmul($distributed, pow(10, 18), 0);
                                                } else {
                                                    $distributed = $distributed2;
                                                }
                                                $hash = $ParentModel->lottery($users, $amounts, $val['ordernum'], $distributed);
                                            }
                                        } 
                                        else 
                                        {
                                            //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                            //extractedType字段写2或者3，2代表本次提现是在做lp分红，3代表本次提现是在做节点奖励分发
                                            if ($val['w_type']==2) {
                                                $extractedType = 4;
                                            } else if ($val['w_type']==5) {
                                                $extractedType = 2;
                                            } else {
                                                $extractedType = 3;
                                            }
                                            $hash = $ParentModel->withdraw($users, $amounts, $val['ordernum'], $extractedType);
                                        }
//                                                                     $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df2487a888888888';
                                        if ($hash)
                                        {
                                            WithdrawParent::query()->where('id', $val['id'])->update([
                                                'end_time' => date('Y-m-d H:i:s', $time+600),
                                                'hash' => $hash,
                                                'is_repeat' => 1,
                                                'repeat_num' => DB::raw("`repeat_num`+1")
                                            ]);
                                            $repeatLog[] = [
                                                'withdraw_parent_id' => $val['id'],
                                                'ordernum' => $val['ordernum'],
                                                'old_hash' => $old_hash,
                                                'new_hash' => $hash,
                                                'created_at' => $date,
                                                'updated_at' => $date,
                                            ];
                                        }
                                    } 
                                }
                            }
                        }
                    }
                }
                
                if ($repeatLog) {
                    $repeatLog = array_chunk($repeatLog, 400);
                    foreach ($repeatLog as $data) {
                        WithdrawRepeatLog::query()->insert($data);
                    }
                }
            }
            
            $MyRedis = new MyRedis();
            $MyRedis->del_lock($lockKey);
        }
    }
}
