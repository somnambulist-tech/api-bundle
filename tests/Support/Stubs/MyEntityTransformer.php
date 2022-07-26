<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs;

use League\Fractal\TransformerAbstract;
use Somnambulist\Bundles\ApiBundle\Tests\Support\Stubs\Entities\MyEntity;

class MyEntityTransformer extends TransformerAbstract
{
    public function transform(MyEntity $entity): array
    {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'other' => $entity->getAnother(),
        ];
    }
}
