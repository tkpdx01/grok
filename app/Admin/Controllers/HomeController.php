<?php

namespace App\Admin\Controllers;
use App\Admin\Metrics\TodayBattle;
use App\Admin\Metrics\TodayFreeze;
use App\Admin\Metrics\TodayRelease;
use App\Admin\Metrics\TodayWin;
use App\Admin\Metrics\TodayWithdraw;
use App\Admin\Metrics\TotalBattle;
use App\Admin\Metrics\TotalFreeze;
use App\Admin\Metrics\TotalRelease;
use App\Admin\Metrics\TotalWin;
use App\Admin\Metrics\TotalWithdraw;
use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

use App\Admin\Metrics\TodayUsers;
use App\Admin\Metrics\TotalUsers;
use App\Admin\Metrics\TotalGameOrder;
use App\Admin\Metrics\TotalGameMoney;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('主页')
            ->description('')
            ->body(function (Row $row) {


                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row){
                        //总注册
                        $row->column(3,new TotalUsers());
                        //今日注册
                        $row->column(3,new TodayUsers());
                    });
                    $column->row(function (Row $row){
                        $row->column(3,new TodayBattle());
                        $row->column(3,new TotalBattle());
                    });
                });

                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row){
                        $row->column(3,new TodayWin());
                        $row->column(3,new TotalWin());

                    });

                    $column->row(function (Row $row){
                        $row->column(3,new TodayWithdraw());
                        $row->column(3,new TotalWithdraw());
                    });
                });


                $row->column(12, function (Column $column) {
                    $column->row(function (Row $row){
                        $row->column(3,new TodayFreeze());
                        $row->column(3,new TotalFreeze());

                    });

                    $column->row(function (Row $row){

                        $row->column(3,new TodayRelease());
                        $row->column(3,new TotalRelease());
                    });

                });


            });
    }
}
