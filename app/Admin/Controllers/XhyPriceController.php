<?php

namespace App\Admin\Controllers;

use App\Models\XhyPrice;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class XhyPriceController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new XhyPrice(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('tday_price');
            $grid->column('yday_price');
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
        return Show::make($id, new XhyPrice(), function (Show $show) {
            $show->field('id');
            $show->field('tday_price');
            $show->field('yday_price');
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
        return Form::make(new XhyPrice(), function (Form $form) {
            $form->display('id');
            $form->text('tday_price');
            $form->text('yday_price');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
