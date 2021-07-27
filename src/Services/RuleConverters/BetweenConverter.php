<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function explode;

/**
 * Class BetweenConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\BetweenConverter
 */
class BetweenConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'between';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        [$min, $max] = explode(',', $params);

        return $this->converters->applyAll(['min' => $min, 'max' => $max], $schema, $rules);
    }
}
