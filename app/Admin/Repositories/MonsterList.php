<?php

namespace App\Admin\Repositories;

use App\Models\MonsterList as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class MonsterList extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
