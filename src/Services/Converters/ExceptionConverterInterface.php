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
     * Convert the exception to an array of message parameters:
     *
     *  * code - the HTTP status code
     *  * data.message - the error message to display
     *  * data.errors - an array of key -> value pairs of error data e.g. fields
     *  * data.XXX - any other elements to be added to the response
     *
     * @param Exception $e
     *
     * @return array
     */
    public function convert(Exception $e): array;
}
