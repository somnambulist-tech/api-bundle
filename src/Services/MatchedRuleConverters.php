<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services;

use Somnambulist\Bundles\ApiBundle\Services\Contracts\RuleConverterInterface;

/**
 * Wraps a set of converters to apply to the same rule.
 */
final class MatchedRuleConverters implements RuleConverterInterface
{
    /**
     * @var array|RuleConverterInterface[]
     */
    private array $converters;

    public function __construct(array $converters)
    {
        $this->converters = $converters;
    }

    public function rule(): string
    {
        return '__internal_matched_rules_converter__';
    }

    public function supports(string $rule): bool
    {
        return false;
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        foreach ($this->converters as $converter) {
            $schema = $converter->apply($schema, $rule, $params, $rules);
        }

        return $schema;
    }
}
