<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Transformer;

use Assert\Assert;
use League\Fractal\TransformerAbstract;

/**
 * Class PassThroughTransformer
 *
 * If the element is an array, return as-is and do not transform. Allows directly
 * returning existing array data while still utilising the Fractal infrastructure.
 *
 * @package    Somnambulist\ApiBundle\Services\Transformer
 * @subpackage Somnambulist\ApiBundle\Services\Transformer\PassThroughTransformer
 */
final class PassThroughTransformer extends TransformerAbstract
{

    /**
     * @param array $entity
     *
     * @return array
     */
    public function transform($entity): array
    {
        Assert::that($entity, 'Attempted to transform non-array data via PassThroughTransformer', 'entity')->isArray();

        return $entity;
    }
}
