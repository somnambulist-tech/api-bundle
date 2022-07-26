<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response;

use Throwable;

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
     * @param Throwable $e
     *
     * @return array
     */
    public function convert(Throwable $e): array;
}
