<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Converters;

use Throwable;

/**
 * Class GenericConverter
 *
 * @package Somnambulist\ApiBundle\Services\Converters
 * @subpackage Somnambulist\ApiBundle\Services\Converters\GenericConverter
 */
final class GenericConverter implements ExceptionConverterInterface
{

    /**
     * @param Throwable $e
     *
     * @return array An array containing "data.message" - the error message and "code" the HTTP status code
     */
    public function convert(Throwable $e): array
    {
        return [
            'data' => [
                'message' => $e->getMessage(),
            ],
            'code' => $e->getCode() && $e->getCode() >=400 && $e->getCode() < 500 ? $e->getCode() : 400
        ];
    }
}
