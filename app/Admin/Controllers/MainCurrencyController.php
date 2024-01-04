<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\MainCurrency;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Cache;

class MainCurrencyController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new MainCurrency(), function (Grid $grid) {
            $grid->column('id');
            $grid->column('name');
//             $grid->column('coin_img')->image(env('APP_URL').'/uploads/',50,50);
            /* 
            $grid->column('rate')->display(function ($rate){
                return '1:'.$rate;
            })->badge();
             */
            $grid->column('contract_address');
            $grid->column('precision');
            $grid->column('created_at')->sortable();
//             $grid->column('updated_at');
            $grid->model()->orderBy('id','desc');

            $grid->disableCreateButton();		//创建按钮
            $grid->disableRowSelector();		//帅选按钮
            
            $grid->disableViewButton();			//查看按钮
            $grid->disableRowSelector();		//帅选按钮
            $grid->disableDeleteButton();		//删除按钮

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('name');
            });
        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new MainCurrency(), function (Form $form) {
            $form->display('name');
//             $form->image('coin_img')->disk('admin')->required()->uniqueName()->maxSize(10240)->accept('jpg,png,jpeg,jfif')->autoUpload();
            //             $form->decimal('rate')->required();
            $form->text('contract_address')->required();
            $form->number('precision')->min(0)->required();
            $form->saving(function (Form $form){
                if (!checkBnbAddress($form->contract_address)) {
                    return $form->response()->error('地址不正确');
                }
                $form->contract_address = strtolower($form->contract_address);
            });
                
            $form->disableViewButton();
            $form->disableDeleteButton();
            $form->disableResetButton();
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->disableCreatingCheck();
        });
    }
}
