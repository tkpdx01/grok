<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use App\Models\Config;
use App\Models\MyRedis;
use App\Models\GameTeam;
use App\Models\User;
use App\Models\NodePool;
use App\Models\UserUsdt;
use App\Models\WithdrawParent;
use App\Models\Withdraw;
use App\Models\GameOrder;
use App\Models\Luidity;

class SyncWithdraw extends Command
{

    // 自定义脚本命令签名
    protected $signature = 'sync:SyncWithdraw';

    // 自定义脚本命令描述
    protected $description = '异步提币';


    // 创建一个新的命令实例
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lockKey = 'sync:SyncWithdraw';
        $MyRedis = new MyRedis();
//         $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 600);
        if ($lock)
        {
//             $this->benjinCash();
//             $this->tuijianCash();
            $this->chaojiCash();
            $this->chuangshiCash();
            $this->lpCash();
            $MyRedis = new MyRedis();
            $MyRedis->del_lock($lockKey);
        }
    }
    
    //互助本金提币
    public function benjinCash () 
    {
        $time = time();
        $ParentModel = new WithdrawParent();
        
        //互助本金提币
        $list = GameTeam::query()
            ->where('state', 1)
            ->where('is_settle', 1)
            ->where('is_gcash', 0)
            ->get(['id','is_gcash','is_tcash','ordernum'])
            ->toArray();
        if ($list)
        {
            foreach ($list as $val)
            {
                $ordernum = 'BJ'.$val['ordernum'];
                $pWithdraw = WithdrawParent::query()
                    ->where('ordernum', $ordernum)
                    ->where('state', 0)
                    ->first();
                if ($pWithdraw)
                {
                    $orderList = Withdraw::query()
                        ->where('p_id', $pWithdraw->id)
                        ->get(['id','receive_address','usdt'])
                        ->toArray();
                    if ($orderList)
                    {
                        $walletList = [];
                        foreach ($orderList as $oval)
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
                            
                            $distributed =  GameOrder::query()->where('team_id', $val['id'])->where('is_win', 2)->value('join_usdt');   //中奖人参与时的总数量
                            if ($distributed) {
                                $distributed = bcmul($distributed, pow(10, 18), 0);
                            } else {
                                $distributed = $distributed2;
                            }
                            $hash = $ParentModel->lottery($users, $amounts, $ordernum, $distributed);
//                             $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df2487aaa24935420c82';
                            if ($hash)
                            {
                                $pWithdraw->state = 1;
                                $pWithdraw->end_time = date('Y-m-d H:i:s', $time+600);  //10分钟不成功就失败
                                $pWithdraw->hash = $hash;                               //提现hash
                                $pWithdraw->save();
                                
                                GameTeam::query()->where('id', $val['id'])->update(['is_gcash'=>1]);
                            }
                            else
                            {
                                continue;
                            }
                        } 
                    } 
                    else 
                    {
                        $pWithdraw->state = 2;
                        $pWithdraw->save();
                        GameTeam::query()->where('id', $val['id'])->update(['is_gcash'=>1]);
                    }
                } 
                else
                {
                    GameTeam::query()->where('id', $val['id'])->update(['is_gcash'=>1]);
                }
            }
        }
    }
    
    //游戏推荐提币
    public function tuijianCash ()
    {
        $time = time();
        $ParentModel = new WithdrawParent();
        
        //游戏推荐提币
        $list = GameTeam::query()
            ->where('state', 1)
            ->where('is_settle', 1)
            ->where('is_tcash', 0)
            ->get(['id','is_gcash','is_tcash','ordernum'])
            ->toArray();
        if ($list)
        {
            foreach ($list as $val)
            {
                $ordernum = 'TJ'.$val['ordernum'];
                $pWithdraw = WithdrawParent::query()
                    ->where('ordernum', $ordernum)
                    ->where('state', 0)
                    ->first();
                if ($pWithdraw)
                {
                    $orderList = Withdraw::query()
                        ->where('p_id', $pWithdraw->id)
                        ->get(['id','receive_address','usdt'])
                        ->toArray();
                    if ($orderList)
                    {
                        $walletList = [];
                        foreach ($orderList as $oval)
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
                            $users = $amounts = [];
                            foreach ($walletList as $wval)
                            {
                                $users[] = $wval['wallet'];
                                $amounts[] = bcmul($wval['usdt'], pow(10, 18), 0);
                            }
                            $extractedType = 4;
                            $hash = $ParentModel->withdraw($users, $amounts, $ordernum, $extractedType);
                            //                             $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df2487aaa24935420c82';
                            if ($hash)
                            {
                                $pWithdraw->state = 1;
                                $pWithdraw->end_time = date('Y-m-d H:i:s', $time+600);  //10分钟不成功就失败
                                $pWithdraw->hash = $hash;                               //提现hash
                                $pWithdraw->save();
                                
                                GameTeam::query()->where('id', $val['id'])->update(['is_tcash'=>1]);
                            }
                            else
                            {
                                continue;
                            }
                        }
                    } 
                    else 
                    {
                        $pWithdraw->state = 2;
                        $pWithdraw->save();
                        GameTeam::query()->where('id', $val['id'])->update(['is_tcash'=>1]);
                    }
                } 
                else 
                {
                    GameTeam::query()->where('id', $val['id'])->update(['is_tcash'=>1]);
                }
            }
        }
    }
    
    //超级节点提币
    public function chaojiCash ()
    {
        $time = time();
        $ParentModel = new WithdrawParent();
        $date = date('Y-m-d H:i:s', $time);
        
        $cj_pool_limit = bcadd(config('cj_pool_limit'), '0', 6);
        if (bccomp($cj_pool_limit, '0', 6)>0) 
        {
            $NodePool = NodePool::query()->where('id', 1)->first();
            if ($NodePool && bccomp($NodePool->cj_pool, $cj_pool_limit, 6)>=0) 
            {
                $subPool = '0';
                $list = User::query()
                    ->where('usdt_cj', '>', 0)
                    ->get(['id', 'wallet', 'usdt_cj'])
                    ->toArray();
                if ($list) 
                {
                    $withdrawData = $usdtData = [];
                    
                    $list = array_chunk($list, 200);
                    foreach ($list as $ulist) 
                    {
                        $users = $amounts = [];
                        foreach ($ulist as $oval)
                        {
                            $users[] = $oval['wallet'];
                            $amounts[] = bcmul($oval['usdt_cj'], pow(10, 18), 0);
                        }
                        
                        $pordernum = get_ordernum('CJ');
                        $extractedType = 3; //extractedType字段写2或者3，2代表本次提现是在做lp分红，3代表本次提现是在做节点奖励分发
                        $hash = $ParentModel->withdraw($users, $amounts, $pordernum, $extractedType);
//                                                     $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df24879999999999';
                        if ($hash)
                        {
                            
                            $WithdrawParent = new WithdrawParent();
                            $WithdrawParent->ordernum = $pordernum;
                            $WithdrawParent->w_type = 3;    //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                            $WithdrawParent->state = 1;     //状态0待上链1上链中2已完成
                            $WithdrawParent->end_time = date('Y-m-d H:i:s', $time+600);  //10分钟不成功就失败
                            $WithdrawParent->hash = $hash;
                            $WithdrawParent->save();
                            
                            foreach ($ulist as $uval)
                            {
                                $ordernum = get_ordernum('CJ');
                                $withdrawData[] = [
                                    'p_id' => $WithdrawParent->id,
                                    'p_ordernum' => $pordernum,
                                    'ordernum' => $ordernum,
                                    'coin_type' => 1,   //提现币种1USDT,2XHY
                                    'w_type' => 3, //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                    'user_id' => $uval['id'],
                                    'receive_address' => $uval['wallet'],
                                    'usdt' => $uval['usdt_cj'],
                                    'num' => $uval['usdt_cj'],
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ];
                                
                                //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                $usdtData[] = [
                                    'ordernum' => $ordernum,
                                    'user_id' => $uval['id'],
                                    'from_user_id' => 0,
                                    'type' => 2,
                                    'cate' => 12,
                                    'total' => $uval['usdt_cj'],
                                    'msg' => '超级节点提币',
                                    'content' => '超级节点提币',
                                    'game_team_id' => 0,
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ];
                                
                                $subPool = bcadd($subPool, $uval['usdt_cj']);
                                User::query()->where('id', $uval['id'])->decrement('usdt_cj', $uval['usdt_cj']);
                            }
                        }
                    }
                    
                    if ($withdrawData)
                    {
                        $withdrawData = array_chunk($withdrawData, 400);
                        foreach ($withdrawData as $data) {
                            Withdraw::query()->insert($data);
                        }
                    }
                    
                    if ($usdtData)
                    {
                        $usdtData = array_chunk($usdtData, 400);
                        foreach ($usdtData as $data) {
                            UserUsdt::query()->insert($data);
                        }
                    }
                }
                
//                 NodePool::query()->where('id', 1)->update(['cj_pool'=>0]);
                if (bccomp($subPool, '0', 6)>0) {
                    $nUp['cj_pool'] = DB::raw("`cj_pool`-{$subPool}");
                    $nUp['cj_pool_out'] = DB::raw("`cj_pool_out`+{$subPool}");
                    NodePool::query()->where('id', 1)->update($nUp);
                }
            }
        }
    }
    
    //创世节点提币
    public function chuangshiCash ()
    {
        $time = time();
        $ParentModel = new WithdrawParent();
        $date = date('Y-m-d H:i:s', $time);
        
        $cs_pool_limit = bcadd(config('cs_pool_limit'), '0', 6);
        if (bccomp($cs_pool_limit, '0', 6)>0)
        {
            $NodePool = NodePool::query()->where('id', 1)->first();
            if ($NodePool && bccomp($NodePool->cs_pool, $cs_pool_limit, 6)>=0)
            {
                $subPool = '0';
                $list = User::query()
                    ->where('usdt_cs', '>', 0)
                    ->get(['id', 'wallet', 'usdt_cs'])
                    ->toArray();
                if ($list)
                {
                    $withdrawData = $usdtData = [];
                    
                    $list = array_chunk($list, 200);
                    foreach ($list as $ulist)
                    {
                        $users = $amounts = [];
                        foreach ($ulist as $oval)
                        {
                            $users[] = $oval['wallet'];
                            $amounts[] = bcmul($oval['usdt_cs'], pow(10, 18), 0);
                        }
                        
                        $pordernum = get_ordernum('CS');
                        $extractedType = 3; //extractedType字段写2或者3，2代表本次提现是在做lp分红，3代表本次提现是在做节点奖励分发
                        $hash = $ParentModel->withdraw($users, $amounts, $pordernum, $extractedType);
//                                                                             $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df2487aaa666666666666';
                        if ($hash)
                        {
                            
                            $WithdrawParent = new WithdrawParent();
                            $WithdrawParent->ordernum = $pordernum;
                            $WithdrawParent->w_type = 4;    //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                            $WithdrawParent->state = 1;     //状态0待上链1上链中2已完成
                            $WithdrawParent->end_time = date('Y-m-d H:i:s', $time+600);  //10分钟不成功就失败
                            $WithdrawParent->hash = $hash;
                            $WithdrawParent->save();
                            
                            foreach ($ulist as $uval)
                            {
                                $ordernum = get_ordernum('CS');
                                $withdrawData[] = [
                                    'p_id' => $WithdrawParent->id,
                                    'p_ordernum' => $pordernum,
                                    'ordernum' => $ordernum,
                                    'coin_type' => 1,   //提现币种1USDT,2XHY
                                    'w_type' => 4, //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                    'user_id' => $uval['id'],
                                    'receive_address' => $uval['wallet'],
                                    'usdt' => $uval['usdt_cs'],
                                    'num' => $uval['usdt_cs'],
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ];
                                
                                //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                $usdtData[] = [
                                    'ordernum' => $ordernum,
                                    'user_id' => $uval['id'],
                                    'from_user_id' => 0,
                                    'type' => 2,
                                    'cate' => 13,
                                    'total' => $uval['usdt_cs'],
                                    'msg' => '创世节点提币',
                                    'content' => '创世节点提币',
                                    'game_team_id' => 0,
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ];
                                
                                $subPool = bcadd($subPool, $uval['usdt_cs']);
                                User::query()->where('id', $uval['id'])->decrement('usdt_cs', $uval['usdt_cs']);
                            }
                        }
                    }
                    
                    if ($withdrawData)
                    {
                        $withdrawData = array_chunk($withdrawData, 400);
                        foreach ($withdrawData as $data) {
                            Withdraw::query()->insert($data);
                        }
                    }
                    
                    if ($usdtData)
                    {
                        $usdtData = array_chunk($usdtData, 400);
                        foreach ($usdtData as $data) {
                            UserUsdt::query()->insert($data);
                        }
                    }
                        
                    }
                        
//                     NodePool::query()->where('id', 1)->update(['cj_pool'=>0]);
                    if (bccomp($subPool, '0', 6)>0) {
                        $nUp['cs_pool'] = DB::raw("`cs_pool`-{$subPool}");
                        $nUp['cs_pool_total'] = DB::raw("`cs_pool_total`+{$subPool}");
                        NodePool::query()->where('id', 1)->update($nUp);
                    }
            }
        }
    }
        
        //LP池子提现
        public function lpCash ()
        {
            $time = time();
            $ParentModel = new WithdrawParent();
            $date = date('Y-m-d H:i:s', $time);
            
            $lp_pool_limit = bcadd(config('lp_pool_limit'), '0', 6);
            if (bccomp($lp_pool_limit, '0', 6)>0)
            {
                $NodePool = NodePool::query()->where('id', 1)->first();
                if ($NodePool && bccomp($NodePool->lp_pool, $lp_pool_limit, 6)>=0)
                {
                    $lp_pool = $NodePool->lp_pool;
                    $subPool = '0';
                    
                    $list = Luidity::query()
                        ->where('amount', '>', 0)
                        ->get(['user','amount'])
                        ->toArray();
                    if ($list) 
                    {
                        $totalHold = '0';
                        $pow = pow(10, 18);
                        $users = $amounts = $userList = $tmpList = [];
                        $withdrawData = $usdtData = [];
                        
                        foreach ($list as $val) 
                        {
                            $wallet = strtolower($val['user']);
                            $user = User::query()->where('wallet', $wallet)->first(['id', 'wallet']);
                            if ($user) 
                            {
                                $hold = bcdiv($val['amount'], $pow, 10);
                                if (bccomp($hold, '0', 10)>0) {
                                    $tmpList[$user->id] = [
                                        'id' => $user->id,
                                        'wallet' => $user->wallet,
                                        'hold' => $hold
                                    ];
                                    $totalHold = bcadd($totalHold, $hold, 10);
                                }
                            }
                        }
                        
                        //加权分红
                        if ($tmpList && bccomp($totalHold, '0', 10)>0) 
                        {
                            foreach ($tmpList as $val) 
                            {
                                $div = bcdiv($val['hold'], $totalHold, 10);
                                if (bccomp($div, '0', 10)>0) {
                                    $num = bcmul($lp_pool, $div, 6);
                                    if (bccomp($num, '0', 6)>0) {
                                        $userList[] = [
                                            'id' => $val['id'],
                                            'wallet' => $val['wallet'],
                                            'amount' => bcmul($num, $pow, 0),
                                            'num' => $num,
                                        ];
                                    }
                                }
                            }
                        }
                        
                        if ($userList) 
                        {
                            $userList = array_chunk($userList, 200);
                            foreach ($userList as $ulist)
                            {
                                $users = $amounts = [];
                                foreach ($ulist as $oval)
                                {
                                    $users[] = $oval['wallet'];
                                    $amounts[] = $oval['amount'];
                                }
                                
                                $pordernum = get_ordernum('LP');
                                $extractedType = 2; //extractedType字段写2或者3，2代表本次提现是在做lp分红，3代表本次提现是在做节点奖励分发
                                $hash = $ParentModel->withdraw($users, $amounts, $pordernum, $extractedType);
//                                                                                     $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df8888888888';
                                if ($hash)
                                {
                                    
                                    $WithdrawParent = new WithdrawParent();
                                    $WithdrawParent->ordernum = $pordernum;
                                    $WithdrawParent->w_type = 5;    //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                    $WithdrawParent->state = 1;     //状态0待上链1上链中2已完成
                                    $WithdrawParent->end_time = date('Y-m-d H:i:s', $time+600);  //10分钟不成功就失败
                                    $WithdrawParent->hash = $hash;
                                    $WithdrawParent->save();
                                    
                                    foreach ($ulist as $uval)
                                    {
                                        $ordernum = get_ordernum('LP');
                                        $withdrawData[] = [
                                            'p_id' => $WithdrawParent->id,
                                            'p_ordernum' => $pordernum,
                                            'ordernum' => $ordernum,
                                            'coin_type' => 1,   //提现币种1USDT,2XHY
                                            'w_type' => 5, //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                            'user_id' => $uval['id'],
                                            'receive_address' => $uval['wallet'],
                                            'usdt' => $uval['num'],
                                            'num' => $uval['num'],
                                            'created_at' => $date,
                                            'updated_at' => $date,
                                        ];
                                        $subPool = bcadd($subPool, $uval['num']);
                                        
                                        //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                        $usdtData[] = [
                                            'ordernum' => $ordernum,
                                            'user_id' => $uval['id'],
                                            'from_user_id' => 0,
                                            'type' => 1,
                                            'cate' => 10,
                                            'total' => $uval['num'],
                                            'msg' => 'LP分红奖励',
                                            'content' => 'LP分红奖励',
                                            'game_team_id' => 0,
                                            'created_at' => $date,
                                            'updated_at' => $date,
                                        ];
                                        $usdtData[] = [
                                            'ordernum' => $ordernum,
                                            'user_id' => $uval['id'],
                                            'from_user_id' => 0,
                                            'type' => 2,
                                            'cate' => 15,
                                            'total' => $uval['num'],
                                            'msg' => 'LP分红提币',
                                            'content' => 'LP分红提币',
                                            'game_team_id' => 0,
                                            'created_at' => $date,
                                            'updated_at' => $date,
                                        ];
                                        
                                    }
                                }
                            }
                        }
                        
                        if ($withdrawData)
                        {
                            $withdrawData = array_chunk($withdrawData, 400);
                            foreach ($withdrawData as $data) {
                                Withdraw::query()->insert($data);
                            }
                        }
                        
                        if ($usdtData)
                        {
                            $usdtData = array_chunk($usdtData, 400);
                            foreach ($usdtData as $data) {
                                UserUsdt::query()->insert($data);
                            }
                        }
                        
                        if (bccomp($subPool, '0', 6)>0) {
                            $nUp['lp_pool'] = DB::raw("`lp_pool`-{$subPool}");
                            $nUp['lp_pool_out'] = DB::raw("`lp_pool_out`+{$subPool}");
                            NodePool::query()->where('id', 1)->update($nUp);
                        }
                    }
                }
            }
        }
    
}
