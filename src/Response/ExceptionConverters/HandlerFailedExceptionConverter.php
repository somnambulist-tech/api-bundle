<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Response\ExceptionConverters;

use Somnambulist\ApiBundle\Response\ExceptionConverter;
use Somnambulist\ApiBundle\Response\ExceptionConverterInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;
use function reset;

/**
 * Class HandlerFailedExceptionConverter
 *
 * Unwraps the messenger HandlerFailedException exception.
 *
 * @package    Somnambulist\ApiBundle\Response\ExceptionConverters
 * @subpackage Somnambulist\ApiBundle\Response\ExceptionConverters\HandlerFailedExceptionConverter
 */
final class HandlerFailedExceptionConverter implements ExceptionConverterInterface
{

    private ExceptionConverter $converter;

    public function __construct(ExceptionConverter $converter)
    {
        $this->converter = $converter;
    }

    public function convert(Throwable $e): array
    {
        if (!$e instanceof HandlerFailedException) {
            return (new GenericConverter())->convert($e);
        }

        $stack = $e->getNestedExceptions();

        switch (count($stack)) {
            case 0:
                return $this->converter->convert($e);
            default:
                return $this->converter->convert(reset($stack));
        }
    }
}
