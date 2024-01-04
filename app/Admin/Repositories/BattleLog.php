<?php

namespace App\Admin\Repositories;

use App\Models\BattleLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class BattleLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
