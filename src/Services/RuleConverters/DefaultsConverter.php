<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;

/**
 * Class DefaultsConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\DefaultsConverter
 */
class DefaultsConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return '/^defaults?$/';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, ['default' => $params]);
    }
}
