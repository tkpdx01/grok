<?php

namespace App\Admin\Controllers;

use App\Models\Recharged;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class RechargedController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Recharged(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('hash');
            $grid->column('user');
            $grid->column('amount');
            $grid->column('index');
            $grid->column('time');
        
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
        return Show::make($id, new Recharged(), function (Show $show) {
            $show->field('id');
            $show->field('hash');
            $show->field('user');
            $show->field('amount');
            $show->field('index');
            $show->field('time');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Recharged(), function (Form $form) {
            $form->display('id');
            $form->text('hash');
            $form->text('user');
            $form->text('amount');
            $form->text('index');
            $form->text('time');
        });
    }
}
