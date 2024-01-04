<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Jobs\UpdateDynamicPowerJob;
use App\Models\BlackHole;
use App\Models\Coin;
use App\Models\Invest;
use App\Models\LpPool;
use App\Models\MainCurrency;
use App\Models\Pledge;
use App\Models\PoolLog;
use App\Models\Recharge;
use App\Models\User;
use App\Models\UserNft;
use App\Models\UsersCoin;
use App\Models\Withdraw;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{

    public function recharge(Request $request)
    {
        Log::channel('recharge_callback')->info('收到回调', $request->post());
        $data = $request->post();
        if (
            empty($data) ||
            !isset($data['coin_token']) ||
            empty($data['to_address']) ||
            !isset($data['status']) ||
            $data['status'] != 3
        ) {
            Log::channel('recharge_callback')->info('无法继续');
            return responseJsonAsBadRequest('参数不完整');
        }

        $coin = MainCurrency::query()->where('contract_address', $data['contract_address'])->count();
        if ($coin < 1) {
            Log::channel('recharge_callback')->info('币种违规');
            return responseJsonAsBadRequest('币种违规');
        }
        $data['amount'] = bcmul($data['amount'], 1, 2);
        if (Recharge::query()->where('hash', $data['hash'])->exists()) {
            Log::channel('recharge_callback')->info('该hash已处理');
            return responseJsonAsBadRequest('该hash已处理');
        }
        if (isset($data['remarks']) && !empty($data['remarks'])) {
            $user = User::query()->where('wallet', $data['to_address'])->select('id', 'wallet', 'path', 'deep',
                'parent_id', 'myperfor', 'teamperfor', 'total_teamperfor', 'is_effective')->first();
            if (empty($user)) {
                Log::channel('recharge_callback')->info('未找到用户，无法继续');
                return responseJsonAsBadRequest('未找到用户，无法继续');
            }
        } else {
            Log::channel('recharge_callback')->info('remarks为空');
            return responseJsonAsBadRequest('remarks为空');
        }

        logic('recharge')->checkMethod($data, $user);
        return responseJsonAsCreated();
    }


    public function withdraw(Request $request)
    {
        $data = $request->post();
        Log::channel('withdraw_callback')->info('收到回调', $data);
        if (empty($data) || !isset($data['address']) || empty($data['address'])) {
            Log::channel('withdraw_callback')->info('无法继续');
            exit();
        }
        $remarks = explode('@', $data['remarks']);
        if (isset($remarks[1])) {
            if ($remarks[0] == 'WITH') {
                DB::beginTransaction();
                try {
                    $withdraw = Withdraw::query()->where('id', $remarks[1])->sharedLock()->first();
                    if (empty($withdraw)) {
                        Log::channel('withdraw_callback')->info('未找到数据无法继续');
                        exit();
                    }
                    if ($withdraw['status'] != 1) {
                        Log::channel('withdraw_callback')->info('数据已被处理，无需继续处理');
                        exit();
                    }

                    if ($data['status'] == 5) {
                        $withdraw->hash       = $data['hash'];
                        $withdraw->status     = 2;
                        $withdraw->finsh_time = date('Y-m-d H:i:s');
                        $withdraw->save();
                    } elseif ($data['status'] == 6) {
                        $withdraw->hash       = $data['hash'];
                        $withdraw->status     = 3;
                        $withdraw->finsh_time = date('Y-m-d H:i:s');
                        $withdraw->save();
                        $totalNum = $withdraw->num;
                        UsersCoin::monsIncome($withdraw->user_id, $withdraw->type, $totalNum, 6, '提现退回');
                    }

                    DB::commit();
                    Log::channel('withdraw_callback')->info('处理成功');
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::channel('withdraw_callback')->info('处理失败'.$e->getMessage().$e->getLine());
                }
            } elseif ($remarks[0] == 'NFT') {
                exit();
                $withdraw = UserNft::query()->where('id', $remarks[1])->sharedLock()->first();
                if (empty($withdraw)) {
                    Log::channel('withdraw_callback')->info('NFT未找到数据无法继续');
                    exit();
                }
                if ($withdraw['status'] != 3) {
                    Log::channel('withdraw_callback')->info('NFT提现数据已被处理，无需继续处理');
                    exit();
                }
                $withdraw->status = 4;
                $withdraw->save();
            }
        }

    }

}
