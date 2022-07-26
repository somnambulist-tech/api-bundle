<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ReadModels\Model;

class MyModel extends Model
{

    protected array $exports = [
        'attributes' => [
            'id', 'name',
        ]
    ];
}
