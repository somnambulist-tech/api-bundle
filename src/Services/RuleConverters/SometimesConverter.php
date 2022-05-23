<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;

/**
 * Class SometimesConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\SometimesConverter
 */
class SometimesConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'sometimes';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            'nullable'  => true,
        ]);
    }
}
