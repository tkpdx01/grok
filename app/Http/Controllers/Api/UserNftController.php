<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GameBattle;
use App\Models\BattleDetail;
use App\Models\BattleLog;
use App\Models\MonsterList;
use App\Models\NftList;
use App\Models\UserNft;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UserNftController extends Controller
{
    public function userNft()
    {
        $userId = auth()->id();
        $status = request()->post('status', 0);
        $nftId  = request()->post('nft_id', 1);
        if ($status <= 0) {
            $status = 1;
        }
        $query = UserNft::query()
            ->with([
                'nft' => function ($query) {
                    $query->select(['id', 'name', 'critical_rate', 'attack_power', 'life_value', 'defence', 'icon']);
                },
            ])
            ->where('user_id', $userId)
            ->where('type', 1)
            ->orderByDesc('id')
            ->select('id', 'nft_id', 'price', 'create_no', 'status', 'battle_result', 'profit', 'created_at');
        if ($status > 0) {
            $query->where('status', $status);
        } else {
            $query->where('status', 1);
        }
        if ($nftId > 0) {
            $query->where('nft_id', $nftId);
        }

        $nftList = $query->get();
        return responseJson($nftList);
    }

    public function nftList()
    {
        $list = NftList::query()->where('status', 1)->select('id', 'name', 'price', 'icon')->orderByRaw('id')->get();
        return responseJson($list);
    }


    /**
     * @return void
     * 选中NFT对战
     */
    public function battleNft()
    {
        $userId = auth()->id();
        $nftId  = request()->post('id', 0);
        if ($nftId <= 0) {
            return responseValidateError('请选择正确的NFT');
        }
        $redisKey = 'battleNft:key:'.$userId;
        if (!Redis::setnx($redisKey, 1)) {
            return responseValidateError('操作频繁');
        }


        DB::beginTransaction();
        try {
            $userNft = UserNft::query()
                ->where('id', $nftId)
                ->where('user_id', $userId)
                ->first();
            if (empty($userNft)) {
                Redis::del($redisKey);
                return responseValidateError('未找到该NFT');
            }
            if ($userNft->status != 1) {
                Redis::del($redisKey);
                return responseValidateError('该NFT已对战完成');
            }
            /*生成对战记录*/
            $setRand = config('battle_win');
            $randNo  = mt_rand(1, 100);
            if ($randNo > $setRand) {
                $monsterId = MonsterList::query()->where('id', '>', $userNft->nft_id)->inRandomOrder()->value('id');
            } else {
                $monsterId = MonsterList::query()->where('id', '<=', $userNft->nft_id)->inRandomOrder()->value('id');
            }

            $nftLife     = NftList::query()->where('id', $userNft->nft_id)->value('life_value');
            $monsterLife = MonsterList::query()->where('id', $monsterId)->value('life_value');
            $battleId    = BattleLog::query()->insertGetId([
                'user_id'      => $userId,
                'nft_id'       => $userNft->nft_id,
                'price'        => $userNft->price,
                'user_nft_id'  => $userNft->id,
                'nft_no'       => $userNft->create_no,
                'monster_id'   => $monsterId,
                'nft_life'     => $nftLife,
                'monster_life' => $monsterLife,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            $userNft->status      = 2;
            $userNft->battle_time = time();
            $userNft->save();
            /*执行对战*/
            Redis::del($redisKey);
            DB::commit();

            GameBattle::dispatch($userId, $battleId);
            return responseJson([], 200, '参与对战成功');
        } catch (Exception $e) {
            Redis::del($redisKey);
            DB::rollBack();
            return responseValidateError('对战参与失败');
        }

    }


    /**
     * @return void
     * 进入对战主场景
     */
    public function battlescene()
    {
        $userId = auth()->id();
        $nftId  = request()->post('id', 0);
        $scene  = BattleLog::query()
            ->with([
                'nft' => function ($query) {
                    $query->select(['id', 'name', 'critical_rate', 'attack_power', 'life_value', 'defence', 'icon']);
                },
            ])
            ->with([
                'monster' => function ($query) {
                    $query->select(['id', 'name', 'critical_rate', 'attack_power', 'life_value', 'defence', 'icon']);
                },
            ])
            ->where('user_nft_id', $nftId)
            ->where('user_id', $userId)
            ->select('id', 'nft_id', 'monster_id', 'nft_life', 'monster_life', 'status', 'battle_result', 'profited',
                'total', 'created_at')
            ->first();

        return responseJson($scene);
    }


    /**
     * @return void
     * 对战播报
     */
    public function battleDetail()
    {
        $userId  = auth()->id();
        $batleId = request()->post('battle_id', 0);
        $log     = BattleDetail::query()
            ->where('user_id', $userId)
            ->where('battle_id', $batleId)
            ->orderByRaw('id')
            ->select('id', 'type', 'is_critical', 'harm', 'reset_life', 'is_endint', 'round', 'created_at')
            ->get();
        return responseJson($log);
    }

    /**
     * @return void
     * 对战记录
     */
    public function battleList()
    {

        $userId = auth()->id();

        $page  = request()->post('page', 1);
        $size  = request()->post('size', 10);
        $nftId = request()->post('id', 0);

        $query = BattleLog::query()
            ->with([
                'nft' => function ($query) {
                    $query->select(['id', 'name', 'critical_rate', 'attack_power', 'life_value', 'defence', 'icon']);
                },
            ])
            ->with([
                'monster' => function ($query) {
                    $query->select(['id', 'name', 'critical_rate', 'attack_power', 'life_value', 'defence', 'icon']);
                },
            ])
            ->where('user_id', $userId)
            ->select('id', 'nft_id', 'price', 'monster_id', 'nft_life', 'monster_life', 'status', 'battle_result',
                'profited',
                'total', 'created_at');
        if ($nftId >= 1) {
            $query->where('user_nft_id', $nftId);
        }

        $total = $query->count();
        $list  = $query->offset(($page - 1) * $size)
            ->limit($size)->get();

        return responseJson(compact('total', 'list'));


    }
}
