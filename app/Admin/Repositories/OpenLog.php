<?php

namespace App\Admin\Repositories;

use App\Models\OpenLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class OpenLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
