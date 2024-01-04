<?php

namespace App\Admin\Repositories;

use App\Models\UserBox as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserBox extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
