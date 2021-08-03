<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services;

use InvalidArgumentException;
use Somnambulist\Bundles\ApiBundle\Services\Contracts\RuleConverterInterface;
use Somnambulist\Bundles\ApiBundle\Services\Contracts\UsesRuleConvertersInterface;
use function array_filter;
use function array_key_exists;

/**
 * Class RuleConverters
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 */
final class RuleConverters
{
    /**
     * @var array|RuleConverterInterface[]
     */
    private array $converters = [];

    public function __construct(iterable $converters)
    {
        foreach ($converters as $converter) {
            $this->add($converter);
        }
    }

    /**
     * Applies each handler defined in handlers to the schema with the value as params
     *
     * @param array $converters rule -> params pairs to apply to schema
     * @param array $schema
     * @param array $rules
     *
     * @return array
     */
    public function applyAll(array $converters, array $schema, array $rules): array
    {
        foreach ($converters as $rule => $params) {
            if ($this->has($rule)) {
                $schema = $this->get($rule)->apply($schema, $rule, $params, $rules);
            }
        }

        return $schema;
    }

    public function add(RuleConverterInterface $converter): self
    {
        $this->converters[] = $converter;

        if ($converter instanceof UsesRuleConvertersInterface) {
            $converter->setConverters($this);
        }

        return $this;
    }

    /**
     * Find the best matching converters for the rule
     *
     * Will return either the converter directly or a wrapped set of converters to apply to the
     * same rule / params.
     *
     * @param string $rule
     *
     * @return RuleConverterInterface
     */
    public function get(string $rule): RuleConverterInterface
    {
        $matches = array_filter($this->converters, fn (RuleConverterInterface $c) => $c->supports($rule));

        if (empty($matches)) {
            throw new InvalidArgumentException(sprintf('A converter for "%s" has not been configured', $rule));
        }

        if (1 === count($matches)) {
            return reset($matches);
        }

        return new MatchedRuleConverters($matches);
    }

    public function has(string $rule): bool
    {
        return count(array_filter($this->converters, fn (RuleConverterInterface $c) => $c->supports($rule))) > 0;
    }
}
