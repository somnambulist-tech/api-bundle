<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

class RegexConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'regex';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            'title'   => 'The string must match the regular expression',
            'pattern' => $params,
        ]);
    }
}
