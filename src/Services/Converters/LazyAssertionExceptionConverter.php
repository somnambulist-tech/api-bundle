<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Converters;

use Assert\LazyAssertionException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class LazyAssertionExceptionConverter
 *
 * Converts an Assert\LazyAssertionException to an array of field data instead of a string.
 * The assertion exception contains an array of exception errors that can be turned into
 * fields.
 *
 * @package Somnambulist\ApiBundle\Services\Converters
 * @subpackage Somnambulist\ApiBundle\Services\Converters\LazyAssertionExceptionConverter
 */
final class LazyAssertionExceptionConverter implements ExceptionConverterInterface
{

    /**
     * @param Throwable $e
     *
     * @return array
     */
    public function convert(Throwable $e): array
    {
        if (!$e instanceof LazyAssertionException) {
            return (new GenericConverter())->convert($e);
        }

        return [
            'data' => [
                'message'  => 'Domain assertion error, see errors for more details',
                'errors' => $this->createFieldsFromExceptionTraces($e),
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
