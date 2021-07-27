<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ApiBundle\Services\RuleConverters;

use function array_merge;
use function explode;

/**
 * Class NotInConverter
 *
 * @package    Somnambulist\Bundles\ApiBundle\Services\RuleConverters
 * @subpackage Somnambulist\Bundles\ApiBundle\Services\RuleConverters\NotInConverter
 */
class NotInConverter extends AbstractRuleConverter
{
    public function rule(): string
    {
        return 'not_in';
    }

    public function apply(array $schema, string $rule, string $params, array $rules): array
    {
        return array_merge($schema, [
            'not' => [
                'type' => 'string',
                'enum' => explode(',', $params),
            ],
        ]);
    }
}
