<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\MyRedis;
use App\Models\OrderLog;
use App\Models\UserOrder;
use App\Models\MainCurrency;
use App\Models\TicketCurrency;
use App\Models\TicketOrderLog;
use App\Models\TicketOrder;
use App\Models\TicketCoinLog;

class TicketController extends Controller
{
    public $host = '';
    
    public function __construct()
    {
        parent::__construct();
        //         $this->host =  $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $this->host =  env('APP_URL');
    }
    
    public function coinlist(Request $request)
    {
        $in = $request->post();
        $list = TicketCurrency::query()
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('price', '>' , 0)
            ->get(['id','symbol','price','coin_img','contract_address','pancake_cate'])
            ->toArray();
        if ($list) {
            foreach ($list as &$val) {
                $val['coin_img'] = getImageUrl($val['coin_img']);
            }
        }
        return responseJson($list);
    }
    
    
    public function buy(Request $request)
    {
        return responseValidateError(__('error.当前暂未开放'));
        $in = $request->post();
        $user = auth()->user();
        
        if (!isset($in['id']) && intval($in['id'])<=0) {
            return responseValidateError(__('error.请选择支付代币'));
        }
        $id = intval($in['id']);
        
        $lockKey = 'user:info:'.$user->id;
        $MyRedis = new MyRedis();
//         $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 60);
        if(!$lock){
            return responseValidateError(__('error.操作频繁'));
        }
        
        $currency = TicketCurrency::query()
            ->where('id', $id)
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('price', '>' , 0)
            ->first();
        if (!$currency) {
            $MyRedis->del_lock($lockKey);
            return responseValidateError(__('error.币种不存在'));
        }
        
        //每购3次门票必须有1次用平台XHY代币购买
        $flag = true;
        if ($currency->is_platform!=1)
        {
            $orderList = TicketOrder::query()
                ->where('user_id', $user->id)
                ->orderBy('id', 'asc')
                ->get(['id','is_platform'])
                ->toArray();
            if ($orderList)
            {
                $orderList = array_chunk($orderList, 3);
                $endList = end($orderList);
                $platformNum = $endNum = 0;
                foreach ($endList as $key=>$val) {
                    $endNum++;
                    if ($val['is_platform']==1) {
                        $platformNum++;
                    }
                    if ($endNum>=2 && $platformNum<=0) {
                        $flag = false;
                        break;
                    }
                }
                
                if ($flag==true) {
                    $orderList2 = array_chunk($orderList, 6);
                    $endList2 = end($orderList);
                    $platformNum2 = $endNum2 = 0;
                    foreach ($endList2 as $key=>$val) {
                        $endNum2++;
                        if ($val['is_platform']==1) {
                            $platformNum++;
                        }
                        if ($endNum2>=5 && $platformNum2<=0) {
                            $flag = false;
                            break;
                        }
                    }
                }
            }
        }
        
        if (!$flag) {
            $MyRedis->del_lock($lockKey);
            return responseValidateError(__('error.必须使用XHY代币购买'));
        }
        
//         $collection_address1 = config('collection_address1');
//         $collection_address2 = config('collection_address2');
//         if (!$collection_address1 || !$collection_address2) {
//             return responseValidateError(__('error.尚未开放'));
//         }
//         $collection_address1 = trim($collection_address1);
//         $collection_address2 = trim($collection_address2);
//         if (!checkBnbAddress($collection_address1) || !checkBnbAddress($collection_address2)) {
//             return responseValidateError(__('error.尚未开放'));
//         }
//         $collection_address1 = strtolower($collection_address1);
//         $collection_address2 = strtolower($collection_address2);
//         //归集地址1比率
//         $collection_address_rate1 = @bcadd(config('collection_address_rate1'), '0', 4);
//         $collection_address_rate1 = bccomp($collection_address_rate1, '0', 4)>0 ? $collection_address_rate1 : '0';
//         $collection_address_rate1 = bccomp($collection_address_rate1, '1', 4)>0 ? '1' : $collection_address_rate1;
//         $collection_address_rate2 = bcsub('1', $collection_address_rate1, 4);
        
        $ordernum = get_ordernum();
        
//         $usdt_value = '1';
//10U购买门票可以玩50次，可以用u购买，也可以任何链上的有价资产，需要门票系统收录，购买门票的u和平台代币收款方为公司号
        $usdt_value = '10';
        $ticket_num = '50';
        $coin_price = $currency->price;
        $coin_num = bcdiv($usdt_value, $coin_price, 6);
        
        $Order = new TicketOrderLog();
        $Order->ordernum = $ordernum;
        $Order->user_id = $user->id;
        $Order->usdt_value = $usdt_value;
        $Order->coin_price = $coin_price;
        $Order->coin_num = $coin_num;
        $Order->ticket_num = $ticket_num;
        $Order->is_platform = $currency->is_platform;
        $Order->currency_id = $currency->id;
        $Order->symbol = $currency->symbol;
        $Order->save();
        
        $OrderLog = new OrderLog();
        $OrderLog->ordernum = $ordernum;
        $OrderLog->user_id = $user->id;
        $OrderLog->type = 2;    //订单类型1余额提币2购买门票3参与游戏
        $OrderLog->save();
        
        $TicketCoinLog = TicketCoinLog::query()->where('symbol', $currency->symbol)->first();
        if (!$TicketCoinLog) {
            $TicketCoinLog  = new TicketCoinLog();
            $TicketCoinLog->symbol = $currency->symbol;
            $TicketCoinLog->save();
        }
        
        $MyRedis->del_lock($lockKey);
        
        $data = [
            'remarks' => $ordernum,
            'coin_num' => $coin_num,
            'contract_address' => $currency->contract_address,
            'collection_address' => config('ticket_collection_address')
        ];
        
        return responseJson($data);
    }
    
    public function buyLog(Request $request)
    {
        $in = $request->post();
        $user = auth()->user();
        
        $pageNum = isset($in['page_num']) && intval($in['page_num'])>0 ? intval($in['page_num']) : 10;
        $page = isset($in['page']) ? intval($in['page']) : 1;
        $page = $page<=0 ? 1 : $page;
        $offset = ($page-1)*$pageNum;
        
        $where['user_id'] = $user->id;
        
        $list = TicketOrder::query()
            ->where($where)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($pageNum)
            ->get(['id','ordernum','usdt_value','coin_price','coin_num','ticket_num','is_platform','symbol','created_at'])
            ->toArray();
            
        return responseJson($list);
    }
    
    
    /**
     * 一条线上下互转
     */
    public function transfer(Request $request)
    {
        $in = $request->post();
        $user = auth()->user();
        
        if (!isset($in['num']) || !$in['num']) {
            return responseValidateError(__('error.请输入转账数量'));
        }
        $num = intval($in['num']);
        if ($num<=0) {
            return responseValidateError(__('error.数量有误'));
        }
        
        if (!isset($in['wallet']) || !$in['wallet'])  {
            return responseValidateError(__('error.请输入钱包地址'));
        }
        $wallet = trim($in['wallet']);
        if (!checkBnbAddress($wallet)) {
            return responseValidateError(__('error.钱包地址有误'));
        }
        $wallet = strtolower($wallet);
        
        $lockKey = 'user:info:'.$user->id;
        $MyRedis = new MyRedis();
        //                 $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 30);
        if(!$lock){
            return responseValidateError(__('error.操作频繁'));
        }
        
        $user = User::query()->where('id', $user->id)->first();
        if ($num>$user->ticket) {
            $MyRedis->del_lock($lockKey);
            return responseValidateError(__('error.门票余额不足'));
        }
        
        $toUser = User::query()->where('wallet', $wallet)->first(['id','path']);
        if (!$toUser) {
            $MyRedis->del_lock($lockKey);
            return responseValidateError(__('error.转账用户不存在'));
        }
        if ($toUser->id==$user->id) {
            $MyRedis->del_lock($lockKey);
            return responseValidateError(__('error.不能给自己转账'));
        }
        
        $flag = false;
        //只能一条线上下互转 查询上级
        if ($user->path) {
            $parentIds = explode('-',trim($user->path, '-'));
            $parentIds = array_filter($parentIds);
            if (in_array($toUser->id, $parentIds)) {
                $flag = true;
            }
        }
        //查询下级
        if (!$flag) {
            if($user->path) {
                $path = $user->path."{$user->id}-";
            } else {
                $path = "-{$user->id}-";
            }
            $isChild = User::query()
            ->where('id', '=', $toUser->id)
            ->where('path', 'like', "{$path}%")
            ->first(['id']);
            if ($isChild) {
                $flag = true;
            }
        }
        
        if (!$flag) {
            $MyRedis->del_lock($lockKey);
            return responseValidateError(__('error.只能一条线上下互转'));
        }
        //分类1后台操作2购买门票3互助拼团4赠予扣除5赠予获得
        $userModel = new User();
        $userModel->handleUser('ticket', $user->id, $num, 2, ['cate'=>4, 'msg'=>"赠予扣除", 'from_user_id'=>$toUser->id]);
        $userModel->handleUser('ticket', $toUser->id, $num, 1, ['cate'=>5, 'msg'=>"赠予获得", 'from_user_id'=>$user->id]);
        
        $MyRedis->del_lock($lockKey);
        return responseJson();
    }
    
    
    public function mineList(Request $request)
    {
        $in = $request->post();
        $user = auth()->user();
        
        $pageNum = isset($in['page_num']) && intval($in['page_num'])>0 ? intval($in['page_num']) : 10;
        $page = isset($in['page']) ? intval($in['page']) : 1;
        $page = $page<=0 ? 1 : $page;
        $offset = ($page-1)*$pageNum;
        
        $where['user_id'] = $user->id;
        $list = UserMine::query()
            ->where($where)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($pageNum)
            ->get(['id','order_id','type','status','price','day','wait_day','rate','created_at'])
            ->toArray();
        return responseJson($list);
    }
}
