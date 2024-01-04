<?php

namespace App\Admin\Repositories;

use App\Models\UsersCoin as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UsersCoin extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
