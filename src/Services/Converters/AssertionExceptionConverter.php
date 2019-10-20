<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Converters;

use Assert\InvalidArgumentException;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use function array_filter;
use function array_shift;
use function explode;
use function preg_replace;

/**
 * Class AssertionExceptionConverter
 *
 * Converts an Assert\InvalidArgumentException to an array of field data instead of a string.
 * An assertion error message typically looks like:
 *
 * <code>
 * The following 4 assertions failed:\n
 * 1) id: Value "" is empty, but non empty value was expected.\n
 * 2) name: Value "" is empty, but non empty value was expected.\n
 * 3) another: Value "" is empty, but non empty value was expected.\n
 * 4) createdAt: Class "" was expected to be instanceof of "DateTimeInterface" but is not.\n
 * </code>
 *
 * @package Somnambulist\ApiBundle\Services\Converters
 * @subpackage Somnambulist\ApiBundle\Services\Converters\AssertionExceptionConverter
 */
final class AssertionExceptionConverter implements ExceptionConverterInterface
{

    /**
     * @param Exception $e
     *
     * @return array An array containing "data.error" - the error message and "code" the HTTP status code
     */
    public function convert(Exception $e): array
    {
        if (!$e instanceof InvalidArgumentException) {
            return (new GenericConverter())->convert($e);
        }

        $fields = $this->createFieldsFromErrorMessage($e->getMessage());

        return [
            'data' => [
                'error'  => 'There was an invalid argument in the request data. See fields for further information',
                'fields' => $fields,
            ],
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ];
    }

    private function createFieldsFromErrorMessage(string $message): array
    {
        $lines  = array_filter(explode("\n", $message));
        $fields = [];

        array_shift($lines);

        foreach ($lines as $line) {
            [$field, $message] = explode(':', $line, 2);
            $fields[trim(preg_replace('/^\d+\) /', '', $field))] = trim($message);
        }

        return $fields;
    }
}
