<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TreasureBox;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TreasureBoxController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TreasureBox(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('price','价格(U)');
            $grid->column('icon','图标')->image(env('APP_URL').'/uploads/',50,50);
            $grid->column('total','总量');
            $grid->column('remain','剩余');
            $grid->column('status','状态')->switch();
            $grid->column('created_at');


            $grid->disableRowSelector();
            $grid->disableDeleteButton();
            $grid->disableViewButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

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
        return Show::make($id, new TreasureBox(), function (Show $show) {
            $show->field('id');
            $show->field('icon');
            $show->field('name');
            $show->field('price');
            $show->field('remain');
            $show->field('total');
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
        return Form::make(new TreasureBox(), function (Form $form) {
            $form->display('id');
            $form->decimal('price','价格');
            $form->number('total');
            $form->number('remain');

            $form->image('icon','图标')->disk('admin')->uniqueName()->maxSize(10240)->accept('jpg,png,gif,jpeg')->required()->autoUpload()->removable(false)->required();
            $form->radio('status','状态')->required()->options([0=>'停止购买',1=>'可购买'])->default(1);
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
