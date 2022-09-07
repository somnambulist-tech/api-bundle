<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters;

use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverterInterface;
use Throwable;

final class EntityNotFoundConverter implements ExceptionConverterInterface
{
    public function convert(Throwable $e): array
    {
        return [
            'data' => [
                'message' => $e->getMessage(),
            ],
            'code' => 404,
        ];
    }
}
