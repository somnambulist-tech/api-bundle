<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters;

use Assert\InvalidArgumentException;
use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverterInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class AssertionExceptionConverter
 *
 * Converts an Assert\InvalidArgumentException to an array of field data instead of a string.
 * Typically this is a single error message for a single property path item.
 *
 * @package    Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\AssertionExceptionConverter
 */
final class AssertionExceptionConverter implements ExceptionConverterInterface
{

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
