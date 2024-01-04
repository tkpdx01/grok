<?php

namespace App\Admin\Repositories;

use App\Models\BattleDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class BattleDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
