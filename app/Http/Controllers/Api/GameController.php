<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\MyRedis;
use App\Models\OrderLog;
use App\Models\MainCurrency;
use App\Models\GameTeam;
use App\Models\GameOrder;
use App\Models\TicketOrder;
use App\Models\GameTimeslot;
use App\Models\TicketCurrency;

class GameController extends Controller
{
    public $host = '';
    
    public function __construct()
    {
        parent::__construct();
        //         $this->host =  $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $this->host =  env('APP_URL');
    }
    
    
    public function checkTicket(Request $request)
    {
        return responseValidateError(__('error.当前暂未开放'));
        $in = $request->post();
        $user = auth()->user();
        
        $lockKey = 'user:checkTicket:'.$user->id;
        $MyRedis = new MyRedis();
//                 $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 40);
        if(!$lock){
            return responseValidateError(__('error.等待链上确认'));
        }
        $user = User::query()->where('id', $user->id)->first(['id', 'ticket']);
        if ($user->ticket<1) {
            $MyRedis->del_lock($lockKey);
            return responseValidateError(__('error.门票余额不足'));
        }
        //每天拼团限制8次
        $daily_max_join = intval(config('daily_max_join'));
        if ($user->tday_join>=$daily_max_join) {
            $MyRedis->del_lock($lockKey);
            $format = __('error.每天最多参与次数');
            $msg = sprintf($format, $max);
            return responseValidateError($msg);
        }
        
        $ordernum = get_ordernum();
       
        $OrderLog = new OrderLog();
        $OrderLog->ordernum = $ordernum;
        $OrderLog->user_id = $user->id;
        $OrderLog->type = 3;    //订单类型1余额提币2购买门票3参与游戏
        $OrderLog->save();
        //游戏系统每人100u参与全球公排
        $coin_num = '100';
        $data = [
            'remarks' => $ordernum,
            'coin_num' => $coin_num,
            'contract_address' => TicketCurrency::query()->where('id', '=' , 1)->value('contract_address')
        ];
        return responseJson($data);
    }
   
    
    public function gameLog(Request $request)
    {
        $user = auth()->user();
        $in = $request->post();
        
        $pageNum = isset($in['page_num']) && intval($in['page_num'])>0 ? intval($in['page_num']) : 10;
        $page = isset($in['page']) ? intval($in['page']) : 1;
        $page = $page<=0 ? 1 : $page;
        $offset = ($page-1)*$pageNum;
        
        $where['user_id'] = $user->id;
        
        if (isset($in['is_win']) && is_numeric($in['is_win'])) {
            $where['is_win'] = intval($in['is_win']);
        }
        $list = GameOrder::query()
            ->where($where)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($pageNum)
            ->get()
            ->toArray();
        if ($list) {
            //             foreach ($list as &$v) {
            //                 $v['content'] = $v['msg'] = __("error.USDT类型{$v['cate']}");
            //             }
            
        }
        return responseJson($list);
    }
    
}
