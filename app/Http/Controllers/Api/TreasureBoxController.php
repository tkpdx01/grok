<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MainCurrency;
use App\Models\News;
use App\Models\NftList;
use App\Models\OpenLog;
use App\Models\Recharge;
use App\Models\TreasureBox;
use App\Models\User;
use App\Models\UserBox;
use App\Models\UserNft;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TreasureBoxController extends Controller
{

    /**
     * @return void
     * 盲盒信息
     */
    public function boxInfo()
    {
        $boxInfo = TreasureBox::query()
            ->select('id', 'icon', 'price', 'total', 'remain', 'status')
            ->first();
        if (!empty($boxInfo)) {
            $boxInfo->icon = assertUrl($boxInfo->icon, 'admin');
        }
        return responseJson($boxInfo);
    }


    /**
     * @return void
     * 盲盒信息
     */
    public function transferBbox()
    {
        $boxInfo = TreasureBox::query()
            ->select('id', 'icon', 'price', 'total', 'remain', 'status')
            ->first();
        if (!empty($boxInfo)) {
            $boxInfo->icon = assertUrl($boxInfo->icon, 'admin');
        }
        return responseJson($boxInfo);
    }

    /**
     * @return void
     * 购买盲盒
     */
    public function getBox()
    {
        $id  = request()->post('id', 1);
        $num = intval(request()->post('num', 1));
        $box = TreasureBox::query()
            ->where('id', $id)
            ->where('status', 1)
            ->first();
        if (empty($box)) {
            return responseValidateError('未找到宝盒信息');
        }

        if ($num <= 0) {
            return responseValidateError('数量不正确');
        }

        $totalPrice = bcmul($box->price, $num, 2);
        if ($totalPrice <= 0) {
            return responseValidateError('价格不正确');
        }
        $coinInfo = MainCurrency::query()->where('id', $box->coin_id)->select([
            'contract_address', 'precision',
        ])->first();
        if (empty($coinInfo)) {
            return responseValidateError('代币信息不正确');
        }
        $remark = 'BOX';
        return responseJson([
            'coinInfo'    => $coinInfo,
            'total_price' => $totalPrice,
            'remark'      => $remark.'@'.$id.'@'.$num,
        ]);
    }

    /**
     * @return void
     * 我的盲盒
     */
    public function userBox()
    {
        $userId  = auth()->id();
        $userBox = UserBox::query()
                ->where('user_id', $userId)
                ->value('num') + 0;
        return responseJson($userBox);
    }

    /**
     * @return JsonResponse
     * 盲盒购买记录
     */
    public function buyLog()
    {
        $userId = auth()->id();
        $page   = request()->post('page', 1);
        $size   = request()->post('size', 10);
        $query  = Recharge::query()->where('user_id', $userId)->where('type', 1)->where('status', 2)->select([
            'id', 'coin', 'nums', 'other_nums', 'created_at',
        ]);

        $total = $query->count();
        $list  = $query->offset(($page - 1) * $size)
            ->limit($size)->get();
        return responseJson(compact('total', 'list'));
    }


    /**
     * @return JsonResponse
     * 开启盲盒
     */
    public function openBox()
    {
        $num = request()->post('num', 1);
        if ($num <= 0) {
            return responseValidateError('开启数量不正确');
        }
        $userId   = auth()->id();
        $redisKey = 'userOpenBox:key:'.$userId;

        if (!Redis::setnx($redisKey, 1)) {
            return responseValidateError('操作频繁');
        }

        $userBox = UserBox::query()->where('user_id', $userId)->first();
        if (!isset($userBox) || empty($userBox)) {
            Redis::del($redisKey);
            return responseValidateError('暂未找到盲盒');
        }

        if ($userBox->num < $num) {
            Redis::del($redisKey);
            return responseValidateError('待开启盲盒'.$userBox->num);
        }

        $no = mt_rand(10000000000, 90000000000).str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            for ($i = 1; $i <= $num; $i++) {
                DB::beginTransaction();
                try {
                    $nftId = $this->getRandNftId();
                    if ($nftId <= 0) {
                        $nftId = NftList::query()->where('status', 1)->where('type', 1)->min('id');
                    }
                    $nftInfo = NftList::query()->where('id', $nftId)->first();

                    /*添加NFT*/
                    $insertId = UserNft::query()->insertGetId([
                        'user_id'    => $userId,
                        'nft_id'     => $nftId,
                        'price'      => $nftInfo->price,
                        'create_no'  => $no.$i,
                        'type'       => 1,
                        'status'     => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    /*添加开启记录*/
                    OpenLog::query()->insertGetId([
                        'user_id'    => $userId,
                        'nft_id'     => $nftId,
                        'insert_id'  => $insertId,
                        'nft_name'   => $nftInfo->name,
                        'icon'       => $nftInfo->icon,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);

                    DB::commit();
                } catch (Exception $exception) {
                    Redis::del($redisKey);
                    DB::rollBack();
                    return responseValidateError('盲盒开启失败');
                }
            }
            UserBox::query()->where('id', $userBox->id)->decrement('num', $num);

            $userEffective = User::query()->where('id',$userId)->value('is_effective');
            if($userEffective!= 1){
                User::query()->where('id',$userId)->update(['is_effective'=>1]);
            }

            Redis::del($redisKey);
            DB::commit();
            return responseJson([], 200, '盲盒开启成功');
        } catch (Exception $e) {
            Redis::del($redisKey);
            return responseValidateError('盲盒开启失败');
        }

    }



    /**
     * @return void
     * 盲盒开启记录
     */
    public function openLog()
    {
        $page = request()->post('page', 1);
        $size = request()->post('size', 10);

        $userId = auth()->id();
        $query  = OpenLog::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')->select([
                'id', 'nft_id', 'nft_name', 'icon', 'created_at',
            ]);

        $total = $query->count();
        $list  = $query->offset(($page - 1) * $size)
            ->limit($size)->get();
        foreach ($list as $v) {
            $v->icon = assertUrl($v->icon, 'admin');
        }

        return responseJson(compact('total', 'list'));
    }

    /*转账盲盒*/
    public function transferBox(){

    }

    /**
     * 概率算法
     * @param  array  $probability
     * @return integer|string
     */
    public function getRandNftId()
    {
        $prize_list = NftList::query()
            ->where('status', 1)
            ->where('type', 1)
            ->where('create_rate', '>', 0)
            ->orderByRaw('rate')
            ->select('id', 'name', 'create_rate')
            ->get()->toArray();

        $arr_pro = [];   //抽奖的集合 最总的结果在次数组的产生
        foreach ($prize_list as $key => $vo) {
            $arr_pro[$vo['id']] = $vo['create_rate'];
        }

        //计算概率数组的总基数（基数越大 中奖率越准确）
        $arr_num = array_sum($arr_pro);   //将所有的中奖率累加起来得到一个基数

        $lucky_id = '';
        //概率数组循环27
        foreach ($arr_pro as $key => $vv) {
            $randNum = mt_rand(1, $arr_num);
            if ($randNum <= $vv) {
                $lucky_id = $key;
                break;
            } else {
                $arr_num -= $vv;
            }
        }
        unset($arr_pro);
        return $lucky_id;
    }

}
