<?php

namespace App\Admin\Metrics;

use App\Models\BattleLog;
use App\Models\User;
use App\Models\Withdraw;
use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class TotalWithdraw extends Card
{
    /**
     * 卡片底部内容.
     *
     * @var string|Renderable|\Closure
     */
    protected $footer;

    protected $height = 100;

    protected $chartMarginTop = 10;

    /**
     * 初始化卡片.
     */
    protected function init()
    {
        parent::init();
        $this->title('累计提现');
    }

    /**
     * 处理请求.
     *
     * @param Request $request
     *
     * @return void
     */
    public function handle(Request $request)
    {
        $num = bcdiv(Withdraw::query()->where('status',2)->sum('num'),10000,2).'W';
        $this->content($num);
    }

    /**
     * 渲染卡片内容.
     *
     * @return string
     */
    public function renderContent()
    {
        $content = parent::renderContent();

        return <<<HTML
<div class="d-flex justify-content-between align-items-center mt-1" style="margin-bottom: 2px">
    <h3 class="ml-1 font-lg-1">{$content}</h3>
</div>
HTML;
    }

    /**
     * 渲染卡片底部内容.
     *
     * @return string
     */
    public function renderFooter()
    {
        return $this->toString($this->footer);
    }
}
