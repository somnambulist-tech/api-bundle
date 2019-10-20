<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Converters;

use Exception;

/**
 * Class GenericConverter
 *
 * @package Somnambulist\ApiBundle\Services\Converters
 * @subpackage Somnambulist\ApiBundle\Services\Converters\GenericConverter
 */
interface ExceptionConverterInterface
{

    /**
     * @param Exception $e
     *
     * @return array An array containing "data.error" - the error message and "code" the HTTP status code
     */
    public function convert(Exception $e): array;
}
