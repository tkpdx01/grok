<?php

namespace App\Admin\Forms;

use App\Models\UsersCoin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Exception;
use Illuminate\Support\Facades\DB;

class SetBalanceNum extends Form implements LazyRenderable
{

    use LazyWidget;

    // 使用异步加载功能

    /**
     * Handle the form request.
     *
     * @param  array  $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $id = $this->payload['id'];

        DB::beginTransaction();
        try {
            $amount = $input['recharge_type'] == 1 ? $input['amount'] : '-'.$input['amount'];
            UsersCoin::monsIncome($id, $input['amount_type'], $amount, 0, '资产校对');

            DB::commit();
            return $this
                ->response()
                ->success('操作成功.')
                ->refresh();

        } catch (Exception $e) {
            DB::rollBack();
            return $this
                ->response()
                ->error('操作失败.'.$e->getMessage())
                ->refresh();
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->radio('amount_type', '账户类型')->options([1 => 'GROK']);
        $this->radio('recharge_type', '充值类型')->options([1 => '增加', 2 => '减少']);
        $this->decimal('amount', '金额');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
        ];
    }
}
