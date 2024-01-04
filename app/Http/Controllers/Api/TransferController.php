<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Validate\Transfer\TransferListValidate;
use App\Http\Validate\Transfer\TransferValidate;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserBox;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TransferController extends Controller
{

    public function transferBbox(TransferValidate $request)
    {
        $onlyKey = 'transfer:key:'.auth()->id();

        if (!Redis::setnx($onlyKey, 1)) {
            return responseValidateError('操作频繁');
        }
        $validated = $request->validated();
        $user      = auth()->user();
        if (strtolower($validated['address']) == strtolower($user->wallet)) {
            Redis::del($onlyKey);
            return responseValidateError('不能转给自己');
        }
        //查找对方用户
        $other = User::query()->where('wallet', $validated['address'])->first();
        if (empty($other)) {
            Redis::del($onlyKey);
            return responseValidateError('未找到对应用户');
        }
        if ($other->id == $user->id) {
            Redis::del($onlyKey);
            return responseValidateError('不能转给自己');
        }


        $balance = UserBox::query()->where('user_id', $user->id)->value('num');
        if ($balance < $validated['num']) {
            Redis::del($onlyKey);
            return responseValidateError('数量不足：'.$validated['num']);
        }

        DB::beginTransaction();
        try {
            Transfer::query()->insert([
                'user_id'     => auth()->id(),
                'num'         => $validated['num'],
                'transfer_id' => $other->id,
                'type'        => 1,
                'fee'         => 0,
                'ac_num'      => $validated['num'],
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            UserBox::insertBox($user->id, -$validated['num']);
            UserBox::insertBox($other->id, $validated['num']);

            DB::commit();
            Redis::del($onlyKey);
            return responseJson();
        } catch (Exception $e) {
            DB::rollBack();
            Redis::del($onlyKey);
            return responseValidateError('转出失败');
            return responseJsonAsServerError($e->getMessage());
        }
    }

    /**
     * 内转记录
     * @param  TransferListValidate  $request
     * @return JsonResponse
     */
    public function transferLog(TransferListValidate $request)
    {
        $validated = $request->validated();
        $query     = Transfer::query()->with([
            'other' => function ($query) {
                $query->select(['id', 'wallet']);
            },
        ])->where('user_id', auth()->id())
            ->select(['transfer_id', 'num', 'created_at']);
        if (isset($validated['type']) && strlen($validated['type']) > 0) {
            $query->where('type', $validated['type']);
        }
        $total = $query->count();
        $list  = $query->orderByDesc('id')->offset(($validated['page'] - 1) * $validated['size'])
            ->limit($validated['size'])
            ->get();
        return responseJson(compact('total', 'list'));
    }

}
