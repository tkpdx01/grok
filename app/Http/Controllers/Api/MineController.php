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
use App\Models\UserMine;
use App\Models\UserMachine;

class MineController extends Controller
{
    public $host = '';
    
    public function __construct()
    {
        parent::__construct();
        //         $this->host =  $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $this->host =  env('APP_URL');
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
        $list = UserMachine::query()
            ->where($where)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($pageNum)
            ->get(['id','ordernum','total','residue_total','status','rate','created_at'])
            ->toArray();
        return responseJson($list);
    }
}
