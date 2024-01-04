<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Admin;
use Earnp\GoogleAuthenticator\GoogleAuthenticator;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class GoogleAuthController extends Controller
{

    public function index()
    {
        // 创建谷歌验证码
        $createSecret = GoogleAuthenticator::CreateSecret();
        // 您自定义的参数，随表单返回
        return view('login.google.google', ['createSecret' => $createSecret]);
    }

    public function doadd(Request $request)
    {
        $in = $request->post();
        if ($request->isMethod('post'))
        {
            if (!$in['username']) {
                return back()->with('msg', '请输入账号')->withInput();
            }

            $admininfo = Administrator::query()->where('username', $in['username'])->first();
            if (!$admininfo) {
                return back()->with('msg', '账号密码错误')->withInput();
            }
            if ($admininfo->google_code) {
                return back()->with('msg', '请勿重复绑定')->withInput();
            }

            if (!$in['onecode'] || strlen($in['onecode']) != 6) {
                return back()->with('msg', '请正确输入手机上google验证码 ！')->withInput();
            }
            if (!$in['password']) {
                return back()->with('msg', '请输入密码')->withInput();
            }

            $google = $in['google'];
            // 验证验证码和密钥是否相同
            if (GoogleAuthenticator::CheckCode($google, $in['onecode']))
            {
                $credentials = ['username'=>$in['username'], 'password'=>$in['password']];
                if (!Admin::guard()->attempt($credentials, false)) {
                    return back()->with('msg', '账号密码错误')->withInput();
                }
                //退出登录
                Admin::guard()->logout();
                $request->session()->invalidate();
                // 绑定场景：绑定成功，向数据库插入google参数，跳转到登录界面让用户登录
                $admininfo->google_code = $google;
                $admininfo->save();
                return back()->with('msg', '验证成功 ！')->withInput();
            } else {
                return back()->with('msg', '验证码错误，请输入正确的验证码 ！')->withInput();
            }
        }
        return back()->with('msg', '什么操作能到我这儿 ！')->withInput();
    }

}
