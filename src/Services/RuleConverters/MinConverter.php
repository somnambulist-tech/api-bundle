<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use Somnambulist\Bundles\ApiBundle\Services\RuleConverters\Behaviours\HasLength;

/**
 * Class MinConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\MinConverter
 */
class MinConverter extends AbstractRuleConverter
{
    use HasLength;

    public function rule(): string
    {
        return 'min';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        $schema[$this->hasLength($schema, $this->converters) ? 'minLength' : 'minimum'] = ctype_digit($params) ? (int)$params : (float)$params;

        return $schema;
    }
}
