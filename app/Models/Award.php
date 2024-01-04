<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'income_log';


    /**
     * @return void
     * 节点收益
     */
    public static function node_award($uid, $num, $pidArr)
    {
        $userInfo = User::query()
            ->where('id', $uid)
            ->select(['id', 'parent_id', 'path', 'deep'])
            ->first();

        $useridArr = User::query()
            ->whereIn('id', $pidArr)
            ->where('node_id', '>=', 1)
            ->orderByDesc('id')
            ->pluck('id');

        if ($useridArr->isEmpty()) {
            return true;
        }

        $price = config('usdx_price');

        $lastLevel  = 0;
        $levelCount = 0;     //同一个等级出现的次数
        $lastAward  = 0;     //上一个收益比例
        foreach ($useridArr as $v) {
            $parentInfo = User::query()->where('id', $v)->first();
            if ($parentInfo->node_id <= $lastLevel) {
                continue;
            }
            $levelInfo = NodeList::query()->where('id', $parentInfo->node_id)->first();
            if ($lastLevel == $parentInfo->node_id) {
                if ($levelCount >= 1) {
                    continue;
                }
                if ($levelInfo->same_rate <= 0) {
                    continue;
                }
                $sameRate = 10;
                $awardNum = bcmul($lastAward, $sameRate, 2) / 100;
                if ($awardNum < 0.01) {
                    continue;
                }
                $levelCount++;
            } elseif ($parentInfo->node_id > $lastLevel) {
                $lastRate  = NodeList::query()->where('id', $lastLevel)->value('rate');
                $awardRate = bcsub($levelInfo->rate, $lastRate, 2);
                $awardNum  = bcdiv($num, $awardRate, 2) / 100;
                if ($awardNum < 0.01) {
                    continue;
                }
                $levelCount = 0;
            }
            if ($awardNum < 0.01) {
                continue;
            }

            $awardNum = bcmul($awardNum, $price, 2);

            $deepDiff = $userInfo->deep - $parentInfo->deep;
            $remark   = $deepDiff.'代'.$userInfo->name.'购买节点'.$num.'['.$levelInfo->name.']收益';
            UsersCoin::insertIncome($parentInfo->id, 3, $awardNum, 1, $remark, $uid);

            $lastLevel = $parentInfo->node_id;
            $lastAward = $awardNum;
        }
        return true;
    }

    /**
     * @return void
     * 趴点收益
     */
    public static function lie_award($uid, $num, $pidArr)
    {
        $userInfo = User::query()
            ->where('id', $uid)
            ->select(['id', 'parent_id', 'path', 'deep'])
            ->first();

        $useridArr = User::query()
            ->whereIn('id', $pidArr)
            ->where('liedown_rate', '>', 0)
            ->orderByDesc('id')
            ->pluck('id');
        if ($useridArr->isEmpty()) {
            return true;
        }

        $price    = config('usdx_price');
        $lastRate = 0;
        foreach ($useridArr as $v) {
            $parentInfo = User::query()->where('id', $v)->select('id', 'deep', 'level_id', 'node_id', 'activate',
                'liedown_rate', 'team_performance')->first();
            if ($parentInfo->liedown_rate <= $lastRate) {
                continue;
            }

            $awardRate = bcsub($parentInfo->liedown_rate, $lastRate, 2);
            $awardNum  = bcmul($num, $awardRate, 2) / 100;

            $awardNum = bcdiv($awardNum, $price, 2);
            if ($awardNum < 0.01) {
                continue;
            }
            $deepDiff = $userInfo->deep - $parentInfo->deep;
            $remark   = $deepDiff.'代'.$userInfo->name.'购买节点'.$num;
            UsersCoin::insertIncome($parentInfo->id, 3, $awardNum, 2, $remark, $uid);

            $lastRate = $parentInfo->liedown_rate;
        }
        return true;
    }


    /**
     * @return void
     * 报单趴点收益
     * $moneyType==>代币标识
     * 1=>'GP',2=>'GEM',3=>'USDX',4=>'USDT'
     */
    public static function pledge_lie_award($uid, $num, $pidArr, $moneyType)
    {
        $userInfo = User::query()
            ->where('id', $uid)
            ->select(['id', 'parent_id', 'path', 'deep'])
            ->first();

        $useridArr = User::query()
            ->whereIn('id', $pidArr)
            ->where('liedown_rate', '>', 0)
            ->orderByDesc('id')
            ->pluck('id');
        if ($useridArr->isEmpty()) {
            return true;
        }

        $price    = config('usdx_price');
        $lastRate = 0;
        foreach ($useridArr as $v) {
            $parentInfo = User::query()->where('id', $v)->select('id', 'deep', 'level_id', 'node_id', 'activate',
                'liedown_rate', 'team_performance')->first();
            if ($parentInfo->liedown_rate <= $lastRate) {
                continue;
            }

            $awardRate = bcsub($parentInfo->liedown_rate, $lastRate, 2);
            $awardNum  = bcmul($num, $awardRate, 2) / 100;
            if ($moneyType == 4) {
                /*USDT报单 给趴点收益换算成USDX*/
                $awardNum  = bcdiv($awardNum, $price, 2);
                $moneyType = 3;
            }
            if ($awardNum < 0.01) {
                continue;
            }
            $deepDiff = $userInfo->deep - $parentInfo->deep;
            $remark   = $deepDiff.'代'.$userInfo->name.'报单'.$num;
            UsersCoin::insertIncome($parentInfo->id, $moneyType, $awardNum, 2, $remark, $uid);

            $lastRate = $parentInfo->liedown_rate;
        }
        return true;
    }


    /**
     * @return void
     * 推荐收益
     * $moneyType = 1 区块购买需要转换价格
     */
    public static function share_award($uid, $num, $pidArr)
    {
        $userInfo = User::query()
            ->where('id', $uid)
            ->select(['id', 'wallet', 'parent_id', 'path', 'deep'])
            ->first();

        $useridArr = User::query()
            ->whereIn('id', $pidArr)
            ->where('is_effective', 1)
            ->whereBetween('deep', [$userInfo->deep - 2, $userInfo->deep])
            ->orderByDesc('id')
            ->limit(2)
            ->pluck('id');
        if ($useridArr->isEmpty()) {
            return true;
        }
        $shareRate = [
            1 => config('direct_rate'),
            2 => config('indirect_rate'),
        ];
        foreach ($useridArr as $v) {
            $parentInfo = User::query()->where('id', $v)->select('id', 'deep', 'is_effective',
                'dynamic_income')->first();
            $deepDiff   = $userInfo->deep - $parentInfo->deep;
            $awardRate  = $shareRate[$deepDiff];
            if ($awardRate <= 0) {
                continue;
            }
            $awardNum = bcmul($num, $awardRate, 2) / 100;

            $deepDiff = $userInfo->deep - $parentInfo->deep;
            $remark   = $deepDiff.'代'.substr_replace($userInfo->wallet, '*****', 4, -4).'战胜收益'.$num;
            UsersCoin::monsIncome($parentInfo->id, 1, $awardNum, 1, $remark, $uid);

            $parentInfo->dynamic_income = bcadd($parentInfo->dynamic_income, $awardNum, 2);
            $parentInfo->save();
        }
        return true;
    }


    /**
     * @return void
     * 推荐收益
     * $moneyType = 1 区块购买需要转换价格
     * 1=>'GP',2=>'GEM',3=>'USDX',4=>'USDT'
     */
    public static function pledge_share_award($uid, $num, $pidArr, $moneyType = 1)
    {
        $userInfo = User::query()
            ->where('id', $uid)
            ->select(['id', 'parent_id', 'path', 'deep'])
            ->first();

        $useridArr = User::query()
            ->whereIn('id', $pidArr)
            ->where('activate', 1)
            ->whereBetween('deep', [$userInfo->deep - 2, $userInfo->deep])
            ->orderByDesc('id')
            ->limit(2)
            ->pluck('id');
        if ($useridArr->isEmpty()) {
            return true;
        }

        $shareRate = [
            1 => config('bao_direct_rate'),
            2 => config('bao_indirect_rate'),
        ];
        $price     = config('usdx_price');
        foreach ($useridArr as $v) {
            $parentInfo = User::query()->where('id', $v)->select('id', 'deep', 'level_id', 'node_id', 'activate',
                'liedown_rate', 'team_performance')->first();
            $deepDiff   = $userInfo->deep - $parentInfo->deep;

            $awardRate = $shareRate[$deepDiff];
            if ($awardRate <= 0) {
                continue;
            }
            $awardNum = bcmul($num, $awardRate, 2) / 100;
            if ($moneyType == 4) {
                $awardNum  = bcdiv($awardNum, $price, 2);
                $moneyType = 3;
            }
            if ($awardNum < 0.01) {
                continue;
            }
            $deepDiff = $userInfo->deep - $parentInfo->deep;
            $remark   = $deepDiff.'代'.$userInfo->name.'报单'.$num;
            UsersCoin::insertIncome($parentInfo->id, $moneyType, $awardNum, 3, $remark, $uid);
        }
        return true;
    }


}
