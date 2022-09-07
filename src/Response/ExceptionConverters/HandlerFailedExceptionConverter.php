<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters;

use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverter;
use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverterInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

use function reset;

/**
 * Unwraps the messenger HandlerFailedException exception.
 */
final class HandlerFailedExceptionConverter implements ExceptionConverterInterface
{
    public function __construct(private ExceptionConverter $converter)
    {
    }

    public function convert(Throwable $e): array
    {
        if (!$e instanceof HandlerFailedException) {
            return (new GenericConverter())->convert($e);
        }

        $stack = $e->getNestedExceptions();

        return match (count($stack)) {
            0       => $this->converter->convert($e),
            default => $this->converter->convert(reset($stack)),
        };
    }
}
