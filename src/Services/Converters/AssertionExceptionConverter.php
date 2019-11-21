<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Converters;

use Assert\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class AssertionExceptionConverter
 *
 * Converts an Assert\InvalidArgumentException to an array of field data instead of a string.
 * Typically this is a single error message for a single property path item.
 *
 * @package Somnambulist\ApiBundle\Services\Converters
 * @subpackage Somnambulist\ApiBundle\Services\Converters\AssertionExceptionConverter
 */
final class AssertionExceptionConverter implements ExceptionConverterInterface
{

    /**
     * @param Throwable $e
     *
     * @return array
     */
    public function convert(Throwable $e): array
    {
        if (!$e instanceof InvalidArgumentException) {
            return (new GenericConverter())->convert($e);
        }

        return [
            'data' => [
                'message' => 'Domain assertion error, see errors for more details',
                'errors'  => $this->createFieldsFromException($e),
            ],
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ];
    }

    private function createFieldsFromException(InvalidArgumentException $e): array
    {
        return [
            $e->getPropertyPath() => $e->getMessage(),
        ];
    }
}
