<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\NftList;
use App\Models\MonsterList;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class MonsterListController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new MonsterList(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name','名称');
            $grid->column('price','价格');
            $grid->column('attack_power','战力');
            $grid->column('life_value','生命值');
            $grid->column('defence','防御');
            $grid->column('critical_rate','暴击率');
            $grid->column('icon','图标')->image(env('APP_URL').'/uploads/',50,50);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();


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
        return Show::make($id, new NftList(), function (Show $show) {
            $show->field('id');
            $show->field('name','名称');
            $show->field('create_rate','开出比例');
            $show->field('icon');
            $show->field('status');
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
        return Form::make(new MonsterList(), function (Form $form) {
            $form->display('id');
            $form->text('name','名称');
            $form->decimal('price','价格');
            $form->decimal('attack_power','战力');
            $form->decimal('life_value','生命值');
            $form->decimal('defence','防御');
            $form->decimal('critical_rate','暴击率');
            $form->image('icon','图标')->disk('admin')->uniqueName()->maxSize(10240)->accept('jpg,png,gif,jpeg')->required()->autoUpload()->removable(false)->required();

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
