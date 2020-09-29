<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Transformers;

use League\Fractal\TransformerAbstract;

/**
 * Class ArrayTransformer
 *
 * If the element is an array, return as-is and do not transform. Allows directly
 * returning existing array data while still utilising the Fractal infrastructure.
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\Transformer
 * @subpackage Somnambulist\Bundles\ApiBundle\Response\Transformers\ArrayTransformer
 */
final class ArrayTransformer extends TransformerAbstract
{

    public function transform(array $entity): array
    {
        return $entity;
    }
}
