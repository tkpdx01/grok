<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('users', 'UserController');
    $router->resource('tree', 'UserTreeController');
    $router->resource('recharge', 'RechargeController');
    $router->resource('banner', 'BannerController');
    $router->resource('bulletin', 'BulletinController');
    $router->resource('withdraw', 'WithdrawController');
    $router->resource('main_currency','MainCurrencyController');
    
    $router->resource('rank_config','RankConfigController');
    $router->resource('user_usdt','UserUsdtController');
    $router->resource('rank_conf','RankConfigController');
    $router->resource('deep_config','DeepConfigController');
    
    
    $router->resource('ticket_currency','TicketCurrencyController');
    $router->resource('ticket_order','TicketOrderController');
    $router->resource('user_machine','UserMachineController');
    $router->resource('user_ticket','UserTicketController');
    $router->resource('game_team','GameTeamController');
    $router->resource('game_order','GameOrderController');
    $router->resource('node_pool','NodePoolController');
    $router->resource('old_user','OldUserDatumController');


    $router->resource('treasure_box','TreasureBoxController');
    $router->resource('income', 'IncomeLogController');

    ##用户NFT
    $router->resource('nft_list','NftListController');
    $router->resource('monster_list','MonsterListController');
    $router->resource('user_nft','UserNftController');

});
