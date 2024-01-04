<?php

namespace App\Admin\Repositories;

use App\Models\UserNft as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserNft extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
