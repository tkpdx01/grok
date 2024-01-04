<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BattleLog;
use App\Models\GameOrder;
use App\Models\IncomeLog;
use App\Models\LpConf;
use App\Models\RankConfig;
use App\Models\TaskLog;
use App\Models\TaskOrder;
use App\Models\TokenCard;
use App\Models\User;
use App\Models\UserDomi;
use App\Models\UserIddao;
use App\Models\UserLp;
use App\Models\UserMachine;
use App\Models\UsersCoin;
use App\Models\UserTh;
use App\Models\UserTicket;
use App\Models\UserUsdt;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public $host = '';

    public $rankArr = [
        0 => 'V0',
        1 => 'V1',
        2 => 'V2',
        3 => 'V3',
        4 => 'V4',
        5 => 'V5',
        6 => 'V6',
        7 => 'V7',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->host = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
    }

    public function info(Request $request)
    {
        $user = User::query()->where('id', auth()->id())->select('id', 'wallet', 'code', 'is_effective', 'zhi_num',
            'group_num', 'myperfor', 'teamperfor', 'static_income', 'dynamic_income', 'created_at')->first();
        $user->coin_balance = UsersCoin::getAmount($user->id,1);        //GROK余额
        $user->reset_release = BattleLog::query()->where('user_id',$user->id)->where('battle_result',3)->where('reset','>',0)->sum('reset');        //待释放
        $user->released = BattleLog::query()->where('user_id',$user->id)->where('battle_result',3)->where('profited','>',0)->sum('profited');        //已释放
        return responseJson($user);
    }


    public function teamList(Request $request)
    {
        $page = request()->post('page', 1);
        $size = request()->post('size', 10);

        $userId = auth()->id();
        $query  = User::query()
            ->where('path', 'LIKE','%-'.$userId.'-%')
            ->select([
                'wallet', 'is_effective', 'myperfor', 'teamperfor', 'created_at',
            ]);
        $total = $query->count();
        $list  = $query->offset(($page - 1) * $size)
            ->orderByDesc('id')
            ->limit($size)->get();
        if(!$list->isEmpty()){
            foreach ($list as $v) {
                $v['wallet']    = substr_replace($v['wallet'], '*****', 4, -4);
            }
        }
        return responseJson(compact('total', 'list'));
    }


    public function usdtLog(Request $request)
    {
        $user = auth()->user();
        $in   = $request->post();

        $pageNum = isset($in['page_num']) && intval($in['page_num']) > 0 ? intval($in['page_num']) : 10;
        $page    = isset($in['page']) ? intval($in['page']) : 1;
        $page    = $page <= 0 ? 1 : $page;
        $offset  = ($page - 1) * $pageNum;

        $where['user_id'] = $user->id;

        $cate = [];
        if (isset($in['cate']) && $in['cate'] && is_array($in['cate'])) {
            $cate = array_filter($in['cate']);
        }

        $list = UserUsdt::query()
            ->where($where);
        if ($cate) {
            $list = $list->whereIn('cate', $cate);
        }
        $list = $list->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($pageNum)
            ->get(['id', 'type', 'total', 'cate', 'msg', 'content', 'created_at'])
            ->toArray();
        if ($list) {

            foreach ($list as &$v) {
                $v['content'] = $v['msg'] = __("error.USDT类型{$v['cate']}");
            }

        }
        return responseJson($list);
    }

    /*收益记录*/
    public function incomeLog()
    {
        $page = request()->post('page', 1);
        $size = request()->post('size', 10);

        $userId = auth()->id();
        $query  = IncomeLog::query()
            ->where('user_id', $userId)
            ->select(['id', 'total', 'amount_type', 'type', 'remark', 'created_at']);
        $total  = $query->count();
        $list   = $query->offset(($page - 1) * $size)
            ->limit($size)
            ->orderByDesc('id')
            ->get();
        return responseJson(compact('total', 'list'));
    }

}
