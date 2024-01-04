<?php


namespace App\Logic;


use App\Jobs\CrowHandleJob;
use App\Jobs\PayOrderJob;
use App\Jobs\PlayJob;
use App\Jobs\ScanPayOrderJob;
use App\Jobs\SettlementOrderJob;
use App\Models\Award;
use App\Models\Car;
use App\Models\CrowList;
use App\Models\CrowLog;
use App\Models\GongApply;
use App\Models\LpMiningPool;
use App\Models\LpMiningPoolLog;
use App\Models\LpPool;
use App\Models\LpPoolLog;
use App\Models\MatchOrder;
use App\Models\Nameplate;
use App\Models\Nft;
use App\Models\PerformanceStatistic;
use App\Models\PledgeList;
use App\Models\PledgeOrder;
use App\Models\Point;
use App\Models\Product;
use App\Models\ProductPay;
use App\Models\Recharge;
use App\Models\SccList;
use App\Models\TreasureBox;
use App\Models\User;
use App\Models\UserBox;
use App\Models\UserScc;
use App\Models\UsersCoin;
use App\Models\UsersNameplate;
use App\Models\UsersNft;
use App\Models\WaitLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RechargeLogic
{

    /**
     * 检测需要调用什么方法
     * @param $data
     */
    public function checkMethod($data, $user)
    {
        $remarks = $this->parseRemark($data['remarks']);

        if ($remarks[0] === 'BOX') {
            $this->buyBox($data, $user);
        }
//
//        if ($remarks[0] === 'MATC') {
//            $this->match($data);
//        }
    }

    /**
     * 解析参数
     * @param $remarks
     * @return false|string[]
     */
    private function parseRemark($remarks)
    {
        return explode('@', $remarks);
    }

    private function buyBox($data, $user)
    {

        Log::channel('recharge_callback')->info('开始处理充值BOX请求');
        $userId  = User::query()->where('wallet', $data['to_address'])->value('id');
        $remarks = $this->parseRemark($data['remarks']);
        $boxId   = $remarks[1];
        $boxNum  = $remarks[2];
        if ($boxId <= 0 || $boxNum <= 0) {
            Log::channel('recharge_callback')->info('未找到盲盒');
            exit();
        }
        $box = TreasureBox::query()
            ->where('id', $boxId)
            ->select('id', 'name', 'icon', 'price')
            ->first();
        if (empty($box)) {
            Log::channel('recharge_callback')->info('未找到盲盒');
            exit();
        }

        $totalPrice = bcmul($box->price, $boxNum, 2);
        if ($totalPrice <= 0) {
            Log::channel('recharge_callback')->info('价格不正确');
            exit();
        }
        if ($data['amount'] < $totalPrice) {
            Log::channel('recharge_callback')->info('价格不正确');
            exit();
        }


        DB::beginTransaction();
        try {
            $userModel = new User();
            $userModel->handleAchievement($user->id, $data['amount'], 1);

            UserBox::insertBox($userId, $boxNum);
            Recharge::query()->insertGetId([
                'user_id'     => $userId,
                'type'        => 1,
                'coin'        => $data['coin_token'],
                'nums'        => $data['amount'],
                'other_nums'  => $boxNum,
                'hash'        => $data['hash'],
                'status'      => 2,
                'created_at'  => date('Y-m-d H:i:s'),
                'finish_time' => date('Y-m-d H:i:s'),
            ]);

            if ($user->parent_id > 0) {
                $userModel->handlePerformance($user->path, $data['amount'], 1);
//                $parentIds = array_reverse(explode('-', trim($user->path, '-')));
//                Award::share_award($userId, $totalPrice, $parentIds);
            }
            DB::commit();
            Log::channel('recharge_callback')->info('处理购买盲盒请求完成');
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('recharge_callback')->info('处理购买盲盒请求失败'.$e->getMessage().$e->getLine());
        }

    }

    private function match($data)
    {
        Log::channel('recharge_callback')->info('开始处理交易请求');
        $remarks = $this->parseRemark($data['remarks']);

        $match = MatchOrder::query()
            ->where('id', $remarks[1])
            ->where('status', 1)
            ->first();
        if (empty($match) || $match->status != 1) {
            Log::channel('recharge_callback')->info('未找到匹配信息');
            exit;
        }
        if ($data['amount'] < $match->price) {
            Log::channel('recharge_callback')->info('支付金额不足,无法购买');
            exit;
        }

        $user = User::query()->where('wallet', $data['to_address'])->first();
        DB::beginTransaction();
        try {
            $issetScc = UserScc::query()->where('id', $match->user_scc_id)->where('user_id',
                $match->sell_uid)->where('scc_id', $match->scc_id)->first();
            if (empty($issetScc)) {
                DB::rollBack();
                Log::channel('recharge_callback')->info('售出方未找到汽车');
                exit;
            }
            /*汽车入库*/
            $userSccFin = UserScc::query()->where('id', $match->user_scc_id)->where('user_id',
                $match->sell_uid)->where('scc_id', $match->scc_id)->update(['status' => 0, 'is_show' => 2]);

            $sccInfo    = SccList::query()->where('id', $match->scc_id)->first();
            $expireDate = date("Y-m-d $sccInfo->start_date", strtotime('+'.$sccInfo->period_day.'day'));
            $round      = UserScc::query()->where('user_id', $user->id)->where('scc_id',
                    $match->scc_id)->where('scc_no', $match->scc_no)->count() + 1;

            $startTime   = strtotime(date("Y-m-d $sccInfo->start_date"));
            $startWeekNo = date('w', $startTime);

            $endTime   = strtotime($expireDate);
            $endWeekNo = $startWeekNo + $sccInfo->period_day;
            $restDay   = config('rest_day');
            if ($startWeekNo <= $restDay && $endWeekNo >= $restDay) {
                $endTime = $endTime + 86400;
            }
            UserScc::query()->insertGetId([
                'user_id'     => $match->user_id,
                'scc_id'      => $match->scc_id,
                'scc_no'      => $match->scc_no,
                'round'       => $round,
                'price'       => $match->price,
                'period_day'  => $sccInfo->period_day,
                'daily_rate'  => $sccInfo->daily_rate,
                'type'        => 1,
                'expire_time' => $endTime - 120,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            $match->status   = 2;
            $match->hash     = $data['hash'];
            $match->finsh_at = date('Y-m-d H:i:s');
            $match->save();

            /*燃料退回*/
            UsersCoin::sccIncome($user->id, 1, $match->reserve_num, 11, '预约交易成功退回');

            Recharge::query()->insertGetId([
                'user_id'    => $user->id,
                'type'       => 2,
                'coin'       => 'USDT',
                'nums'       => $data['amount'],
                'hash'       => $data['hash'],
                'status'     => 2,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            DB::commit();
            Log::channel('recharge_callback')->info('处理交易请求完成');
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('recharge_callback')->info($match.'处理交易请求失败'.$e->getMessage().$e->getLine());
        }

    }

}
