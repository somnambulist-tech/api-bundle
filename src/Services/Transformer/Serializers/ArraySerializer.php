<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Transformer\Serializers;

use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

/**
 * Class ArraySerializer
 *
 * Prevents array data from being assigned to a "data" element.
 *
 * @package Somnambulist\ApiBundle\Services\Transformer\Serializers
 * @subpackage Somnambulist\ApiBundle\Services\Transformer\Serializers\ArraySerializer
 */
class ArraySerializer extends BaseArraySerializer
{

    /**
     * @inheritDoc
     */
    public function collection($resourceKey, array $data)
    {
        if (!$resourceKey) {
            return $data;
        }

        return [$resourceKey => $data];
    }

    /**
     * @inheritDoc
     */
    public function item($resourceKey, array $data)
    {
        if (!$resourceKey) {
            return $data;
        }

        return [$resourceKey => $data];
    }
}
