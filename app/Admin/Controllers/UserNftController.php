<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\Ship;
use App\Admin\Repositories\Recharge;
use App\Models\NftList;
use App\Models\UserNft;
use Dcat\Admin\Color;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class UserNftController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(UserNft::with(['user','nft']), function (Grid $grid) {
            $grid->number();

            $grid->column('user_id','UID');
            $grid->column('user.wallet','用户');
            $grid->column('nft.name','NFT');
            $grid->column('nft_icon','NFT图标')->display(function ($value){
                return NftList::query()->where('id',$this->nft_id)->value('icon');
            })->image(env('APP_URL').'/uploads/',100,100);
            $grid->column('status','状态')->using([1=>'闲置',2=>'对战中',3=>'已完成'])->badge([1=>'danger',2=>'blue1',3=>'orange']);
            $grid->column('created_at');

            $grid->model()->orderBy('id','desc');

            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableActions();


            $grid->export()->rows(function ($rows){
                $statusArr = [1=>'闲置',2=>'对战中',3=>'已完成'];
                foreach ($rows as $index=>&$row){
                    $row['status'] = $statusArr[$row['status']];
                }
                return $rows;
            })->xlsx();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('user.wallet','用户');
                $filter->equal('status','状态')->select([1=>'闲置',2=>'已销毁']);
                $filter->between('created_at','创建时间')->datetime();
            });
        });
    }

}
