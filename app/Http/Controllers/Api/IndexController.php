<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameOrder;
use App\Models\GameTeam;
use App\Models\Recharge;
use App\Models\User;

class IndexController extends Controller
{
    public $host = '';

    public function __construct()
    {
        parent::__construct();
        $this->host = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
    }

    public function index()
    {
        $data             = [];
        $data['user']     = User::query()->count() + config('other_user');
        $data['destoryd'] = Recharge::query()->where('status', 2)->where('type',
                1)->sum('nums') + config('other_destory');
        return responseJson($data);
    }

}
