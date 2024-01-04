<?php
namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Config;
use App\Models\MyRedis;
use App\Models\Recharged;
use App\Models\SyncGameRecharge;
use App\Models\GameTeam;
use App\Models\GameOrder;
use App\Models\UserTicket;
use App\Models\RankConfig;
use App\Models\NodePool;

use App\Models\UserMachine;
use App\Models\UserUsdt;
use App\Models\WithdrawParent;
use App\Models\Withdraw;
use App\Models\Luidity;


class SyncGameJoin extends Command
{

    // 自定义脚本命令签名
    protected $signature = 'sync:SyncGameJoin';

    // 自定义脚本命令描述
    protected $description = '用户组团';

    // 创建一个新的命令实例
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lockKey = 'sync:SyncGameJoin';
        $MyRedis = new MyRedis();
//                 $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 600);
        if ($lock)
        {
            $time = time();
            $datetime = date('Y-m-d H:i:s', $time);
            
            $RankModel = new RankConfig();
            $rankConfig = RankConfig::GetListCache();
            if ($rankConfig) {
                $rankConfig = array_column($rankConfig, null, 'lv');
            }
            
//             //更新用户等级  每个账号每天拍3次为活跃账号（24小时），享受互助推荐奖励，未活跃账号互助推荐奖励通缩到公司号。
//             $overUserList = User::query()
//                 ->where('is_active', 1)
//                 ->where('active_etime', '<=', $datetime)
//                 ->get(['id','path'])
//                 ->toArray();
//             if ($overUserList && $rankConfig) 
//             {
//                 $overIds = array_column($overUserList, 'id'); 
//                 $overIds = array_chunk($overIds, 400);
//                 foreach ($overIds as $oids) {
//                     User::query()->whereIn('id', $oids)->update(['is_active'=>0]);
//                 }
//                 foreach ($overUserList as $ouser) {
//                     $RankModel->upUserRank($ouser, $rankConfig);
//                 }
//             }
            
            
            $gameRecharge = SyncGameRecharge::query()->where('id', 1)->first(['id','recharge_id']);
            if (!$gameRecharge) {
                $gameRecharge = new SyncGameRecharge();
                $gameRecharge->id = 1;
                $gameRecharge->save();
            }
            $recharge_id  = $gameRecharge->recharge_id;
            
            $list = Recharged::query()
                ->where('id', '>', $recharge_id)
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
            if ($list) 
            {
                $team_num = 11;         //11人参与
                $join_ticket = 1;       //消耗一张门票
                $join_usdt = '100';
//                 $join_usdt = '1';
                $newOrder = $userList = $ticketData = [];
                
                $hyDay = 1;     //活跃时间增加1天
                $hyTime = $hyDay*86400;
                
                $userModel = new User();
                $pow = pow(10, 18);
                foreach ($list as $val) 
                {
                    $recharge_id = $val['id'];
                    $wallet = strtolower($val['user']);
                    $user = User::query()->where('wallet', $wallet)->first(['id','is_active','is_effective','wallet','path']);
                    $amount = @bcdiv($val['amount'], $pow, 6);
                    if ($user && bccomp($amount, $join_usdt, 6)>=0) 
                    {
                        $user_id = $user->id;
                        
                        //金额需要除18位
                        $joinTime = date('Y-m-d H:i:s', $val['time']);
                        
                        $state = 0;
                        
                        //查看有没有未满的团 有就补满
                        $waitTeam = GameTeam::query()->where('state', 0)->first(['id','team_num','join_num','total_benjin']);
                        if ($waitTeam)
                        {
                            $teamId = $waitTeam->id;
                            $waitTeam->join_num = $waitTeam->join_num+1;
                            $waitTeam->total_benjin = bcadd($waitTeam->total_benjin, $amount, 6);
                            if ($waitTeam->join_num>=$waitTeam->team_num) { //满团
                                $waitTeam->state = 1;
                            }
                            $waitTeam->save();
                        }
                        else
                        {
                            $gameTeam = new GameTeam();
                            $gameTeam->team_num = $team_num;    //成团人数
                            $gameTeam->join_num = 1;            //参与人数
                            $gameTeam->join_ticket = $join_ticket;
                            $gameTeam->total_benjin = $amount;
                            $gameTeam->ordernum = get_ordernum();
                            $gameTeam->save();
                            $teamId = $gameTeam->id;
                        }
                        
                        if (!isset($userList[$user_id])) {
                            $userList[$user_id] = [
                                'id' => $user_id,
                                'user_id' => $user_id,
                                'ticket' => 0,
                                'tday_join' => 0,
                                'is_active' => $user->is_active,
                                'is_effective' => $user->is_effective,
                                'active_etime' => date('Y-m-d H:i:s', $val['time']+$hyTime),
                                'path' => $user->path,
                            ];
                        }
                        
                        $userList[$user_id]['ticket'] = $userList[$user_id]['ticket']+$join_ticket;
                        $userList[$user_id]['tday_join'] = $userList[$user_id]['tday_join']+1;
                        $userList[$user_id]['active_etime'] = date('Y-m-d H:i:s', $val['time']+$hyTime);
                        
                        $ordernum = get_ordernum();
                        $newOrder[] = [
                            'ordernum' => $ordernum,
                            'user_id' => $user_id,
                            'team_id' => $teamId,
                            'join_usdt' => $amount,
                            'join_ticket' => $join_ticket,
                            'created_at' => $joinTime,
                            'updated_at' => $joinTime
                        ];
                        
                        //分类1后台操作2购买门票3互助拼团4赠予扣除5赠予获得
                        $ticketData[] = [
                            'ordernum' => $ordernum,
                            'user_id' => $user_id,
                            'from_user_id' => 0,
                            'type' => 2,
                            'cate' => 3,
                            'total' => $join_ticket,
                            'msg' => '互助拼团',
                            'content' => '互助拼团',
                            'created_at' => $joinTime,
                            'updated_at' => $joinTime,
                        ];
                        
                        //业绩
                        $userModel->handleAchievement($user_id, $amount);
                        if ($userList[$user_id]['path']) {
                            $userModel->handlePerformance($userList[$user_id]['path'], $amount);
                        }
                    }
                }
                
                //更新同步ID下标
                SyncGameRecharge::query()->where('id', 1)->update(['recharge_id'=>$recharge_id]);
                
                if ($newOrder)
                {
                    $newOrder = array_chunk($newOrder, 400);
                    foreach ($newOrder as $ndata) {
                        GameOrder::query()->insert($ndata);
                    }
                }
                
                if ($userList)
                {
                    foreach ($userList as $val) 
                    {
                        $uup = [];
                        $uup['active_etime'] = $val['active_etime'];
                        $uup['tday_join'] = DB::raw("`tday_join`+{$val['tday_join']}");
                        $uup['ticket'] = DB::raw("`ticket`-{$val['ticket']}");
                        
                        //更新用户等级  每个账号每天拍3次为活跃账号（24小时），享受互助推荐奖励，未活跃账号互助推荐奖励通缩到公司号
                        $is_active = 0;
                        $yTime = $time-86400;
                        $oNum = GameOrder::query()
                            ->where('user_id', $val['user_id'])
                            ->where('created_at', '>', date('Y-m-d H:i:s', $yTime))
                            ->count();
                        if ($oNum>=3) {
                            $is_active = 1;
                        }
                        if ($val['is_active']!=$is_active) {
                            $uup['is_active'] = 1;
                        }
                        if ($val['is_effective']==0) {
                            $uup['is_effective'] = 1;
                        }
                        
                        User::query()->where('id', $val['user_id'])->update($uup);
                        //更新等级
                        if ($rankConfig) {
                            $RankModel->upUserRank($val, $rankConfig);
                        }
                    }
                }
                
                if ($ticketData)
                {
                    $ticketData = array_chunk($ticketData, 400);
                    foreach ($ticketData as $ndata) {
                        UserTicket::query()->insert($ndata);
                    }
                }
            }
            
            //游戏结算
            $this->GameSettle();
            
            $MyRedis = new MyRedis();
            $MyRedis->del_lock($lockKey);
        }
    }
    
    //游戏结算
    public function GameSettle() 
    {
        $time = time();
        $date = date('Y-m-d H:i:s', $time);
        $release_time = date('Y-m-d H:i:s', $time+86400);   //24小时制释放
        $list = GameTeam::query()
            ->where('state', 1)
            ->where('is_settle', 0)
            ->get()
            ->toArray();
        
        if ($list)
        {
            $notwinRate = @bcadd(config('notwin_rate'), '0', 6);
            $tuijianRate = @bcadd(config('tuijian_rate'), '0', 6);
            $cjNodeRate = @bcadd(config('cj_node_rate'), '0', 6);
            $csNodeRate = @bcadd(config('cs_node_rate'), '0', 6);
            $lpPoolRate = @bcadd(config('lp_pool_rate'), '0', 6);
            $machineRate = @bcadd(config('machine_rate'), '0', 6);
            $machineDayRate = @bcadd(config('machine_day_rate'), '0', 6);
            
            $cjNodePool = $csNodePool = $lpPool = '0';
            
            $usdtData = $machineData = $userList = $gameWithdraw = $tuijianWithdraw  = [];
            //游戏系统每人100u参与全球公排，够11单爆仓并有1单随机中奖，
            //未中奖账户103u秒回钱包，
            //推荐人奖励1u，
            //中奖账号得200u保价资产包（矿机）每天自然释放1u。
            //每个账号每天拍3次为活跃账号（24小时），享受互助推荐奖励，未活跃账号互助推荐奖励通缩到公司号。
            
            foreach ($list as $val)
            {
                $gameWordernum = 'BJ'.$val['ordernum'];  //互助提币总订单号
                $gameTordernum = 'TJ'.$val['ordernum'];  //互助推荐提币总订单号
                $gameWithdraw[$gameWordernum] = [];
                $tuijianWithdraw[$gameTordernum] = [];
                
                $total_fan_benjin = $total_fan_jingtai = $total_fan_tuijian = $total_fan_lp = $total_fan_cj = $total_fan_cs = $total_residue = '0';
                
                $winOid = $winUid = 0;
                $orderList = GameOrder::query()
                    ->where('team_id', $val['id'])
                    ->get(['id','ordernum','user_id','team_id','join_usdt'])
                    ->toArray();
                if ($orderList)
                {
                    //获取中奖订单ID 没有白名单内的
                    $winKey = array_rand($orderList);
                    $winOid = $orderList[$winKey]['id'];
                    $winUid = $orderList[$winKey]['user_id'];
                    $csnode_reward = '0';   //创世节点奖励
                    
                    foreach ($orderList as $order)
                    {
                        if (!isset($userList[$order['user_id']]))
                        {
                            $user = User::query()->where('id', $order['user_id'])->first(['id','level','is_active','rank','parent_id','wallet','path']);
                            if (!$user) {
                                continue;
                            }
                            $userList[$order['user_id']] = [
                                'user_id' => $order['user_id'],
                                'is_active' => $user->is_active,
                                'rank' => $user->rank,
                                'parent_id' => $user->parent_id,
                                'path' => $user->path,
                                'usdt_cj' => '0',
                                'usdt_cs' => '0',
                                'wallet' => $user->wallet,
                            ];
                        }
                        
                        //返还本金
                        if ($winOid!=$order['id'])
                        {
                            if (bccomp($order['join_usdt'], '0', 6)>0)
                            {
                                //返还总本金
                                $total_fan_benjin = bcadd($total_fan_benjin, $order['join_usdt'], 6);
                                
                                //互助提币订单号
                                $gameWithdrawItem = [
                                    'user_id' => $order['user_id'],
                                    'wallet' => $userList[$order['user_id']]['wallet'],
                                    'usdt' => $order['join_usdt'],
                                    'game_team_id' => $val['id'],
                                    'ordernum' => $order['ordernum'],
                                ];
                                
                                //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                $usdtData[] = [
                                    'ordernum' => $order['ordernum'],
                                    'user_id' => $order['user_id'],
                                    'from_user_id' => 0,
                                    'type' => 1,
                                    'cate' => 5,
                                    'total' => $order['join_usdt'],
                                    'msg' => '互助本金',
                                    'game_team_id' => $val['id'],
                                    'content' => '互助本金',
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ];
                                
                                $lpNum = bcmul($order['join_usdt'], $lpPoolRate, 6);
                                if (bccomp($lpNum, '0', 6)>0) {
                                    $lpPool = bcadd($lpPool, $lpNum, 6);
                                    //总注入LP池
                                    $total_fan_lp = bcadd($total_fan_lp, $lpNum, 6);
                                }
                                
                                //未中奖奖励
                                $notwin_reward = bcmul($order['join_usdt'], $notwinRate, 6);
                                if (bccomp($notwin_reward, '0', 6)>0)
                                {
                                    //返还总静态
                                    $total_fan_jingtai = bcadd($total_fan_jingtai, $notwin_reward, 6);
                                    
                                    //互助提币订单号
                                    $gameWithdrawItem['usdt'] = bcadd($gameWithdrawItem['usdt'], $notwin_reward, 6);
                                    
                                    //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                    $usdtData[] = [
                                        'ordernum' => $order['ordernum'],
                                        'user_id' => $order['user_id'],
                                        'from_user_id' => 0,
                                        'type' => 1,
                                        'cate' => 6,
                                        'total' => $notwin_reward,
                                        'msg' => '互助奖励',
                                        'content' => '互助奖励',
                                        'game_team_id' => $val['id'],
                                        'created_at' => $date,
                                        'updated_at' => $date,
                                    ];
                                }
                                $gameWithdraw[$gameWordernum][] = $gameWithdrawItem;
                                
                                //推荐人奖励
                                $tuijian_reward = bcmul($order['join_usdt'], $tuijianRate, 6);
                                if ($userList[$order['user_id']]['parent_id']>0 && bccomp($tuijian_reward, '0', 6)>0)
                                {
                                    $parent_id = $userList[$order['user_id']]['parent_id'];
                                    if (!isset($userList[$parent_id]))
                                    {
                                        $puser = User::query()->where('id', $parent_id)->first(['id','level','is_active','rank','parent_id','wallet','path']);
                                        if ($puser)
                                        {
                                            $userList[$parent_id] = [
                                                'user_id' => $parent_id,
                                                'is_active' => $puser->is_active,
                                                'rank' => $puser->rank,
                                                'parent_id' => $puser->parent_id,
                                                'path' => $puser->path,
                                                'usdt_cj' => '0',
                                                'usdt_cs' => '0',
                                                'wallet' => $puser->wallet,
                                            ];
                                        }
                                    }
                                    
                                    //每个账号每天拍3次为活跃账号（24小时），享受互助推荐奖励，未活跃账号互助推荐奖励通缩到公司号。
                                    if (isset($userList[$parent_id]) && $userList[$parent_id]['is_active']==1)
                                    {
                                        //返还总推荐
                                        $total_fan_tuijian = bcadd($total_fan_tuijian, $tuijian_reward, 6);
                                        
                                        //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                        $usdtData[] = [
                                            'ordernum' => $order['ordernum'],
                                            'user_id' => $parent_id,
                                            'from_user_id' => $order['user_id'],
                                            'type' => 1,
                                            'cate' => 7,
                                            'total' => $tuijian_reward,
                                            'msg' => '互助推荐奖励',
                                            'content' => '互助推荐奖励',
                                            'game_team_id' => $val['id'],
                                            'created_at' => $date,
                                            'updated_at' => $date,
                                        ];
                                        
                                        //互助提币订单号
                                        $tuijianWithdraw[$gameTordernum][] = [
                                            'user_id' => $parent_id,
                                            'wallet' => $userList[$parent_id]['wallet'],
                                            'usdt' => $tuijian_reward,
                                            'game_team_id' => $val['id'],
                                            'ordernum' => $order['ordernum'],
                                        ];
                                    }
                                }
                                
                                //超级节点 直推10个活跃账号伞下1000个活跃账号享受伞下每拍1次的奖励(给最近的一个)
                                $cjnode_reward = bcmul($order['join_usdt'], $cjNodeRate, 6);
                                if ($userList[$order['user_id']]['path'] && bccomp($cjnode_reward, '0', 6)>0)
                                {
                                    $parentIds = explode('-',trim($user['path'],'-'));
                                    $parentIds = array_reverse($parentIds);
                                    $parentIds = array_filter($parentIds);
                                    if ($parentIds)
                                    {
                                        $cjUser = User::query()
                                            ->whereIn('id', $parentIds)
                                            ->where('rank', 1)  //超级节点
                                            ->orderBy('level', 'desc')
                                            ->first(['id','level','is_active','rank','parent_id','path','path']);
                                        if ($cjUser)
                                        {
                                            //总返超级节点
                                            $total_fan_cj = bcadd($total_fan_cj, $cjnode_reward, 6);
                                            
                                            if (!isset($userList[$cjUser->id])) {
                                                $userList[$cjUser->id] = [
                                                    'user_id' => $cjUser->id,
                                                    'is_active' => $cjUser->is_active,
                                                    'rank' => $cjUser->rank,
                                                    'parent_id' => $cjUser->parent_id,
                                                    'path' => $cjUser->path,
                                                    'usdt_cj' => '0',
                                                    'usdt_cs' => '0',
                                                    'wallet' => $cjUser->wallet,
                                                ];
                                            }
                                            $userList[$cjUser->id]['usdt_cj'] = bcadd($userList[$cjUser->id]['usdt_cj'], $cjnode_reward, 6);
                                            
                                            //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点奖励9创世节点奖励10LP分红奖励11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                            $usdtData[] = [
                                                'ordernum' => $order['ordernum'],
                                                'user_id' => $cjUser->id,
                                                'from_user_id' => $order['user_id'],
                                                'type' => 1,
                                                'cate' => 8,
                                                'total' => $cjnode_reward,
                                                'msg' => '超级节点奖励',
                                                'content' => '超级节点奖励',
                                                'game_team_id' => $val['id'],
                                                'created_at' => $date,
                                                'updated_at' => $date,
                                            ];
                                            
                                            //超级节点池子
                                            $cjNodePool = bcadd($cjNodePool, $cjnode_reward, 6);
                                        }
                                    }
                                }
                                
                                //创世节点 享受全网每拍一次奖励（平均分配）
                                $tmpReward = bcmul($order['join_usdt'], $csNodeRate, 6);
                                $csnode_reward = bcadd($csnode_reward, $tmpReward, 6);
                                
                                GameOrder::query()
                                    ->where('id', $order['id'])
                                    ->update([
                                        'is_win'=>1,
                                        'not_reward'=>$notwin_reward,
                                        'state' => 1
                                    ]);
                                
                            }
                        }
                        else     //中奖名单 赠送矿机
                        {
                            $wUp = [];
                            $wUp['is_win'] = 2;
                            $wUp['state'] = 1;
                            GameOrder::query()->where('id', $winOid)->update($wUp);
                            
                            $win_reward = bcmul($order['join_usdt'], $machineRate, 6);
                            if (bccomp($win_reward, '0', 6)>0 && bccomp($machineDayRate, '0', 6)>0)
                            {
                                $machineData[] = [
                                    'ordernum' => $order['ordernum'],
                                    'user_id' => $winUid,
                                    'total' => $win_reward,
                                    'residue_total' => $win_reward,
                                    'rate' => $machineDayRate,
                                    'release_time' => $release_time,
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ];
                                
                                //累计中奖资产
                                User::query()->where('id', $winUid)->increment('machine_win_total', $win_reward);
                            }
                        }
                    }
                    
                    //创世界节点池子
                    if (bccomp($csnode_reward, '0', 6)>0)
                    {
                        $csUserlist = User::query()
                            ->where('rank', 2)
                            ->get(['id','level','is_active','rank','parent_id','wallet','path'])
                            ->toArray();
                        if ($csUserlist) 
                        {
                            $csNum = count($csUserlist);
                            $avgCs = bcdiv($csnode_reward, $csNum, 6);
                            if (bccomp($avgCs, '0', 6)>0)
                            {
                                //总返创世节点
                                $total_fan_cs = bcmul($avgCs, $csNum, 6);
                                
                                foreach ($csUserlist as $csUser)
                                {
                                    if (!isset($userList[$csUser['id']])) {
                                        $userList[$csUser['id']] = [
                                            'user_id' => $csUser['id'],
                                            'is_active' => $csUser['is_active'],
                                            'rank' => $csUser['rank'],
                                            'parent_id' => $csUser['parent_id'],
                                            'path' => $csUser['path'],
                                            'usdt_cj' => '0',
                                            'usdt_cs' => '0',
                                            'wallet' => $csUser['wallet'],
                                        ];
                                    }
                                    $userList[$csUser['id']]['usdt_cs'] = bcadd($userList[$csUser['id']]['usdt_cs'], $avgCs, 6);
                                    
                                    //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                                    $usdtData[] = [
                                        'ordernum' => $order['ordernum'],
                                        'user_id' => $csUser['id'],
                                        'from_user_id' => 0,
                                        'type' => 1,
                                        'cate' => 9,
                                        'total' => $avgCs,
                                        'msg' => '创世节点奖励',
                                        'content' => '创世节点奖励',
                                        'game_team_id' => $val['id'],
                                        'created_at' => $date,
                                        'updated_at' => $date,
                                    ];
                                    //创世节点池子
                                    $csNodePool = bcadd($csNodePool, $avgCs, 6);
                                }
                            }
                        }
                    }
                }
                
                //更新组团状态
                $gUp = [
                    'is_settle'=>1, 
                    'kj_time'=>date('Y-m-d H:i:s'),
                    'total_fan_benjin'=>$total_fan_benjin,
                    'total_fan_jingtai'=>$total_fan_jingtai,
                    'total_fan_tuijian'=>$total_fan_tuijian,
                    'total_fan_lp'=>$total_fan_lp,
                    'total_fan_cj'=>$total_fan_cj,
                    'total_fan_cs'=>$total_fan_cs,
                ];
                $total_tmp = bcadd($total_fan_benjin, $total_fan_jingtai, 6);
                $total_tmp = bcadd($total_tmp, $total_fan_tuijian, 6);
                $total_tmp = bcadd($total_tmp, $total_fan_lp, 6);
                $total_tmp = bcadd($total_tmp, $total_fan_cj, 6);
                $total_tmp = bcadd($total_tmp, $total_fan_cs, 6);
                $total_residue = bcsub($val['total_benjin'], $total_tmp, 6);
                $gUp['total_residue'] = $total_residue;
                
                if (!$gameWithdraw[$gameWordernum]) {
                    $gUp['is_gcash'] = 1;
                }
                if (!$tuijianWithdraw[$gameTordernum]) {
                    $gUp['is_tcash'] = 1;
                }
                GameTeam::query()->where('id', $val['id'])->update($gUp);
            }
            
            if ($userList)
            {
                foreach ($userList as $vuser)
                {
                    $uUp = [];
                    if (bccomp($vuser['usdt_cj'], '0', 6)>0) {
                        $uUp['usdt_cj'] = DB::raw("`usdt_cj`+{$vuser['usdt_cj']}");
                    }
                    if (bccomp($vuser['usdt_cs'], '0', 6)>0) {
                        $uUp['usdt_cs'] = DB::raw("`usdt_cs`+{$vuser['usdt_cs']}");
                    }
                    if ($uUp) {
                        User::query()->where('id', $vuser['user_id'])->update($uUp);
                    }
                }
            }
            
            if ($machineData)
            {
                $machineData = array_chunk($machineData, 400);
                foreach ($machineData as $data) {
                    UserMachine::query()->insert($data);
                }
            }
            
            //节点池子
            $nUp = [];
            if (bccomp($cjNodePool, '0', 6)>0) {
                $nUp['cj_pool'] = DB::raw("`cj_pool`+{$cjNodePool}");
                $nUp['cj_pool_total'] = DB::raw("`cj_pool_total`+{$cjNodePool}");
            }
            if (bccomp($csNodePool, '0', 6)>0) {
                $nUp['cs_pool'] = DB::raw("`cs_pool`+{$csNodePool}");
                $nUp['cs_pool_total'] = DB::raw("`cs_pool_total`+{$csNodePool}");
            }
            if (bccomp($lpPool, '0', 6)>0) {
                $nUp['lp_pool'] = DB::raw("`lp_pool`+{$lpPool}");
                $nUp['lp_pool_total'] = DB::raw("`lp_pool_total`+{$lpPool}");
            }
            if ($nUp) {
                NodePool::query()->where('id', 1)->update($nUp);
            }
            
            $withdrawData = [];
            //互助奖励提现
            if ($gameWithdraw)
            {
                foreach ($gameWithdraw as $pordernum=>$wlist)
                {
                    if ($wlist)
                    {
                        $WithdrawParent = new WithdrawParent();
                        $WithdrawParent->ordernum = $pordernum;
                        $WithdrawParent->w_type = 1;    //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                        $WithdrawParent->state = 0;     //状态0待上链1上链中2已完成
                        $WithdrawParent->end_time = '';
                        $WithdrawParent->hash = '';
                        $WithdrawParent->game_team_id = $wlist[0]['game_team_id'];
                        $WithdrawParent->save();
                        
                        foreach ($wlist as $val)
                        {
                            $wOrderNum = get_ordernum('BJ');
                            $withdrawData[] = [
                                'p_id' => $WithdrawParent->id,
                                'p_ordernum' => $pordernum,
                                'ordernum' => $wOrderNum,
                                'coin_type' => 1,   //提现币种1USDT,2XHY
                                'w_type' => 1, //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                'user_id' => $val['user_id'],
                                'receive_address' => $val['wallet'],
                                'usdt' => $val['usdt'],
                                'num' => $val['usdt'],
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                            
                            //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                            $usdtData[] = [
                                'ordernum' => $wOrderNum,
                                'user_id' => $val['user_id'],
                                'from_user_id' => 0,
                                'type' => 2,
                                'cate' => 11,
                                'total' => $val['usdt'],
                                'msg' => '互助提币',
                                'content' => '互助提币',
                                'game_team_id' => $val['game_team_id'],
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                        }
                    }
                }
            }
            
            //互助奖励提现
            if ($tuijianWithdraw)
            {
                foreach ($tuijianWithdraw as $pordernum=>$wlist)
                {
                    if ($wlist)
                    {
                        $WithdrawParent = new WithdrawParent();
                        $WithdrawParent->ordernum = $pordernum;
                        $WithdrawParent->w_type = 2;    //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                        $WithdrawParent->state = 0;     //状态0待上链1上链中2已完成
                        $WithdrawParent->end_time = '';
                        $WithdrawParent->hash = '';
                        $WithdrawParent->game_team_id = $wlist[0]['game_team_id'];
                        $WithdrawParent->save();
                        
                        foreach ($wlist as $val)
                        {
                            $wOrderNum = get_ordernum('TJ');
                            $withdrawData[] = [
                                'p_id' => $WithdrawParent->id,
                                'p_ordernum' => $pordernum,
                                'ordernum' => $wOrderNum,
                                'coin_type' => 1,   //提现币种1USDT,2XHY
                                'w_type' => 2, //提现类型1开奖提现2互助推荐提币3超级提现4创世提现5LP分红提现6矿机提现
                                'user_id' => $val['user_id'],
                                'receive_address' => $val['wallet'],
                                'usdt' => $val['usdt'],
                                'num' => $val['usdt'],
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                            
                            //分类1后台操作2余额提币3提币失败4矿机释放5互助本金6互助奖励7互助推荐奖励8超级节点9创世节点10LP分红11互助提币12超级节点提币13创世节点提币14互助推荐提币
                            $usdtData[] = [
                                'ordernum' => $wOrderNum,
                                'user_id' => $val['user_id'],
                                'from_user_id' => 0,
                                'type' => 2,
                                'cate' => 14,
                                'total' => $val['usdt'],
                                'msg' => '互助推荐提币',
                                'content' => '互助推荐提币',
                                'game_team_id' => $val['game_team_id'],
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
        }
        
        //互助提币
        $this->benjinCash();
        $this->tuijianCash();
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
//                                                         $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df2487aaa24935499999';
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
//                                                         $hash = '0x6681aa1f8a74b3b7d4abfc97e05268ddf7191554ea34df2487aaa24935420c82';
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
}
