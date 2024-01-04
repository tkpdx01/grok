<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\OpenLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OpenLogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new OpenLog(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('nft_id');
            $grid->column('insert_id');
            $grid->column('icon');
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
        return Show::make($id, new OpenLog(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('nft_id');
            $show->field('insert_id');
            $show->field('icon');
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
        return Form::make(new OpenLog(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('nft_id');
            $form->text('insert_id');
            $form->text('icon');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
