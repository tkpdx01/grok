<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\Ship;
use App\Admin\Renderable\OpenLogTable;
use App\Admin\Repositories\Recharge;
use App\Models\NftList;
use App\Models\UserBox;
use App\Models\UserNft;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class UserBoxController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(UserBox::with(['user']), function (Grid $grid) {
            $grid->number();

            $grid->column('user_id','UID');
            $grid->column('user.name','用户');
            $grid->column('num','宝盒数量');
            $grid->column('created_at');

            $grid->model()->orderBy('id','desc');

            $grid->column('openlog','开启记录')->display('开启记录')->modal('开启记录', OpenLogTable::make());


            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableActions();


            $grid->export()->rows(function ($rows){
            })->xlsx();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('user.wallet','用户');
                $filter->between('created_at','创建时间')->datetime();
            });
        });
    }

}
