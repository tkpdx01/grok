<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LevelConfig;
use App\Models\MyRedis;
use App\Models\User;
use App\Units\EthHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public $host = '';

    public function __construct()
    {
        parent::__construct();
        $this->host = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
    }


    public function login(Request $request)
    {
        $in = $request->post();
        if (!isset($in['wallet']) || !$in['wallet']) {
            return responseValidateError('钱包地址不能为空');
        }

        $wallet = trim($in['wallet']);
        if (!checkBnbAddress($wallet)) {
            return responseValidateError('钱包地址不正确');
        }

        if (env('VERIFY_ENABLE') === true) {
            $wallet1 = strtolower($wallet);
            if (!isset($in['sign_message']) || !$in['sign_message']) {
                return responseSignError('签名错误');
            }
            $sign_message = trim($in['sign_message']);

            $signVerify = env('SIGN_VERIFY');
            if (EthHelper::signVerify($signVerify, $wallet, $sign_message) == false) {
                return responseSignError('签名不正确,无法登录');
            }
        }

        $wallet = strtolower($wallet);
        //判断是否注册过了，没有就注册一遍
        $lockKey = 'auth:login:'.$wallet;
        $MyRedis = new MyRedis();
                 $MyRedis->del_lock($lockKey);
        $lock = $MyRedis->setnx_lock($lockKey, 60);
        if (!$lock) {
            return responseValidateError('网络延迟');
        }

        $user = User::query()->where('wallet', $wallet)->first();
        if ($user) {
            if ($user->status != 1) {
                $MyRedis->del_lock($lockKey);
                return responseValidateError('用户已被禁止登陆');
            }
        } else {
            if (!isset($in['code']) || !$in['code']) {
                $MyRedis->del_lock($lockKey);
                return responseValidateError(__('error.请填写邀请码'));
            }
            $code = trim($in['code']);
            $code = strtolower($code);
//            if (!checkBnbAddress($code)) {
//                $MyRedis->del_lock($lockKey);
//                return responseValidateError(__('error.推荐人不存在'));
//            }
            $parent = User::query()->where('wallet', $code)->orWhere('code',$code)->select('id', 'path', 'deep')->first();
            if (!$parent) {
                $MyRedis->del_lock($lockKey);
                return responseValidateError(__('error.推荐人不存在'));
            }

            if ($parent->wallet == $wallet) {
                $MyRedis->del_lock($lockKey);
                return responseValidateError(__('error.推荐人不存在'));
            }

            $validated['parent_id']  = $parent->id;
            $validated['wallet']     = $wallet;
            $validated['path']       = empty($parent->path) ? '-'.$parent->id.'-' : $parent->path.$parent->id.'-';
            $validated['deep']      = $parent->deep + 1;
            $validated['headimgurl'] = 'headimgurl/default.jpg';
            $user                    = User::create($validated);
            //             $user->save();
        }

        $token   = 'Bearer '.JWTAuth::fromUser($user);
        $lastKey = 'last_token:'.$user->id;
        $MyRedis->set_key($lastKey, $token);

        $MyRedis->del_lock($lockKey);
        return responseJson([
            'token' => $token,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return responseJson();
    }

    /**
     * 注册
     */
    public function isRegister(Request $request)
    {
        $in = $request->post();
        if (!isset($in['wallet']) || !$in['wallet']) {
            return responseValidateError(__('error.请输入钱包地址'));
        }
        $wallet = trim($in['wallet']);
        if (!checkBnbAddress($wallet)) {
            return responseValidateError(__('error.钱包地址有误'));
        }
        //判断是否注册过了，没有就注册一遍
        $lockKey = 'auth:login:'.$wallet;
        $MyRedis = new MyRedis();
        $lock    = $MyRedis->setnx_lock($lockKey, 15);
        if (!$lock) {
            return responseValidateError('操作频繁');
        }
        $flag = 1;
        $user = User::where('wallet', $wallet)->first();
        if (!$user) {
            $flag = 0;
        }
        $MyRedis->del_lock($lockKey);
        return responseJson([
            'is_register' => $flag,
        ]);

    }
}
