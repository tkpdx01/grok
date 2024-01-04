<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
*/

Route::namespace('App\Http\Controllers\Api')->group(function () {


    //提供给区块那边调用的
    Route::middleware(['checkCallback'])->prefix('callback')->group(function () {
        //所有订单回调
        Route::post('recharge', 'CallbackController@recharge');
        Route::post('withdraw_callback', 'CallbackController@withdraw');
    });

    Route::prefix('callback')->group(function () {
        Route::post('swapSignture', 'CallbackController@swapSignture');
        Route::post('swapPurchase', 'CallbackController@swapPurchase');
        Route::post('swapWithdraw', 'CallbackController@swapWithdraw');
        Route::post('swapLottery', 'CallbackController@swapLottery');
        Route::post('swapGetPrice', 'CallbackController@swapGetPrice');

        Route::get('searchPrice', 'CallbackController@searchPrice');
        Route::post('lpInfo', 'CallbackController@lpInfo');
        Route::post('lpInfov3', 'CallbackController@lpInfov3');
        Route::post('autoTradeDetail', 'CallbackController@autoTradeDetail');
    });

    Route::prefix('reptile')->group(function () {
        //爬虫
        Route::get('reptile', 'CallbackController@reptile');
        //爬虫
        Route::get('transfer', 'CallbackController@transfer');
    });


    //需要接口sign鉴权
    Route::middleware('CheckApiAuth')->group(function () {

        Route::prefix('auth')->group(function () {
            //登录
            Route::post('login', 'AuthController@login');
            Route::post('isRegister', 'AuthController@isRegister');
        });

        //需要验证登录
        Route::middleware(['checkUserLogin'])->group(function () {
            Route::prefix('user')->group(function () {
                Route::post('info', 'UserController@info');
                Route::post('teamList', 'UserController@teamList');
                Route::post('teamActiveList', 'UserController@teamActiveList');
                Route::post('usdtLog', 'UserController@usdtLog');
                Route::post('ticketLog', 'UserController@ticketLog');

                Route::post('incomeLog', 'UserController@incomeLog');
            });

            Route::prefix('ticket')->group(function () {
                Route::post('buy', 'TicketController@buy');
                Route::post('buyLog', 'TicketController@buyLog');
                Route::post('coinlist', 'TicketController@coinlist');
                Route::post('transfer', 'TicketController@transfer');
            });

            Route::prefix('game')->group(function () {
                Route::post('checkTicket', 'GameController@checkTicket');
                Route::post('gameLog', 'GameController@gameLog');
            });

            Route::prefix('mine')->group(function () {
                Route::post('buy', 'MineController@buy');
                Route::post('buyLog', 'MineController@buyLog');
                Route::post('mineList', 'MineController@mineList');
            });

            Route::prefix('index')->group(function () {
                Route::post('index', 'IndexController@index');
                Route::post('gameCensus', 'IndexController@gameCensus');
            });

            Route::prefix('basic')->group(function () {
//                 Route::post('upload','BasicController@upload');
                Route::post('basic', 'BasicController@basic');
                //公告列表
                Route::post('bulletin', 'BasicController@bulletin');
                Route::post('banner', 'BasicController@banner');
            });

            Route::prefix('withdraw')->group(function () {
                //提现
                Route::post('index', 'WithdrawController@index');
                //提现列表
                Route::post('list', 'WithdrawController@list');
            });

            //盲盒
            Route::prefix('box')->group(function () {
                ##盲盒信息
                Route::post('boxInfo', 'TreasureBoxController@boxInfo');
                ##购买盲盒
                Route::post('getBox', 'TreasureBoxController@getBox');
                ##盲盒购买记录
                Route::post('buyLog', 'TreasureBoxController@buyLog');
                ##我的盲盒
                Route::post('userBox', 'TreasureBoxController@userBox');
                ##开启宝盒
                Route::post('openBox', 'TreasureBoxController@openBox');
                ##开启记录
                Route::post('openLog', 'TreasureBoxController@openLog');

                Route::post('boxInfo', 'TreasureBoxController@boxInfo');


                Route::post('transferBbox', 'TransferController@transferBbox');
                Route::post('transferLog', 'TransferController@transferLog');
            });


            //我的NFT
            Route::prefix('nft')->group(function () {
                ##盲盒信息
                Route::post('nftList', 'UserNftController@nftList');
                Route::post('userNft', 'UserNftController@userNft');

                Route::post('battleNft', 'UserNftController@battleNft');

                Route::post('battlescene', 'UserNftController@battlescene');
                Route::post('battleDetail', 'UserNftController@battleDetail');
                Route::post('battleList', 'UserNftController@battleList');

            });

        });

    });

});

