<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\BattleDetail;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BattleDetailController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new BattleDetail(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('battle_id');
            $grid->column('type');
            $grid->column('harm');
            $grid->column('is_critical');
            $grid->column('reset_life');
            $grid->column('is_endint');
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
        return Show::make($id, new BattleDetail(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('battle_id');
            $show->field('type');
            $show->field('harm');
            $show->field('is_critical');
            $show->field('reset_life');
            $show->field('is_endint');
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
        return Form::make(new BattleDetail(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('battle_id');
            $form->text('type');
            $form->text('harm');
            $form->text('is_critical');
            $form->text('reset_life');
            $form->text('is_endint');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
