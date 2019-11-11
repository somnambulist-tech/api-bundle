<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Converters;

use Exception;

/**
 * Class GenericConverter
 *
 * @package Somnambulist\ApiBundle\Services\Converters
 * @subpackage Somnambulist\ApiBundle\Services\Converters\GenericConverter
 */
final class GenericConverter implements ExceptionConverterInterface
{

    /**
     * @param Exception $e
     *
     * @return array An array containing "data.error" - the error message and "code" the HTTP status code
     */
    public function convert(Exception $e): array
    {
        return [
            'data' => [
                'message' => $e->getMessage(),
            ],
            'code' => $e->getCode() && $e->getCode() >=400 && $e->getCode() < 500 ? $e->getCode() : 400
        ];
    }
}
