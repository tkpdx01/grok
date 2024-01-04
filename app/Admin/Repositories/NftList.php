<?php

namespace App\Admin\Repositories;

use App\Models\NftList as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class NftList extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
