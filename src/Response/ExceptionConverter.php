<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Response;

use Somnambulist\Bundles\ApiBundle\Response\ExceptionConverters\GenericConverter;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Throwable;

use function array_key_exists;
use function get_class;

final class ExceptionConverter implements ExceptionConverterInterface
{
    public function __construct(private ServiceLocator $converters, private array $mappings = [])
    {
    }

    public function map(Throwable $e): ExceptionConverterInterface
    {
        if (array_key_exists($type = get_class($e), $this->mappings)) {
            $class     = $this->mappings[$type];
            $converter = $this->converters->get($class);

            if ($converter instanceof ExceptionConverterInterface) {
                return $converter;
            }

            return new $class();
        }

        return $this->converters->get(GenericConverter::class);
    }

    public function convert(Throwable $e): array
    {
        return $this->map($e)->convert($e);
    }
}
