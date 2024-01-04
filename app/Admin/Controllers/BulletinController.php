<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Bulletin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BulletinController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Bulletin(), function (Grid $grid) {
            $grid->number();
            $grid->column('title');
            $grid->column('content')->display('点击查看') // 设置按钮名称
            ->modal(function ($modal) {
                // 设置弹窗标题
                $modal->title($this->title);
                // 自定义图标
                return $this->content;
            });
            $grid->column('status')->switch();
            $grid->column('sort')->editable();
//             $grid->column('lang','语言')->using(['zh_CN'=>'中文','en'=>'英文']);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->model()->orderBy('id','desc');
            $grid->disableRowSelector();
            $grid->disableViewButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('name');
                $filter->equal('status')->radio([
                    0 => '下架',
                    1 => '上架'
                ]);
                /* 
                $filter->equal('lang','语言')->radio([
                    'zh_CN'=>'中文',
                    'en'=>'英文'
                ]);
                 */
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
        return Form::make(new Bulletin(), function (Form $form) {
            $form->text('title')->required();
            $form->wangEditor('content')->required()->disk('admin')->height('600');
//            $form->editor('content')->required()->disk('admin')->height('600');
            $form->radio('status','状态')->required()->options([0=>'下架',1=>'上架'])->default(1);
//             $form->radio('lang','语言')->required()->options(['zh_CN'=>'中文','en'=>'英文']);
            $form->number('sort','排序')->required()->default(0);
        });
    }
}
