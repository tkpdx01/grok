<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\IncomeLog;
use Dcat\Admin\Color;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class IncomeLogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(IncomeLog::with(['user','from']), function (Grid $grid) {
            $typeArr = [
                0 => '资产校对',
                1 => '推荐收益',
                2 => '团队收益',
                3 => '战胜奖励',
                4 => '战败释放',
                5 => '收益提现',
                6 => '提现退回',
                7 => '战胜本金归还',
                8 => '战败释放',
            ];

            $grid->number();
            $grid->column('user_id','UID');
            $grid->column('user.wallet','获得用户');
            $grid->column('from.wallet','触发用户');
            $grid->column('amount_type','钱包类型')->using([1=>'GROK',2=>'USDT'])
                ->badge([1=>'blue1',2=>'orange1',3=>'yellow',4=>'tear',5=>'indigo-darker',6=>'red-darker',9=>'blue-darker',10=>'cyan-darker']);
            $grid->column('type','类型')->using($typeArr)->label();
            $grid->column('remark','说明')->label('success');
            $grid->column('before','操作前');
            $grid->column('total','金额');
            $grid->column('after','操作后');
            $grid->column('created_at')->sortable();
            $grid->model()->orderBy('id','desc');

            $grid->export()->rows(function (array $rows){
                return $rows;
            })->xlsx();


            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->model()->orderBy('id','desc');

            $grid->filter(function (Grid\Filter $filter) use ($typeArr) {
                $filter->equal('user.wallet','用户名');
                $filter->equal('from.wallet','触发用户');
                $filter->equal('amount_type','资产类型')->select([1=>'GROK',2=>'USDT']);
                $filter->equal('type','类型')->select($typeArr);
                $filter->between('created_at')->datetime();
            });
        });
    }

}
