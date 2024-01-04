<?php

namespace App\Jobs;

use App\Models\Award;
use App\Models\BattleDetail;
use App\Models\BattleLog;
use App\Models\MonsterList;
use App\Models\NftList;
use App\Models\User;
use App\Models\UserNft;
use App\Models\UsersCoin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GameBattle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userIds;
    private $battleId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userIds, $battleId)
    {
        $this->userIds  = $userIds;
        $this->battleId = $battleId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userId    = $this->userIds;
        $battleId  = $this->battleId;
        $battleLog = BattleLog::query()->where('id', $battleId)->where('status', 1)->where('battle_result', 1)->first();
        if (empty($battleLog)) {
            return;
        }

        $profit = config('win_profit');

        $nftInfo     = NftList::query()->where('id', $battleLog->nft_id)->first();
        $monsterInfo = MonsterList::query()->where('id', $battleLog->monster_id)->first();
        for ($i = 1; $i <= 20; $i++) {
            if ($i % 2 != 0) {
                $type = 1;
                /*玩家轮回*/
                $criticalNo = mt_rand(1, 100);
                if ($nftInfo->critical_rate >= $criticalNo) {
                    $is_critical = 1;
                    $harm        = bcmul($nftInfo->attack_power, 1.5, 2);       //暴击伤害*1.5
                } else {
                    $is_critical = 0;
                    $harm        = $nftInfo->attack_power;
                }
                if ($harm >= $battleLog->monster_life) {
                    $profitNum                = bcmul($battleLog->price, $profit, 2) / 100;
                    $battleLog->monster_life  = 0;
                    $battleLog->status        = 2;
                    $battleLog->battle_result = 2;
                    $battleLog->profited      = $profitNum;        //战胜奖励
                    $battleLog->finshed_at    = date('Y-m-d H:i:s');
                    $battleLog->save();

                    $user = User::query()->where('id', $battleLog->user_id)->select('id', 'parent_id', 'path',
                        'deep')->first();
                    /*添加奖励*/
                    UsersCoin::monsIncome($userId, 1, $profitNum, 3, '对战胜利');
                    UsersCoin::monsIncome($userId, 1, $battleLog->price, 7, '对战胜利返回等值NFT');
                    if ($user->parent_id > 0) {
                        $parentIds = array_reverse(explode('-', trim($user->path, '-')));
                        Award::share_award($userId, $profitNum, $parentIds);
                    }

                    /*添加战斗记录*/
                    BattleDetail::query()->insertGetId([
                        'user_id'     => $userId,
                        'battle_id'   => $battleLog->id,
                        'type'        => $type,
                        'is_critical' => $is_critical,
                        'harm'        => $harm,
                        'reset_life'  => 0,
                        'is_endint'   => 1,
                        'round'       => $i,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);

                    UserNft::query()->where('id', $battleLog->user_nft_id)->update(['status'        => 3,
                                                                                    'battle_result' => 2,
                                                                                    'profit'        => $profitNum,
                                                                                    'finshed_at'    => date('Y-m-d H:i:s'),
                    ]);
                    break;
                } else {
                    $monsterLife             = bcsub($battleLog->monster_life, $harm, 2);     //怪兽剩余HP
                    $battleLog->monster_life = $monsterLife;
                    $battleLog->save();
                }
                $resetLife = $battleLog->monster_life;
            } else {
                $type      = 2;
                $monsterNo = mt_rand(1, 100);
                if ($monsterInfo->critical_rate >= $monsterNo) {
                    $is_critical = 1;
                    $harm        = bcmul($monsterInfo->attack_power, 1.5, 2);       //暴击伤害*1.5
                } else {
                    $is_critical = 0;
                    $harm        = $monsterInfo->attack_power;
                }
                if ($harm >= $battleLog->nft_life) {
                    $battleLog->nft_life      = 0;
                    $battleLog->status        = 2;
                    $battleLog->battle_result = 3;
                    $battleLog->total         = $battleLog->price;        //总共需要释放
                    $battleLog->reset         = $battleLog->price;        //剩余释放
                    $battleLog->finshed_at    = date('Y-m-d H:i:s');
                    $battleLog->save();

                    /*添加战斗记录*/
                    BattleDetail::query()->insertGetId([
                        'user_id'     => $userId,
                        'battle_id'   => $battleLog->id,
                        'type'        => $type,
                        'is_critical' => $is_critical,
                        'harm'        => $harm,
                        'reset_life'  => 0,
                        'is_endint'   => 1,
                        'round'       => $i,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);
                    UserNft::query()->where('id', $battleLog->user_nft_id)->update(['status'        => 3,
                                                                                    'battle_result' => 3,
                                                                                    'finshed_at'    => date('Y-m-d H:i:s'),
                    ]);
                    break;
                } else {
                    $nftLife             = bcsub($battleLog->nft_life, $harm, 2);     //精灵剩余HP
                    $battleLog->nft_life = $nftLife;
                    $battleLog->save();
                }
                $resetLife = $battleLog->nft_life;
            }

            /*添加战斗记录*/
            BattleDetail::query()->insertGetId([
                'user_id'     => $userId,
                'battle_id'   => $battleLog->id,
                'type'        => $type,
                'is_critical' => $is_critical,
                'harm'        => $harm,
                'reset_life'  => $resetLife,
                'is_endint'   => 0,
                'round'       => $i,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return;
    }

    /*NFT对战*/
    private function nftBattle()
    {

    }
}
