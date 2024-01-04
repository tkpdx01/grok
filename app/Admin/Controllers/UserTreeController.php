<?php
namespace App\Admin\Controllers;

use App\Admin\Renderable\UserPowerTable;
use App\Admin\Repositories\User;
use App\Models\LevelConfig;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Tab;
use Dcat\Admin\Widgets\Card;

class UserTreeController extends AdminController
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
    
    public function index(Content $content)
    {
        return $content->header('推荐树')->description('推荐树管理')->body($this->grid());
    }

    protected function grid(){
        return Grid::make(User::with(['coin1']), function (Grid $grid) {
//            $grid->number();
            
            $grid->column('wallet', '钱包地址')->tree();
            $grid->column('id','用户ID');
            $grid->column('is_effective')->using([0=>'否',1=>'是'])->badge([0=>'gray',1=>'tear']);
            $grid->column('coin1.amount', 'GROK余额')->badge('danger');
//            $grid->column('static_income', '游戏收益')->badge('blue');
            $grid->column('dynamic_income', '动态收益')->badge('pink');
            $grid->column('zhi_num', '直推人数');
            $grid->column('group_num', '团队人数');
            $grid->column('myperfor', '个人业绩');
            $grid->column('teamperfor', '伞下业绩');

            /* 
            $grid->column('wallet', '钱包地址')->display('点击查看') // 设置按钮名称
                ->modal(function ($modal) {
                    // 设置弹窗标题
//                     $modal->title('钱包地址');
                    // 自定义图标
                    return $this->wallet;
            });
              */ 
            /* 
            $grid->column('wallet', '钱包地址')
            ->display('点击查看') // 设置按钮名称
            ->expand(function () {
                // 返回显示的详情
                // 这里返回 content 字段内容，并用 Card 包裹起来
                $card = new Card(null, $this->wallet);
                return "<div style='padding:10px 10px 0'>$card</div>";
            });
             */
            
//             $grid->column('status','状态')->switch('',true);
//             $grid->column('created_at','注册时间');
            $grid->disableRowSelector();

            $grid->disableActions();
            $grid->disableCreateButton();

            $grid->model()->orderBy('id','asc');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id','用户ID');
                $filter->equal('wallet','钱包地址');
//                 $filter->equal('status','状态')->radio([0=>'禁用',1=>'有效']);
//                 $filter->between('created_at','注册时间')->datetime();
            });
        });
    }

}
