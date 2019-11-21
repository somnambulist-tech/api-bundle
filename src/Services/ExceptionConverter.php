<?php declare(strict_types=1);

namespace Somnambulist\ApiBundle\Services;

use Exception;
use Somnambulist\ApiBundle\Services\Converters\ExceptionConverterInterface;
use Somnambulist\ApiBundle\Services\Converters\GenericConverter;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Throwable;
use function array_key_exists;
use function get_class;

/**
 * Class ExceptionConverter
 *
 * @package Somnambulist\ApiBundle\Services
 * @subpackage Somnambulist\ApiBundle\Services\ExceptionConverter
 */
final class ExceptionConverter implements ExceptionConverterInterface
{

    /**
     * @var iterable
     */
    private $converters;

    /**
     * @var array
     */
    private $mappings = [];

    /**
     * Constructor
     *
     * @param ServiceLocator $converters
     * @param array          $mappings
     */
    public function __construct(ServiceLocator $converters, array $mappings = [])
    {
        $this->converters = $converters;
        $this->mappings   = $mappings;
    }

    /**
     * Maps the exception to an appropriate converter and returns the converter
     *
     * @param Exception $e
     *
     * @return ExceptionConverterInterface
     */
    public function map(Exception $e): ExceptionConverterInterface
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
