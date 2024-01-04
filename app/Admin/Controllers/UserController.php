<?php

namespace App\Admin\Controllers;

// use App\Admin\Actions\Grid\SetUsdtNum;
use App\Admin\Actions\Grid\SetBalanceNum;

use App\Admin\Repositories\User;
use Dcat\Admin\Actions\Action;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

use App\Models\User as UserModel;


class UserController extends AdminController
{
    public $rankArr = [
        0 => '普通会员',
        1 => '超级节点',
        2 => '创世节点',
    ];
    public $activeArr = [
        0 => '否',
        1 => '是',
    ];
    public $effectiveArr = [
        0 => '否',
        1 => '是',
    ];
    
    
    protected function grid()
    {
        return Grid::make(User::with(['parent','coin1']), function (Grid $grid) {
            
            $grid->column('id');
            $grid->column('wallet');
            $grid->column('parent_id', '上级ID');
            $grid->column('parent.wallet','上级')->limit(15);

            $grid->column('is_effective')->using([0=>'否',1=>'是'])->badge([0=>'gray',1=>'tear']);

            $grid->column('coin1.amount', 'GROK余额')->badge('danger');
//            $grid->column('static_income', '游戏收益')->badge('blue');
            $grid->column('dynamic_income', '动态收益')->badge('pink');
            $grid->column('zhi_num', '直推人数');
            $grid->column('group_num', '团队人数');
            $grid->column('myperfor', '个人业绩');
            $grid->column('teamperfor', '伞下业绩');
             $grid->column('status','状态')->switch('',true);

            $grid->column('pathlist', '关系树')->display('查看') // 设置按钮名称
                ->modal(function ($modal) {
                    // 设置弹窗标题
                    $modal->title('关系树');
                    $path = $this->path;
                    $parentIds = explode('-',trim($path,'-'));
                    $parentIds = array_reverse($parentIds);
                    $parentIds = array_filter($parentIds);
                    
                    $html = '<table class="table custom-data-table data-table" id="grid-table">
                                    <thead>
                                    	  <tr>
                                    			 <th>上级ID</th>
                                                 <th>层级</th>
                                    			 <th>地址</th>
                                    	  </tr>
                                    </thead>
                                    <tbody>';
                    
                    if ($parentIds)
                    {
                        $list = UserModel::query()->whereIn('id',$parentIds)->orderBy('deep', 'desc')->get(['id','wallet','deep','code'])->toArray();
                        if ($list) {
                            foreach ($list as $val) {
                                $html.= "<tr><td>{$val['id']}</td>";
                                $html.= "<td>{$val['deep']}</td>";
                                $html.= "<td>{$val['wallet']}</td>";
                                $html.= "</tr>";
                            }
                        }
                    }
                    
                    $html.= "</tbody></table>";
                    // 自定义图标
                    return $html;
            });

            $grid->column('created_at','注册时间');
            
            //如果代发货，显示发货按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) use (&$grid){
                $actions->append(new SetBalanceNum());
            });
            
//             $grid->disableActions();			//操作按钮
            $grid->disableRowSelector();
             $grid->disableEditButton();
             $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->disableCreateButton();

            $grid->model()->orderBy('id','desc');
            $grid->scrollbarX();    			//滚动条
            $grid->paginate(10);				//分页

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('parent.wallet','推荐人地址地址');
                $filter->equal('wallet');
                $filter->equal('parent_id', '上级ID');
                 $filter->equal('status','状态')->radio([0=>'禁用',1=>'有效']);
                $filter->equal('is_effective')->select($this->effectiveArr);
                $filter->between('created_at','注册时间')->datetime();
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
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('wallet');
            $show->field('rank')->using($this->rankArr)->label('success');
            $show->field('usdt');
            $show->field('ticket');
            $show->field('usdt_cj');
            $show->field('usdt_cs');
            $show->field('zhi_num');
            $show->field('group_num');
            $show->field('achievement');
            $show->field('performance');
            
            $show->field('created_at');
//             $show->field('updated_at');
            $show->disableDeleteButton();
            $show->disableEditButton();
        });
        
    }
    

    protected function form()
    {
        return Form::make(new User(), function (Form $form) {
            $form->display('id');
            $form->display('wallet');
            
            $form->select('rank')->options($this->rankArr)->required();
                
            $form->disableViewButton();
            $form->disableDeleteButton();
            $form->disableResetButton();
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->disableCreatingCheck();
        });
    }

}
