<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours\HasLength;

class MaxConverter extends AbstractRuleConverter
{
    use HasLength;

    public function rule(): string
    {
        return 'max';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        $schema[$this->hasLength($schema, $rules) ? 'maxLength' : 'maximum'] = ctype_digit($params) ? (int)$params : (float)$params;

        return $schema;
    }
}
