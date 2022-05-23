<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;

/**
 * Class RejectedConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\RejectedConverter
 */
class RejectedConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'rejected';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            'type'  => null,
            'oneOf' => [
                ['type' => 'boolean'],
                ['type' => 'string', 'enum' => ['off', 'no', '0', 'false']],
            ],
        ]);
    }
}
