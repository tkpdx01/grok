<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\BattleLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BattleLogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new BattleLog(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('nft_id');
            $grid->column('price');
            $grid->column('user_nft_id');
            $grid->column('create_no');
            $grid->column('monter_id');
            $grid->column('status');
            $grid->column('battle_result');
            $grid->column('finshed_at');
            $grid->column('created_at');
        
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
        return Show::make($id, new BattleLog(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('nft_id');
            $show->field('price');
            $show->field('user_nft_id');
            $show->field('create_no');
            $show->field('monter_id');
            $show->field('status');
            $show->field('battle_result');
            $show->field('finshed_at');
            $show->field('created_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new BattleLog(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('nft_id');
            $form->text('price');
            $form->text('user_nft_id');
            $form->text('create_no');
            $form->text('monter_id');
            $form->text('status');
            $form->text('battle_result');
            $form->text('finshed_at');
            $form->text('created_at');
        });
    }
}
