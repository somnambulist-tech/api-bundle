<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\Contracts;

/**
 * Interface RuleConverterInterface
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\Contracts\RuleConverterInterface
 */
interface RuleConverterInterface
{
    /**
     * The rule name to apply to; sometimes a Rakit rule or a regex of matches
     *
     * @return string
     */
    public function rule(): string;

    /**
     * Returns true if this handler can apply to the rule
     *
     * @param string $rule
     *
     * @return bool
     */
    public function supports(string $rule): bool;

    /**
     * Apply changes to the schema based on the rule
     *
     * @param array  $schema
     * @param string $rule
     * @param string $params
     * @param array  $rules
     *
     * @return array
     */
    public function apply(array $schema, string $rule, string $params, array $rules): array;
}
