<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Serializers;

use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

/**
 * Class ArraySerializer
 *
 * Prevents array data from being assigned to a "data" element.
 *
 * @package Somnambulist\Bundles\ApiBundle\Services\Transformer\Serializers
 * @subpackage Somnambulist\Bundles\ApiBundle\Response\Serializers\ArraySerializer
 */
class ArraySerializer extends BaseArraySerializer
{
    /**
     * @inheritDoc
     */
    public function collection(?string $resourceKey, array $data): array
    {
        if (!$resourceKey) {
            return $data;
        }

        return [$resourceKey => $data];
    }

    /**
     * @inheritDoc
     */
    public function item(?string $resourceKey, array $data): array
    {
        if (!$resourceKey) {
            return $data;
        }

        return [$resourceKey => $data];
    }
}
