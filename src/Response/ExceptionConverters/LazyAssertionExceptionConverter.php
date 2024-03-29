<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters;

use Assert\LazyAssertionException;
use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverterInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Converts an Assert\LazyAssertionException to an array of field data instead of a string.
 *
 * The assertion exception contains an array of exception errors that can be turned into
 * fields.
 */
final class LazyAssertionExceptionConverter implements ExceptionConverterInterface
{
    public function convert(Throwable $e): array
    {
        if (!$e instanceof LazyAssertionException) {
            return (new GenericConverter())->convert($e);
        }

        return [
            'data' => [
                'message' => 'Domain assertion error, see errors for more details',
                'errors'  => $this->createFieldsFromExceptionTraces($e),
            ],
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ];
    }

    private function createFieldsFromExceptionTraces(LazyAssertionException $e): array
    {
        $fields = [];

        foreach ($e->getErrorExceptions() as $error) {
            $fields[$error->getPropertyPath()] = $error->getMessage();
        }

        return $fields;
    }
}
