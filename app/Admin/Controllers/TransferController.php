<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Transfer;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TransferController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Transfer(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('transfer_id');
            $grid->column('type');
            $grid->column('num');
            $grid->column('fee');
            $grid->column('ac_num');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
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
        return Show::make($id, new Transfer(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('transfer_id');
            $show->field('type');
            $show->field('num');
            $show->field('fee');
            $show->field('ac_num');
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
        return Form::make(new Transfer(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('transfer_id');
            $form->text('type');
            $form->text('num');
            $form->text('fee');
            $form->text('ac_num');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
