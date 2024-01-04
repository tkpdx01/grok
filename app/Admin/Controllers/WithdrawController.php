<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Withdraw;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class WithdrawController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Withdraw::with(['user']), function (Grid $grid) {
            $statusArr = [
                1 => '处理中',
                2 => '已完成',
                3 => '已失败'
            ];


            $grid->number();
            $grid->column('no');
            $grid->column('user.wallet','用户');
            $grid->column('coin','提现币种')->badge('tear1');
            $grid->column('num','提现数量')->badge('blue');
            $grid->column('receive_address','收款地址');
            $grid->column('fee_amount','手续费');
            $grid->column('ac_amount','实际到账');
            $grid->column('status','状态')->using($statusArr)->label();

            $grid->column('finsh_time');
            $grid->column('created_at')->sortable();

            $grid->export()->rows(function (array $rows) use ($statusArr){
                foreach ($rows as $index => &$row) {
                    $row['status'] = $statusArr[$row['status']];
//                    $row['type'] = $typeArr[$row['type']];
//                    $row['user']['is_supervision'] = $supervisionArr[$row['user']['is_supervision']];
                }
                return $rows;
            })->xlsx();

            $grid->model()->orderBy('id','desc');
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableRowSelector();

            $grid->filter(function (Grid\Filter $filter) use ($statusArr) {
                $filter->equal('user.wallet','用户');
//                $filter->equal('order_no','订单号');
                $filter->equal('status','状态')->select($statusArr);
                $filter->equal('receive_address','收款地址');
                $filter->between('created_at','创建时间')->datetime();

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Withdraw(), function (Show $show) {
            $show->field('id');
            $show->field('no');
            $show->field('user_id');
            $show->field('receive_address');
            $show->field('dai_num');
            $show->field('usdt_num');
            $show->field('dai_rate');
            $show->field('fee');
            $show->field('fee_amount');
            $show->field('ac_amount');
            $show->field('status');
            $show->field('finsh_time');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Withdraw(), function (Form $form) {
            $form->display('id');
            $form->text('no');
            $form->text('user_id');
            $form->text('receive_address');
            $form->text('dai_num');
            $form->text('usdt_num');
            $form->text('dai_rate');
            $form->text('fee');
            $form->text('fee_amount');
            $form->text('ac_amount');
            $form->text('status');
            $form->text('finsh_time');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
