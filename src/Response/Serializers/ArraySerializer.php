<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\Serializers;

use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

/**
 * Prevents array data from being assigned to a "data" element.
 */
class ArraySerializer extends BaseArraySerializer
{
    public function collection(?string $resourceKey, array $data): array
    {
        if (!$resourceKey) {
            return $data;
        }

        return [$resourceKey => $data];
    }

    public function item(?string $resourceKey, array $data): array
    {
        if (!$resourceKey) {
            return $data;
        }

        return [$resourceKey => $data];
    }
}
