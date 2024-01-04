<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Validate\Recharge\RechargeListValidate;
use App\Http\Validate\Withdraw\WithdrawFormValidate;
use App\Jobs\UpdateDynamicPowerJob;
use App\Models\BlackHole;
use App\Models\Image;
use App\Models\InvestDestroyLog;
use App\Models\InvestLpLog;
use App\Models\InvestPledgeLog;
use App\Models\MainCurrency;
use App\Models\PoolLog;
use App\Models\UsersCoin;
use App\Models\Withdraw;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WithdrawController extends Controller
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
        $validated['type'] = 1;
        if (!in_array($validated['type'], [1, 2])) {
            return responseValidateError('提现币种不正确');
        }

        if (!Redis::setnx('withdraw:key:' . auth()->id(), 1)) {
            return responseValidateError('操作频繁');
        }
        $user = auth()->user();

        if ($validated['type'] == 1) {
            $coin = 'GROK';
            $rate = config('withdraw_rate');
            $minNum = config('min_withdraw');
        } else {
            Redis::del('withdraw:key:' . $user->id);
            return responseValidateError('提现币种有误');
        }

        if ($validated['num'] < $minNum) {
            Redis::del('withdraw:key:' . $user->id);
            return responseValidateError('最低提现金额' . $minNum);
        }

        $balance = UsersCoin::getAmount($user->id, $validated['type']);
        if ($balance < $validated['num']) {
            Redis::del('withdraw:key:' . $user->id);
            return responseValidateError('余额不足' . $validated['num'], '无法提现');
        }
        $totalWithed = Withdraw::query()->where('user_id',$user->id)->sum('num');
        if($totalWithed > $user->myperfor * 1.5){
            Redis::del('withdraw:key:' . $user->id);
            return responseValidateError('上链失败');
        }
        //检测支付是否成功
        DB::beginTransaction();
        try {
            $feeAmount = bcmul($validated['num'],$rate,4) / 100;
            $withdraw = new Withdraw();
            $withdraw->no = 'W' . date('YmdHis') . mt_rand(10000, 99999);
            $withdraw->type = $validated['type'];
            $withdraw->user_id = $user->id;
            $withdraw->receive_address = $user->wallet;
            $withdraw->num = $validated['num'];
            $withdraw->fee = $feeAmount;
            $withdraw->coin = $coin;
            $withdraw->status = 1;
            $withdraw->fee_amount = $feeAmount;
            $withdraw->ac_amount = bcsub($validated['num'], $feeAmount, 4);
            $withdraw->created_at = date('Y-m-d H:i:s');
            $withdraw->save();

            UsersCoin::monsIncome($user->id, $validated['type'], '-' . $validated['num'], 5, '提现');
            if ($withdraw->ac_amount > 0) {
                //发送提币申请
                try {
                    $http = new Client();
                    $data = [
                        'address' => $user->wallet,
                        'amount' => $withdraw->ac_amount,
                        'contract_address' => MainCurrency::query()->where('id', 1)->value('contract_address'),
                        'notify_url' => env('APP_URL') . '/api/callback/withdraw_callback',
                        'remarks' => 'WITH@' . $withdraw->id
                    ];
                    $response = $http->post('http://127.0.0.1:9090/v1/bnb/withdraw', [
                        'form_params' => $data
                    ]);
                    $result = json_decode($response->getBody()->getContents(), true);
                    if (!isset($result['code']) || $result['code'] != 200) {
                        DB::rollBack();
                        Redis::del('withdraw:key:' . $user->id);
                        return responseValidateError('操作失败');
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    Redis::del('withdraw:key:' . $user->id);
                    return responseValidateError('操作失败');
                }
            }
            DB::commit();
            Redis::del('withdraw:key:' . $user->id);
            return responseJson();
        } catch (Exception $e) {
            DB::rollBack();
            Redis::del('withdraw:key:' . $user->id);
            return responseJsonAsServerError($e->getMessage() . $e->getLine());
        }
    }


    public function list(RechargeListValidate $request)
    {
        $validated = $request->validated();
        $query = Withdraw::query()->where('user_id', auth()->id())
            ->select(['coin', 'num', 'fee', 'fee_amount', 'ac_amount', 'status', 'created_at']);

        $total = $query->count();
        $list = $query->offset(($validated['page'] - 1) * $validated['size'])
            ->limit($validated['size'])
            ->orderBy('id', 'desc')
            ->get();
        return responseJson(compact('total', 'list'));
    }

}
