<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services\Converters;

use Somnambulist\ApiBundle\Services\ExceptionConverter;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;
use function reset;

/**
 * Class HandlerFailedExceptionConverter
 *
 * Unwraps the messenger HandlerFailedException exception.
 *
 * @package Somnambulist\ApiBundle\Services\Converters
 * @subpackage Somnambulist\ApiBundle\Services\Converters\HandlerFailedExceptionConverter
 */
final class HandlerFailedExceptionConverter implements ExceptionConverterInterface
{

    /**
     * @var ExceptionConverter
     */
    private $converter;

    /**
     * Constructor
     *
     * @param ExceptionConverter $converter
     */
    public function __construct(ExceptionConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param Throwable $e
     *
     * @return array
     */
    public function convert(Throwable $e): array
    {
        if (!$e instanceof HandlerFailedException) {
            return (new GenericConverter())->convert($e);
        }

        $stack = $e->getNestedExceptions();

        switch (count($stack)) {
            case 0: return $this->converter->convert($e);
            default:
                return $this->converter->convert(reset($stack));
        }
    }
}
