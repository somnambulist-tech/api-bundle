<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Tests\Support\Stubs;

use League\Fractal\TransformerAbstract;
use Somnambulist\ApiBundle\Tests\Support\Stubs\Entities\MyEntity;


/**
 * Class MyEntityTransformer
 *
 * @package    Somnambulist\ApiBundle\Tests\Support\Stubs
 * @subpackage Somnambulist\ApiBundle\Tests\Support\Stubs\MyEntityTransformer
 */
class MyEntityTransformer extends TransformerAbstract
{

    public function transform(MyEntity $entity)
    {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'other' => $entity->getAnother(),
        ];
    }
}
