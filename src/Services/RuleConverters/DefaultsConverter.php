<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;
use function explode;
use function str_contains;

class DefaultsConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return '/^defaults?$/';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        if (str_contains($params, '|') && !str_contains($params, '\|')) {
            $params = explode('|', $params)[0];
        }

        return array_merge($schema, ['default' => $params]);
    }
}
