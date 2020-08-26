<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Response\Transformers;

use League\Fractal\TransformerAbstract;

/**
 * Class ArrayTransformer
 *
 * If the element is an array, return as-is and do not transform. Allows directly
 * returning existing array data while still utilising the Fractal infrastructure.
 *
 * @package    Somnambulist\ApiBundle\Services\Transformer
 * @subpackage Somnambulist\ApiBundle\Response\Transformers\ArrayTransformer
 */
final class ArrayTransformer extends TransformerAbstract
{

    public function transform(array $entity): array
    {
        return $entity;
    }
}
