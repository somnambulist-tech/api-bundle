<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;

class PresentConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'present';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            '#required' => true,
            'nullable'  => true,
        ]);
    }
}
