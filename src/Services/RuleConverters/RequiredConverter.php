<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;

class RequiredConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'required';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, ['#required' => true]);
    }
}
