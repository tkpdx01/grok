<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Validate\Recharge\RechargeListValidate;
use App\Http\Validate\Withdraw\WithdrawFormValidate;
use App\Jobs\UpdateDynamicPowerJob;
use App\Models\BlackHole;
use App\Models\Image;
use App\Models\IncomeLog;
use App\Models\InvestDestroyLog;
use App\Models\InvestLpLog;
use App\Models\InvestPledgeLog;
use App\Models\PoolLog;
use App\Models\User;
use App\Models\WithdrawLog;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WithdrawBackController extends Controller
{


    public function index(WithdrawFormValidate $request)
    {
        //判断提现时间范围
        $withdrawTime = explode(',', config('withdraw_time'));
        $startTime = $withdrawTime[0];
        $endTime = $withdrawTime[1];
        if (date('H') < $startTime || date('H') >= $endTime) {
            return responseValidateError('提现时间:' . $startTime . ':00~' . $endTime . ':00');
        }
        $validated = $request->validated();

        $userId = auth()->id();
        $onlyKey = 'withdrawLog:key:' . $userId;
        if (!Redis::setnx($onlyKey, 1)) {
            return responseValidateError('操作频繁');
        }
        $dayWith = WithdrawLog::query()
            ->where('user_id', $userId)
            ->whereDate('created_at',date('Y-m-d'))
            ->count();
        if ($dayWith >= 20) {
            Redis::del($onlyKey);
            return responseValidateError('今日上链次数过多,请明日再来');
        }

        $configList = ['day_max' => config('plat_withdraw_max'), 'user_max' => config('user_max'),];
        $dayWithdrawed = WithdrawLog::query()
            ->where('status',3)
            ->whereDate('created_at',date('Y-m-d'))
            ->sum('num');
        if ($dayWithdrawed + $validated['num'] > $configList['day_max']) {
            Redis::del($onlyKey);
            return responseValidateError('今日上链次数过多,请明日再来');
        }
        $withdrawed = WithdrawLog::query()
            ->where('user_id',$userId)
            ->where('status',3)
            ->whereDate('created_at',date('Y-m-d'))
            ->sum('num');
        if ($withdrawed + $validated['num'] > $configList['user_max']) {
            Redis::del($onlyKey);
            return responseValidateError('当日该地址提现额度已消耗');
        }

        $withButton = config('withdraw_button');
        if ($withButton != 1) {
            Redis::del($onlyKey);
            return responseValidateError('区块波动,请稍后再试');
        }

        $user = User::query()->where('id', $userId)->first();
        $balance = $user->current_dynamic;

        $coin = 'BNB';
        $rate = config('withdraw_rate');
        $minNum = config('min_withdraw');

        if ($validated['num'] < $minNum) {
            Redis::del($onlyKey);
            return responseValidateError('最低提现金额' . $minNum);
        }
        if ($balance < $validated['num']) {
            Redis::del($onlyKey);
            return responseValidateError('BNB收益不足' . $validated['num'], '无法提现');
        }
        $feeAmount = 0;
        $ac_amount = $validated['num'];
        //检测支付是否成功
        DB::beginTransaction();
        try {
            $withdraw = new WithdrawLog();
            $withdraw->no = 'W' . date('YmdHis') . mt_rand(10000, 99999);
            $withdraw->type = 1;
            $withdraw->user_id = $user->id;
            $withdraw->receive_address = $user->name;
            $withdraw->num = $validated['num'];
            $withdraw->fee = $rate;
            $withdraw->coin = $coin;
            $withdraw->status = 1;
            $withdraw->fee_amount = $feeAmount;
            $withdraw->ac_amount = $ac_amount;
            $withdraw->create_time = time();
            $withdraw->created_at = date('Y-m-d H:i:s');
            $withdraw->save();

            if ($ac_amount > 0) {
                $before = User::query()->where('id', $userId)->value('current_dynamic');
                User::query()->where('id', $userId)->decrement('current_dynamic', $validated['num']);
                $after = bcsub($before,$validated['num'],6);
                IncomeLog::query()->insert([
                    'user_id' => $userId,
                    'from_id' => $userId,
                    'amount_type' => 1,
                    'before' => $before,
                    'total' => -$validated['num'],
                    'after' => $after,
                    'type' => 3,
                    'remark' => '提现上链',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $sendAmount = bcmul($withdraw->ac_amount, pow(10, 18), 0);
                $time = time() + 120;
                $url = 'http://127.0.0.1:8080/sign';
                $http = new Client();
                $withData = ['token' => env('BNBN_TOKEN'), 'holder' => $user->name, 'amount' => "$sendAmount", 'orderId' => "$withdraw->id", 'deadline' => "$time",];
                $withData = json_encode($withData, true);
                $response = http_post_json_data($url, $withData);
                $result = json_decode($response, true);
                if (!isset($result['token']) || !isset($result['holder'])) {
                    DB::rollBack();
                    Redis::del($onlyKey);
                    return responseValidateError('上链失败');
                }
            }
            $result['r'] = '0x' . $result['r'];
            $result['s'] = '0x' . $result['s'];
            DB::commit();
            Redis::del($onlyKey);
            return responseJson($result);
        } catch (Exception $e) {
            DB::rollBack();
            Redis::del($onlyKey);
            return responseJsonAsServerError($e->getMessage() . $e->getLine());
        }
    }


    /*点击确认调用*/
    public function withOut()
    {
        $userId = auth()->id();

        $onlyKey = 'withOutId:key:' . auth()->id();

        if (!Redis::setnx($onlyKey, 1)) {
            return responseValidateError('操作频繁');
        }
        $withId = request()->post('id', 0);
        if ($withId <= 0) {
            Redis::del($onlyKey);
            return responseValidateError('请输入正确的ID');
        }

        $order = WithdrawLog::query()->where('id', $withId)->first();
        if (empty($order)) {
            Redis::del($onlyKey);
            return responseValidateError('暂未找到该订单');
        }
        if ($order->status == 2) {
            Redis::del($onlyKey);
            return responseJson([], 200, '提现上链中...');
        } elseif ($order->status == 1) {
            DB::beginTransaction();
            try {
                WithdrawLog::query()->where('id', $withId)->where('user_id', $userId)->update(['status' => 2]);

                Redis::del($onlyKey);
                DB::commit();
                return responseJson([], 200, '提现上链中...');
            } catch (Exception $e) {
                Redis::del($onlyKey);
                DB::rollBack();
                return responseValidateError('提现失败,请稍后再试');
            }
        } elseif ($order->status == 3) {
            Redis::del($onlyKey);
            return responseValidateError('该提现已完成');
        }
    }


    public function list(RechargeListValidate $request)
    {
        $validated = $request->validated();
        $query = WithdrawLog::query()
            ->where('is_hidden', 1)
            ->where('user_id', auth()->id())
            ->select(['coin', 'num', 'fee', 'fee_amount', 'ac_amount', 'status', 'created_at']);

        $total = $query->count();
        $list = $query->offset(($validated['page'] - 1) * $validated['size'])->limit($validated['size'])->orderBy('id', 'desc')->get();
        return responseJson(compact('total', 'list'));
    }

}
