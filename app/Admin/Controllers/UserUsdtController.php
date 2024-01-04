<?php

namespace App\Admin\Controllers;

use App\Models\UserUsdt;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserUsdtController extends AdminController
{
    public $cateArr = [
        1=>'系统操作',
        2=>'余额提币',
        3=>'提币失败',
        4=>'矿机释放',
        5=>'互助本金',
        6=>'互助奖励',
        7=>'互助推荐奖励',
        8=>'超级节点奖励',
        9=>'创世节点奖励',
        10=>'LP分红奖励',
        11=>'互助提币',
        12=>'超级节点提币',
        13=>'创世节点提币',
        14=>'互助推荐提币',
        15=>'LP分红提币',
        16=>'矿机加速释放',
    ];
    
    
    protected function grid()
    {
        return Grid::make(UserUsdt::with(['user']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('user.wallet', '用户地址');
            $grid->column('type')
            ->display(function () {
                $arr = [1=>'收入', 2=>'支出'];
                $msg = $arr[$this->type];
                $colour = $this->type == 1 ? '#21b978' : '#ea5455';
                return "<span class='label' style='background:{$colour}'>{$msg}</span>";
            });
            $grid->column('total');
            //             $grid->column('ordernum');
            //             $grid->column('msg');
            $grid->column('cate')->using($this->cateArr)->label();
            $grid->column('from_user_id');
            //             $grid->column('ma_usdt_price');
            $grid->column('content');
            $grid->column('created_at');
            $grid->model()->orderBy('id','desc');
            
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableDeleteButton();
            $grid->disableActions();
            $grid->scrollbarX();    			//滚动条
            $grid->paginate(10);				//分页
            
            
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('user_id');
                $filter->equal('user.wallet', '用户地址');
                $filter->equal('type')->select([1=>'收入', 2=>'支出']);
                $filter->equal('cate')->select($this->cateArr);
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
        return Show::make($id, new UserUsdt(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('from_user_id');
            $show->field('type');
            $show->field('cate');
            $show->field('total');
            $show->field('msg');
            $show->field('ordernum');
            $show->field('content');
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
        return Form::make(new UserUsdt(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('from_user_id');
            $form->text('type');
            $form->text('cate');
            $form->text('total');
            $form->text('msg');
            $form->text('ordernum');
            $form->text('content');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
